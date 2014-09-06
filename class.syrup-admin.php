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

        add_action( 'admin_enqueue_scripts', array( 'Syrup_Admin', 'hook_admin_enqueue_scripts' ) );
        add_action( 'admin_menu', array( 'Syrup_Admin', 'hook_admin_menu' ) );
        add_action( 'admin_post_syrup_shops_create', array( 'Syrup_Admin', 'action_shops_create' ) );
        add_action( 'admin_post_syrup_shops_update', array( 'Syrup_Admin', 'action_shops_update' ) );
        add_action( 'admin_post_syrup_shop_hours_update', array( 'Syrup_Admin', 'action_shop_hours_update' ) );
    }

    public static function hook_admin_enqueue_scripts() {
        wp_enqueue_style( 'syrup-admin', SYRUP__PLUGIN_URL . 'css/admin.css' );
        wp_enqueue_script( 'syrup-admin', SYRUP__PLUGIN_URL . 'js/admin.js' );
    }

    public static function hook_admin_menu() {
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

        $table_name = $wpdb->prefix . 'syrup_shop_hours';
        $hours = $wpdb->get_results(
            "
            SELECT *
            FROM $table_name
            WHERE shop_id = $shop_id
            "
        , ARRAY_A );

        Syrup::view( 'shops/edit', array( 'shop' => $shop, 'hours' => $hours ) );
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

    public static function action_shop_hours_update() {
        global $wpdb;

        $shop_id = $_POST['shop_id'];

        $table_name = $wpdb->prefix . 'syrup_shop_hours';

        // Delete all shop hours binded this shop
        $wpdb->delete( $table_name, array( 'shop_id' => $shop_id ), array( '%d' ) );

        // Enumerate new shop hours and insert them
        $i = 0;
        foreach ( $_POST['hour_open_h'] as $_ ) {
            foreach ( array( 'hour_open_h', 'hour_open_m', 'hour_close_h', 'hour_close_m' ) as $key ) {
                if ($_POST[$key][$i] == '') {
                    $_POST[$key][$i] = '0';
                }
            }

            $open = 100 * intval($_POST['hour_open_h'][$i]) + intval($_POST['hour_open_m'][$i]);
            $close = 100 * intval($_POST['hour_close_h'][$i]) + intval($_POST['hour_close_m'][$i]);

            $wpdb->insert( $table_name, array(
                'shop_id' => $shop_id,
                'open' => $open,
                'close' => $close,
                'wd0' => $_POST['hour_wd0'][$i] == 'on',
                'wd1' => $_POST['hour_wd1'][$i] == 'on',
                'wd2' => $_POST['hour_wd2'][$i] == 'on',
                'wd3' => $_POST['hour_wd3'][$i] == 'on',
                'wd4' => $_POST['hour_wd4'][$i] == 'on',
                'wd5' => $_POST['hour_wd5'][$i] == 'on',
                'wd6' => $_POST['hour_wd6'][$i] == 'on',
            ), array( '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d' ) );

            $i++;
        }

        wp_safe_redirect( self::url_shops_edit( $shop_id ) );
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
