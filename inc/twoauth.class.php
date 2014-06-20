<?php

defined('ABSPATH') OR exit;


final class twoauth {

    private static $_token_field = 'twoauth_token';


    /**
     * Singleton
     */
    private static $instance = null;

    public static function instance() {
        if(NULL === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }


    /**
     * Init plugin
     */
    private function __construct() {

        add_action(
            'init',
            array(
                $this,
                'register_textdomain'
            )
        );

        add_action(
            'login_enqueue_scripts',
            array(
                $this,
                'add_scripts'
            )
        );

        add_action(
            'login_head',
            array(
                $this,
                'add_ajaxurl'
            )
        );

        add_action(
            'login_form',
            array(
                $this,
                'loginform'
            )
        );

        add_action(
            'wp_ajax_nopriv_twoauth',
            array(
                $this,
                'twoauth_ajax_callback'
            )
        );

        add_action(
            'admin_init',
            array(
                $this,
                'remove_token'
            )
        );

        add_filter(
            'authenticate',
            array(
                $this,
                'check_token'
            ),
            50,
            3
        );
    }


    /**
     * Translation
     */
    public function register_textdomain()
    {
        load_plugin_textdomain(
            'twoauth',
            false,
            'TwoAuth/lang'
        );
    }


    /**
     * Add jQuery and twoauth.js
     */
    public function add_scripts() {
        if(!wp_script_is('jquery', 'registered')) {
            wp_register_script(
                'jquery',
                plugins_url(
                    '/js/jquery-1.11.1.min.js',
                    PLUGIN_FILE_TWOAUTH
                ),
                array(),
                null
            );
        }
        wp_enqueue_script('jquery');

        wp_register_script(
            'twoauth',
            plugins_url(
                '/js/twoauth.js',
                PLUGIN_FILE_TWOAUTH
            ),
            array(),
            null,
            true
        );
        wp_enqueue_script('twoauth');
    }


    /**
     * Add url to access admin-ajax.php
     */
    public function add_ajaxurl() {
    ?>
        <script>
            var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        </script>
    <?php
    }


    /**
     * Extends the login form
     */
    public function loginform() {
        echo "\t<p>\n";
        echo "\t\t<button type=\"button\" id=\"btn_twoauth\" class=\"button button-primary button-small\" style=\"float:none;width:100%;margin-bottom:8px;\">". __('Get TwoAuth Token', 'twoauth') ."</button>";
        echo "\t</p>\n";
        echo "\t<p>\n";
        echo "\t\t<label for=\"user_twoauth\">". __('TwoAuth Token', 'twoauth') ."<br>";
        echo "\t\t<input type=\"text\" name=\"twoauth\" id=\"user_twoauth\" class=\"input\" value=\"\" size=\"20\"></label>\n";
        echo "\t</p>\n";
    }


    /**
     * Ajax callback function
     */
    public function twoauth_ajax_callback() {
        $user_login = sanitize_user($_POST['user_login']);
        $user_pass = trim($_POST['user_pass']);

        $user = get_user_by('login', $user_login);
        if(!$user || !wp_check_password($user_pass, $user->data->user_pass, $user->ID)) {
            printf('<div id="login_error"><strong>%s:</strong> %s<br></div>',
                __('TwoAuth ERROR', 'twoauth'),
                __('Invalid Username or Password.', 'twoauth')
            );
            die();
        }

        $token = wp_generate_password(5, false);

        update_user_meta(
            $user->ID,
            self::$_token_field,
            wp_hash_password($token)
        );

        set_site_transient(
            self::$_token_field . $user->ID,
            1,
            5 * MINUTE_IN_SECONDS
        );

        wp_mail(
            $user->user_email,
            __('Your TwoAuth Token', 'twoauth'),
            $token
        );

        printf('<p class="message"><strong>%s:</strong> %s<br></p>',
            __('TwoAuth', 'twoauth'),
            __('Token sent via email. <strong>Valid for five minutes.</strong>', 'twoauth')
        );
        die();
    }


    /**
     * Remove token after valid login
     */
    public function remove_token() {
        delete_user_meta(
            get_current_user_id(),
            self::$_token_field
        );
    }


    /**
     * Custom login verification
     */
    public function check_token($user, $username = '', $password = '') {

        $userstate = $user;

        $user = get_user_by('login', $username);
        if(!$user) return $userstate;

        if(!get_site_transient(self::$_token_field . $user->ID)) {
            delete_user_meta(
                $user->ID,
                self::$_token_field
            );
        }

        $user_token = get_user_meta(
            $user->ID,
            self::$_token_field,
            true
        );

        $auth_token = sanitize_text_field(trim($_POST['twoauth']));

        if(!wp_check_password($auth_token, $user_token, $user->ID)) {

            return new WP_Error(
                'invalid_twoauth_token',
                __('<strong>TwoAuth ERROR:</strong> Invalid or expired token.', 'twoauth')
            );

        }

        return $userstate;
    }

}

?>