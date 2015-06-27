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

        add_action( 'wp_ajax_syrup_get_tags', array( 'Syrup', 'action_get_tags' ) );
        add_action( 'wp_ajax_syrup_get_shops', array( 'Syrup', 'action_get_shops' ) );
        add_action( 'wp_ajax_nopriv_syrup_get_tags', array( 'Syrup', 'action_get_tags' ) );
        add_action( 'wp_ajax_nopriv_syrup_get_shops', array( 'Syrup', 'action_get_shops' ) );
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
            LIMIT 200
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

    public static function get_shops_by_tags( $tags ) {
        $shops = array();
        $posts = get_posts( array( 'tag' => $tags, 'posts_per_page' => 200 ) );

        foreach ( $posts as $post ) {
            $items = self::get_shops( $post->ID );
            $shops = array_merge( $shops, $items );
        }

        return $shops;
    }

    public static function get_shops_of_open() {
        global $wpdb;

        $now_list = array( intval( strftime( '%H%M' ), 10 ) + 900 );
        if ( 2400 <= $now_list[0] ) {
            array_push( $now_list, $now_list[0] - 2400 );
        }

        $cond_list = array();
        foreach ( $now_list as $now ) {
            array_push( $cond_list, "(open <= $now AND IF(last_order > 0, last_order, close) > $now)" );
        }

        $time_cond = join( ' OR ', $cond_list );
        $wd_cond = 'wd' . strftime( '%w' ) . ' = 1';

        $table_name = $wpdb->prefix . 'syrup_shop_hours';
        $shop_hours = $wpdb->get_results(
            "
            SELECT shop_id
            FROM $table_name
            WHERE ($time_cond) AND ($wd_cond)
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

    private static function determine_charset_collate() {
        global $wpdb;

        $charset_collate = '';
        if ( ! empty( $wpdb->charset ) ) {
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        }
        if ( ! empty( $wpdb->collate ) ) {
            $charset_collate .= " COLLATE {$wpdb->collate}";
        }

        return $charset_collate;
    }

    public static function plugin_install() {
        add_option( 'syrup_db_version', '0' );
    }

    public static function hook_plugins_loaded() {
        $db_version = get_option( 'syrup_db_version' );
        while ( (int)$db_version < (int)SYRUP_DB_VERSION ) {
            $next_version = (int)$db_version + 1;
            self::execute_migration( (string)$next_version );
            $db_version = get_option( 'syrup_db_version' );
        }
    }

    private static function execute_migration( $version ) {
        global $wpdb;

        switch ($version) {
            case '1':
                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

                $charset_collate = self::determine_charset_collate();

                $table_name = $wpdb->prefix . 'syrup_shops';
                $sql = "CREATE TABLE $table_name (
                        shop_id mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
                        name tinytext NOT NULL,
                        lat double,
                        lng double,
                        url text,
                        post_id bigint(20) UNSIGNED,
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

                break;

            case '2':
                $table_name = $wpdb->prefix . 'syrup_shop_hours';

                // Add column
                $wpdb->query(
                    "
                    ALTER TABLE $table_name
                    ADD last_order smallint(6) UNSIGNED NOT NULL
                    AFTER open;
                    "
                );

                // Set default values
                $wpdb->query(
                    "
                    UPDATE $table_name
                    SET last_order = close;
                    "
                );

                break;
        }

        update_option( 'syrup_db_version', $version );
    }

    public static function hook_wp_enqueue_scripts() {
        wp_enqueue_style( 'syrup-pure', SYRUP__PLUGIN_URL . 'css/pure.css' );
        wp_enqueue_style( 'syrup-style', SYRUP__PLUGIN_URL . 'css/style.css' );
        wp_enqueue_script( 'syrup-google-maps', '//maps.googleapis.com/maps/api/js?key=AIzaSyBKVdsaN43VQGayTc1thF-faFjpzZUrqCo' );
        wp_enqueue_script( 'syrup-app', SYRUP__PLUGIN_URL . 'js/app.js' );
    }

    public static function hook_the_content( $content ) {
        $endpoint = admin_url( 'admin-ajax.php' );
        $content .= "<script>
            ENDPOINT = '{$endpoint}';
        </script>";

        $content .= '<div id="syrup-container"></div>';

        return $content;


        $target_id = get_the_ID();

        if ( is_page( $target_id ) ) {
            $tags = get_post_meta( $target_id, 'syrup-tags', true );
            $type = get_post_meta( $target_id, 'syrup-type', true );

            switch ($type) {
                case 'area':
                    $shops = self::get_shops_by_tags( $tags );
                    break;
                case 'now':
                    $shops = self::get_shops_of_open();
                    break;
            }
        } else if ( is_single( $target_id ) ) {
            $shops = self::get_shops( $target_id );
        } else {
            return $content;
        }

        $items = array();
        foreach ( $shops as $shop ) {
            $permalink = get_permalink( $shop['post_id'] );
            $items[] = "{
                name: '{$shop['name']}',
                permalink: '{$permalink}',
                coordinate: '{$shop['lat']}, {$shop['lng']}'
            }";
        }
        $items = join( ', ', $items );
        $content .= "<script>
            ENDPOINT = '{$endpoint}';
            SPOTS = [{$items}];
        </script>";

        $content .= '<div id="syrup-map"></div>';

        $content .= '<ul>';
        foreach ( $shops as $shop ) {
            $content .= "<li>{$shop['name']}</li>";
        }
        $content .= '</ul>';

        $content .= '<div>';
        $content .= '<input id="syrup-get-shops-tags" type="text" value="cafe+tokyo" />';
        $content .= '<button id="syrup-get-shops">GET shops</button>';
        $content .= '</div>';

        $content .= '<div id="syrup-container"></div>';

        // return $content;
    }

    public static function action_get_tags() {
        $tags = get_tags();
        $tag_group_labels = get_option( 'tag_group_labels', array() );

        $items = array();
        foreach ( $tags as $tag ) {
            $items[] = array(
                'term_id' => $tag->term_id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'term_group' => $tag_group_labels[$tag->term_group],
            );
        }

        wp_send_json_success( $items );
    }

    public static function action_get_shops() {
        $tags = $_GET['tags'];
        if ( !$tags ) {
            wp_send_json_error();
            return;
        }

        $now = $_GET['now'];

        $open_shops = self::get_shops_of_open();
        $open_ids = array();
        foreach ( $open_shops as $shop ) {
            $open_ids[] = $shop['shop_id'];
        }

        $items = array();
        $shops = self::get_shops_by_tags( $tags );
        foreach ( $shops as $shop ) {
            if ( $now == 'off' || in_array( $shop['shop_id'], $open_ids ) ) {
                $permalink = get_permalink( $shop['post_id'] );
                $thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $shop['post_id'] ) );
                $items[] = array(
                    'id' => $shop['shop_id'],
                    'name' => $shop['name'],
                    'post_url' => $permalink,
                    'thumbnail_url' => $thumb[0],
                    'lat' => $shop['lat'],
                    'lng' => $shop['lng'],
                );
            }
        }

        wp_send_json_success( $items );
    }
}
