<?php

class Syrup {
    private static $initiated = false;

    public static function init() {
        if ( ! self::$initiated ) {
            self::init_hooks();
        }
    }

    private static function init_hooks() {
        self::$initiated = true;

        add_filter( 'wp_enqueue_scripts', array( 'Syrup', 'hook_wp_enqueue_scripts' ) );
        add_filter( 'the_content', array( 'Syrup', 'hook_the_content' ) );
    }

    public static function view( $name, array $args = array() ) {
        foreach ( $args AS $key => $val ) {
            $$key = $val;
        }

        $file = SYRUP__PLUGIN_DIR . 'views/'. $name . '.php';

        include( $file );
    }

    public static function get_shops( $post_id ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'syrup_shops';
        $shops = $wpdb->get_results(
            "
            SELECT *
            FROM $table_name
            WHERE post_id = $post_id
            LIMIT 10
            "
        , ARRAY_A );

        return $shops;
    }

    public static function plugin_install() {
        global $wpdb;

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $charset_collate = '';
        if ( ! empty( $wpdb->charset ) ) {
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        }
        if ( ! empty( $wpdb->collate ) ) {
            $charset_collate .= " COLLATE {$wpdb->collate}";
        }

        $table_name = $wpdb->prefix . 'syrup_shops';
        $sql = "CREATE TABLE $table_name (
                shop_id mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
                name tinytext NOT NULL,
                lat double,
                lng double,
                url text,
                post_id bigint(20) UNSIGNED,
                group_id mediumint(9) UNSIGNED,
                UNIQUE KEY id (shop_id)
                ) $charset_collate;";
        dbDelta( $sql );

        $table_name = $wpdb->prefix . 'syrup_shop_hours';
        $sql = "CREATE TABLE $table_name (
                shop_hour_id mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
                shop_id mediumint(9) UNSIGNED NOT NULL,
                open smallint(6) UNSIGNED NOT NULL,
                close smallint(6) UNSIGNED NOT NULL,
                wd0 bool DEFAULT false NOT NULL,
                wd1 bool DEFAULT false NOT NULL,
                wd2 bool DEFAULT false NOT NULL,
                wd3 bool DEFAULT false NOT NULL,
                wd4 bool DEFAULT false NOT NULL,
                wd5 bool DEFAULT false NOT NULL,
                wd6 bool DEFAULT false NOT NULL,
                UNIQUE KEY id (shop_hour_id)
                ) $charset_collate;";
        dbDelta( $sql );

        $table_name = $wpdb->prefix . 'syrup_groups';
        $sql = "CREATE TABLE $table_name (
                group_id mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
                name tinytext NOT NULL,
                UNIQUE KEY id (group_id)
                ) $charset_collate;";
        dbDelta( $sql );
    }

    public static function hook_wp_enqueue_scripts() {
        wp_enqueue_style( 'syrup-style', SYRUP__PLUGIN_URL . 'css/style.css' );
        wp_enqueue_script( 'syrup-google-maps', '//maps.googleapis.com/maps/api/js?key=AIzaSyBKVdsaN43VQGayTc1thF-faFjpzZUrqCo' );
        wp_enqueue_script( 'syrup-core', SYRUP__PLUGIN_URL . 'js/core.js' );
    }

    public static function hook_the_content( $content ) {
        $post_id = get_the_ID();

        $content .= '<div id="syrup-map" style="width: 640px; height: 320px;" />';

        $items = array();
        foreach ( self::get_shops( $post_id ) as $shop ) {
            $items[] = "{
                name: '{$shop['name']}',
                coordinate: '{$shop['lat']}, {$shop['lng']}'
            }";
        }
        $items = join( ', ', $items );
        $content .= "<script>SPOTS = [{$items}];</script>";

        $content .= '<ul>';
        foreach ( self::get_shops( $post_id ) as $shop ) {
            $content .= "<li>{$shop['name']}</li>";
        }
        $content .= '</ul>';

        return $content;
    }
}
