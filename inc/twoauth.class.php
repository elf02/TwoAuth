<?php

defined('ABSPATH') OR exit;


final class twoauth {

    private static $_token_field = '_twoauth_token';
    private static $_nonce_field = '_twoauth_nonce';
    private static $_apppw_field = '_twoauth_apppw';
    private static $_app_field = '_twoauth_app';
    private static $_email_field = '_twoauth_email';


    /**
     * Singleton
     */
    private static $instance = null;

    public static function instance() {
        if( NULL === self::$instance ) {
            self::$instance = new self;
        }
        return self::$instance;
    }


    /**
     * Init plugin
     */
    private function __construct() {
        add_action(
            'login_enqueue_scripts',
            array(
                $this,
                'add_scripts'
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

        add_action(
            'show_user_profile',
            array(
                $this,
                'twoauth_profile_fields'
            )
        );
        add_action(
            'edit_user_profile',
            array(
                $this,
                'twoauth_profile_fields'
            )
        );

        add_action(
            'personal_options_update',
            array(
                $this,
                'twoauth_save_profile_fields'
            )
        );
        add_action(
            'edit_user_profile_update',
            array(
                $this,
                'twoauth_save_profile_fields'
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
     * Show profile fields
     */
    public function twoauth_profile_fields( $user ) { ?>
        <h3>TwoAuth</h3>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e( 'Enable App Password', 'twoauth' ); ?></th>
                <td>
                    <label for="twoauth_app">
                        <?php

                            $twoauth_app = get_user_meta(
                                $user->ID,
                                self::$_app_field,
                                true
                            );

                            printf(
                                '<input type="checkbox" name="twoauth_app" id="twoauth_app" value="1" %s>%s',
                                checked( $twoauth_app, 1, false ),
                                __( 'Enable', 'twoauth' )
                            );

                        ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="twoauth_apppw"><?php _e( 'App Password', 'twoauth' ); ?></label></th>
                <td>
                    <input name="twoauth_apppw" type="password" id="twoauth_apppw" class="regular-text" value="" autocomplete="off"><br>
                    <span class="description" for="twoauth_apppw"><?php _e( 'Set an App password', 'twoauth' ); ?></span>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="twoauth_email"><?php _e( 'Token Email Address', 'twoauth' ); ?></label></th>
                <td>
                    <?php

                        $twoauth_email = get_user_meta(
                            $user->ID,
                            self::$_email_field,
                            true
                        );

                        printf(
                            '<input name="twoauth_email" type="email" id="twoauth_email" class="regular-text ltr" value="%s"><br>',
                            $twoauth_email
                        );

                    ?>
                    <span class="description" for="twoauth_email"><?php _e( 'Leave it blank, to use your account email address.', 'twoauth' ); ?></span>
                </td>
            </tr>
        </table>
    <?php }


    /**
     * Save profile fields
     */
    public function twoauth_save_profile_fields( $user_id ) {
        if( !current_user_can( 'edit_user', $user_id ) ) {
            return false;
        }

        $twoauth_app = ( isset( $_POST['twoauth_app'] ) ) ? intval( $_POST['twoauth_app'] ) : 0;
        $twoauth_apppw = $_POST['twoauth_apppw'];
        $twoauth_email = $_POST['twoauth_email'];

        update_user_meta(
            $user_id,
            self::$_app_field,
            $twoauth_app
        );

        if( !empty( $twoauth_apppw ) ) {
            update_user_meta(
                $user_id,
                self::$_apppw_field,
                wp_hash_password( trim( $twoauth_apppw ) )
            );
        }

        update_user_meta(
            $user_id,
            self::$_email_field,
            sanitize_email( $twoauth_email )
        );
    }


    /**
     * Add jQuery and twoauth.js
     */
    public function add_scripts() {
        if( !wp_script_is( 'jquery', 'registered' ) ) {
            wp_register_script(
                'jquery',
                sprintf(
                    '%s%s',
                    TWOAUTH_PLUGIN_URL,
                    '/js/jquery-1.11.1.min.js'
                ),
                array(),
                null
            );
        }
        wp_enqueue_script( 'jquery' );

        wp_register_script(
            'twoauth',
            sprintf(
                '%s%s',
                TWOAUTH_PLUGIN_URL,
                '/js/twoauth.js'
            ),
            array(),
            null,
            true
        );
        wp_enqueue_script( 'twoauth' );

        wp_localize_script(
            'twoauth',
            'twoauth_ajax_vars',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'ajax_nonce' => wp_create_nonce( self::$_nonce_field )
            )
        );
    }


    /**
     * Extends the login form with a bunch of echo commands
     */
    public function loginform() {
        echo "\t<p>\n";
        echo "\t\t<button type=\"button\" id=\"btn_twoauth\" class=\"button button-primary button-small\" style=\"float:none;width:100%;margin-bottom:8px;\">". __('Get TwoAuth Token', 'twoauth') ."</button>\n";
        echo "\t</p>\n";
        echo "\t<p>\n";
        echo "\t\t<label for=\"user_twoauth\">". __('TwoAuth Token', 'twoauth') ."<br>";
        echo "\t\t<input type=\"text\" name=\"user_twoauth\" id=\"user_twoauth\" class=\"input\" value=\"\" size=\"20\"></label>\n";
        echo "\t</p>\n";
    }


    /**
     * Ajax callback function
     */
    public function twoauth_ajax_callback() {
        // Ajax nonce check
        if( !check_ajax_referer( self::$_nonce_field, 'ajax_nonce', false ) ) {
            die();
        }

        $user_login = sanitize_user( $_POST['user_login'] );

        $user = get_user_by( 'login', $user_login );
        if( false === $user ) {
            printf(
                '<div id="login_error"><strong>%s:</strong> %s<br></div>',
                __('TwoAuth ERROR', 'twoauth'),
                __('Invalid Username.', 'twoauth')
            );
            die();
        }

        $twoauth_email = get_user_meta(
            $user->ID,
            self::$_email_field,
            true
        );

        $user_email = ( !empty( $twoauth_email ) ) ? $twoauth_email : $user->user_email;

        $token = wp_generate_password( 5, false );
        update_user_meta(
            $user->ID,
            self::$_token_field,
            wp_hash_password( $token )
        );

        set_site_transient(
            self::$_token_field . $user->ID,
            1,
            5 * MINUTE_IN_SECONDS
        );

        wp_mail(
            $user_email,
            __( 'Your TwoAuth Token', 'twoauth' ),
            $token
        );

        printf(
            '<p class="message"><strong>%s:</strong> %s<br></p>',
            __( 'TwoAuth', 'twoauth' ),
            __( 'Token sent via email. <strong>Valid for five minutes.</strong>', 'twoauth' )
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
    public function check_token( $user, $username = '', $password = '' ) {
        $userstate = $user;

        $user = get_user_by( 'login', $username );
        if( false === $user ) {
            return $userstate;
        }

        if( !get_site_transient( self::$_token_field . $user->ID ) ) {
            delete_user_meta(
                $user->ID,
                self::$_token_field
            );
        }

        $user_meta = get_user_meta( $user->ID );

        $twoauth_token = ( isset( $user_meta[self::$_token_field][0] ) ) ?
            $user_meta[self::$_token_field][0] :
            '';

        $twoauth_app = ( isset( $user_meta[self::$_app_field][0] ) ) ?
            $user_meta[self::$_app_field][0] :
            '0';

        $twoauth_apppw = ( isset( $user_meta[self::$_apppw_field][0] ) ) ?
            $user_meta[self::$_apppw_field][0] :
            '';

        $user_twoauth = sanitize_text_field( trim( $_POST['user_twoauth'] ) );


        if(wp_check_password( $user_twoauth, $twoauth_token, $user->ID) ) {

            return $userstate;

        } else {

            if( $twoauth_app === '1' && wp_check_password( $password, $twoauth_apppw, $user->ID ) && (defined('XMLRPC_REQUEST') || defined('APP_REQUEST')) ) {

                return new WP_User( $user->ID );

            } else {

                return new WP_Error(
                    'invalid_twoauth_token',
                    __( '<strong>TwoAuth ERROR:</strong> Invalid or expired token.', 'twoauth' )
                );

            }

        }

        return $userstate;
    }

}

?>