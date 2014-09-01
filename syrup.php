<?php
/*
Plugin Name: Syrup
Description: Location spices to WordPress.
Version: 0.1.0
Author: Yuki Kodama
Author URI: http://endflow.net/
License: MIT
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define( 'SYRUP_VERSION', '0.1.0' );
define( 'SYRUP_DB_VERSION', '1' );
define( 'SYRUP__MINIMUM_WP_VERSION', '3.9' );
define( 'SYRUP__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SYRUP__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

register_activation_hook( __FILE__, array( 'Syrup', 'plugin_install' ) );

require_once( SYRUP__PLUGIN_DIR . 'class.syrup.php' );
add_action( 'init', array( 'Syrup', 'init' ) );

if ( is_admin() ) {
    require_once( SYRUP__PLUGIN_DIR . 'class.syrup-admin.php' );
    add_action( 'init', array( 'Syrup_Admin', 'init' ) );
}
