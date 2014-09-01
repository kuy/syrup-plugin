<?php

class Syrup_Admin {
    private static $initiated = false;

    public static function init() {
        if ( ! self::$initiated ) {
            self::init_hooks();
        }
    }

    private static function init_hooks() {
        self::$initiated = true;

        add_action( 'admin_menu', array( 'Syrup_Admin', 'setup_menu' ) );
        add_action( 'admin_post_syrup_shops_create', array( 'Syrup_Admin', 'action_shops_create' ) );
        add_action( 'admin_post_syrup_shops_update', array( 'Syrup_Admin', 'action_shops_update' ) );
    }

    public static function setup_menu() {
        add_menu_page( 'Shops', 'Shops', 'manage_options', 'syrup', array( 'Syrup_Admin', 'view_shops_index' ) );
        add_submenu_page( 'syrup', 'Groups', 'Groups', 'manage_options', 'syrup-groups', array( 'Syrup_Admin', 'view_groups_index' ) );

        add_submenu_page( NULL, 'Add New Shop', '', 'manage_options', 'syrup-shops-new', array( 'Syrup_Admin', 'view_shops_new' ) );
        add_submenu_page( NULL, 'Edit Shop', '', 'manage_options', 'syrup-shops-edit', array( 'Syrup_Admin', 'view_shops_edit' ) );
        add_submenu_page( NULL, 'Add New Group', '', 'manage_options', 'syrup-groups-new', array( 'Syrup_Admin', 'view_groups_new' ) );
    }

    public static function view_shops_index() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'syrup_shops';
        $shops = $wpdb->get_results(
            "
            SELECT *
            FROM $table_name
            LIMIT 10
            "
        , ARRAY_A );

        Syrup::view( 'shops/index', array( 'shops' => $shops ) );
    }

    public static function view_shops_new() {
        Syrup::view( 'shops/new' );
    }

    public static function view_shops_edit() {
        global $wpdb;

        $shop_id = $_GET['shop_id'];

        $table_name = $wpdb->prefix . 'syrup_shops';
        $shop = $wpdb->get_row(
            "
            SELECT *
            FROM $table_name
            WHERE shop_id = $shop_id
            LIMIT 1
            "
        , ARRAY_A );

        Syrup::view( 'shops/edit', array( 'shop' => $shop ) );
    }

    public static function view_groups_index() {
        Syrup::view( 'groups/index' );
    }

    public static function view_groups_new() {
        Syrup::view( 'groups/new' );
    }

    public static function action_shops_create() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'syrup_shops';
        $wpdb->insert( $table_name, array(
            'name' => $_POST['shop_name'],
            'lat' => $_POST['shop_lat'],
            'lng' => $_POST['shop_lng'],
            'url' => $_POST['shop_url'],
            'post_id' => $_POST['shop_post_id'],
            'group_id' => $_POST['shop_group_id'],
        ), array( '%s', '%f', '%f', '%s', '%d', '%d' ) );

        wp_safe_redirect( self::url_shops_index() );
    }

    public static function action_shops_update() {
        global $wpdb;

        $shop_id = $_POST['shop_id'];

        $table_name = $wpdb->prefix . 'syrup_shops';
        $result = $wpdb->update( $table_name, array(
            'name' => $_POST['shop_name'],
            'lat' => $_POST['shop_lat'],
            'lng' => $_POST['shop_lng'],
            'url' => $_POST['shop_url'],
            'post_id' => $_POST['shop_post_id'],
            'group_id' => $_POST['shop_group_id'],
        ), array( 'shop_id' => $shop_id ), array( '%s', '%f', '%f', '%s', '%d', '%d' ), array( '%d' ) );

        // TODO: handle error

        wp_safe_redirect( self::url_shops_index() );
    }

    public static function url_shops_index() {
        $base = admin_url( 'admin.php' );
        $url = add_query_arg( array( 'page' => 'syrup' ), $base );
        return $url;
    }

    public static function url_shops_edit( $shop_id ) {
        $base = admin_url( 'admin.php' );
        $url = add_query_arg( array( 'page' => 'syrup-shops-edit', 'shop_id' => $shop_id ), $base );
        return $url;
    }
}
