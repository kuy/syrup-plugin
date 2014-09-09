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

        add_action( 'wp_enqueue_scripts', array( 'Syrup', 'hook_wp_enqueue_scripts' ) );
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

    public static function get_shops_by_ids( $shop_ids ) {
        global $wpdb;

        if ( count( $shop_ids ) === 0 ) {
            return array();
        }

        $list = join( ',', $shop_ids );

        $table_name = $wpdb->prefix . 'syrup_shops';
        $shops = $wpdb->get_results(
            "
            SELECT *
            FROM $table_name
            WHERE shop_id IN ($list)
            LIMIT 200
            "
        , ARRAY_A );

        return $shops;
    }

    public static function get_shops_by_category( $category ) {
        global $wpdb;

        $shops = array();
        $table_name = $wpdb->prefix . 'syrup_shops';
        $cat = get_category_by_slug( $category );
        $posts = get_posts( array( 'category' => $cat->term_id, 'posts_per_page' => 200 ) );

        foreach ( $posts as $post ) {
            $items = self::get_shops( $post->ID );
            $shops = array_merge( $shops, $items );
        }

        return $shops;
    }

    public static function get_shops_of_open( $category ) {
        global $wpdb;

        $now = intval( strftime( '%H%M' ), 10 ) + 900;
        if ( 2400 <= $now ) {
            $now -= 2400;
        }

        $table_name = $wpdb->prefix . 'syrup_shop_hours';
        $shop_hours = $wpdb->get_results(
            "
            SELECT shop_id
            FROM $table_name
            WHERE open <= $now AND close > $now
            GROUP BY shop_id
            LIMIT 100
            "
        , ARRAY_A );

        $shop_ids = array();
        foreach ( $shop_hours as $shop_hour ) {
            array_push( $shop_ids, $shop_hour['shop_id'] );
        }

        return self::get_shops_by_ids( $shop_ids );
    }

    public static function get_shop_hours( $shop_id ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'syrup_shop_hours';
        $shop_hours = $wpdb->get_results(
            "
            SELECT *
            FROM $table_name
            WHERE shop_id = $shop_id
            LIMIT 20
            "
        , ARRAY_A );

        return $shop_hours;
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
        wp_enqueue_script( 'syrup-core', SYRUP__PLUGIN_URL . 'js/core.js', array( 'jquery' ) );
    }

    public static function hook_the_content( $content ) {
        $target_id = get_the_ID();

        if ( is_page( $target_id ) ) {
            $cat = get_post_meta( $target_id, 'syrup-category', true );
            $type = get_post_meta( $target_id, 'syrup-type', true );

            switch ($type) {
                case 'area':
                    $shops = self::get_shops_by_category( $cat );
                    break;
                case 'now':
                    $shops = self::get_shops_of_open( $cat );
                    break;
            }
        } else if ( is_single( $target_id ) ) {
            $shops = self::get_shops( $target_id );
        } else {
            return $content;
        }

        $items = array();
        foreach ( $shops as $shop ) {
            $items[] = "{
                name: '{$shop['name']}',
                coordinate: '{$shop['lat']}, {$shop['lng']}'
            }";
        }
        $items = join( ', ', $items );
        $content .= "<script>SPOTS = [{$items}];</script>";

        $content .= '<ul>';
        foreach ( $shops as $shop ) {
            $content .= "<li>{$shop['name']}</li>";
        }
        $content .= '</ul>';

        $content .= '<div id="syrup-map" style="width: 640px; height: 320px;"></div>';

        return $content;
    }
}
