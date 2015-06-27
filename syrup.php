<?php
/*
Plugin Name: Syrup
Description: Provides features to manage a database of travel spots.
Author: Yuki Kodama
Author URI: http://endflow.net/
Version: 1.0.0
License: MIT
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define( 'SYRUP_VERSION', '0.1.0' );
define( 'SYRUP_DB_VERSION', '2' );
define( 'SYRUP__MINIMUM_WP_VERSION', '3.9' );
define( 'SYRUP__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SYRUP__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

register_activation_hook( __FILE__, array( 'Syrup', 'plugin_install' ) );

require_once( SYRUP__PLUGIN_DIR . 'class.syrup.php' );
add_action( 'init', array( 'Syrup', 'init' ) );

if ( is_admin() ) {
    require_once( SYRUP__PLUGIN_DIR . 'class.syrup-admin.php' );
    add_action( 'init', array( 'Syrup_Admin', 'init' ) );
    add_action( 'plugins_loaded', array( 'Syrup', 'hook_plugins_loaded' ) );
}
