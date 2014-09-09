<?php

/*
Plugin Name: TwoAuth
Plugin URI: http://elf02.de/2014/06/17/twoauth-wordpress-plugin/
Description: 2-Step-Verification for WordPress via email. Based on the 2-Step-Verification Plugin by Sergej Müller @wpseo.
Version: 1.0.2
Author: ChrisB
Author URI: http://elf02.de
License: MIT
*/

defined( 'ABSPATH' ) OR exit;

define( 'TWOAUTH_FILE', __FILE__ );
define( 'TWOAUTH_PLUGIN_DIR', untrailingslashit( plugin_dir_path( TWOAUTH_FILE ) ) );
define( 'TWOAUTH_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( TWOAUTH_FILE ) ), basename( TWOAUTH_FILE ) ) ) );


require_once(
    sprintf(
        '%s/inc/%s.class.php',
        TWOAUTH_PLUGIN_DIR,
        'twoauth'
    )
);


add_action(
    'plugins_loaded',
    array(
        'twoauth',
        'instance'
    )
);

?>