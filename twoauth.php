<?php

/*
Plugin Name: TwoAuth
Plugin URI: http://elf02.de/2014/06/17/twoauth-wordpress-plugin/
Description: Simple Two-Factor authentication for WordPress via email. Based on the 2-Step-Verification Plugin by Sergej Müller @wpseo.
Version: 1.0.1
Author: ChrisB
Author URI: http://elf02.de
License: MIT
*/

defined('ABSPATH') OR exit;


define('PLUGIN_FILE_TWOAUTH', __FILE__);

require_once(
    sprintf(
        '%s/inc/%s.class.php',
        dirname(__FILE__),
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