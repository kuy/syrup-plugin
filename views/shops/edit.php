<div class="wrap">
    <h2>Edit Shop</h2>

    <p></p>

    <form action="admin-post.php" method="post" id="updateshop">
        <input type="hidden" name="action" value="syrup_shops_update">
        <input type="hidden" name="shop_id" value="<?= $shop['shop_id'] ?>">

        <div id="syrup-location-preview-map" style="width: 320px; height: 320px;"></div>

        <table class="form-table">
            <tbody>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="shop_name">Name</label>
                    </th>
                    <td>
                        <input type="text" id="shop_name" name="shop_name" value="<?= $shop['name'] ?>">
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="shop_location">Location</label>
                    </th>
                    <td>
                        <div class="syrup-location-preview" data-map="syrup-location-preview-map">
                            <input type="text" id="shop_lat" name="shop_lat" class="half-text lat" value="<?= $shop['lat'] ?>">
                            <input type="text" id="shop_lng" name="shop_lng" class="half-text lng" value="<?= $shop['lng'] ?>">
                        </div>
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row">
                        <label for="shop_url">URL</label>
                    </th>
                    <td>
                        <input type="text" id="shop_url" name="shop_url" value="<?= $shop['url'] ?>">
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row">
                        <label for="shop_post_id">Post</label>
                    </th>
                    <td>
                        <select id="shop_post_id" name="shop_post_id" value="<?= $shop['post_id'] ?>">
                            <option value=""></option>
                            <?php foreach ( get_posts( array( 'posts_per_page' => 200, 'post_status' => 'any' ) ) as $post ): ?>
                            <option value="<?= $post->ID ?>" <?= $shop['post_id'] == $post->ID ? 'selected="selected"' : '' ?>>
                                <?= $post->post_title ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <input type="submit" class="button button-primary" value="Save">
        </p>
    </form>

    <script>
        window.Syrup = {};
        window.Syrup.hours = <?= wp_json_encode( $hours ) ?>;
    </script>

    <form action="admin-post.php" method="post">
        <input type="hidden" name="action" value="syrup_shop_hours_update">
        <input type="hidden" name="shop_id" value="<?= $shop['shop_id'] ?>">

        <div id="syrup-shop-hours-editor"></div>

        <p class="submit">
            <input type="submit" class="button button-primary" value="Save">
        </p>
    </form>
</div>
