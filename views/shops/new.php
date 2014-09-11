<div class="wrap">
    <h2>Add New Shop</h2>

    <p></p>

    <form action="admin-post.php" method="post" id="createshop">
        <input type="hidden" name="action" value="syrup_shops_create">

        <table class="form-table">
            <tbody>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="shop_name">Name</label>
                    </th>
                    <td>
                        <input type="text" id="shop_name" name="shop_name">
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="shop_location">Location</label>
                    </th>
                    <td>
                        <input type="text" id="shop_lat" name="shop_lat" class="half-text">
                        <input type="text" id="shop_lng" name="shop_lng" class="half-text">
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row">
                        <label for="shop_url">URL</label>
                    </th>
                    <td>
                        <input type="text" id="shop_url" name="shop_url">
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row">
                        <label for="shop_post_id">Post</label>
                    </th>
                    <td>
                        <select id="shop_post_id" name="shop_post_id">
                            <option value="" selected="selected"></option>
                            <?php foreach ( get_posts( array( 'posts_per_page' => 200, 'post_status' => 'any' ) ) as $post ): ?>
                            <option value="<?= $post->ID ?>">
                                <?= $post->post_title ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row">
                        <label for="shop_group_id">Group</label>
                    </th>
                    <td>
                        <select id="shop_group_id" name="shop_group_id">
                            <option value="" selected="selected"></option>
                            <?php foreach ( Syrup::get_groups() as $group ): ?>
                            <option value="<?= $group['group_id'] ?>">
                                <?= $group['name'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <input type="submit" class="button button-primary" value="Add New Shop">
        </p>
    </form>
</div>
