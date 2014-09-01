<div class="wrap">
    <h2>Edit Shop</h2>

    <p></p>

    <form action="admin-post.php" method="post">
        <input type="hidden" name="action" value="syrup_shops_update">
        <input type="hidden" name="shop_id" value="<?php echo $shop['shop_id']; ?>">

        <table class="form-table">
            <tbody>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="shop_name">Name</label>
                    </th>
                    <td>
                        <input type="text" id="shop_name" name="shop_name" value="<?php echo $shop['name']; ?>">
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="shop_location">Location</label>
                    </th>
                    <td>
                        <input type="text" id="shop_lat" name="shop_lat" value="<?php echo $shop['lat']; ?>">
                        <input type="text" id="shop_lng" name="shop_lng" value="<?php echo $shop['lng']; ?>">
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row">
                        <label for="shop_url">URL</label>
                    </th>
                    <td>
                        <input type="text" id="shop_url" name="shop_url" value="<?php echo $shop['url']; ?>">
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row">
                        <label for="shop_post_id">Post ID</label>
                    </th>
                    <td>
                        <input type="text" id="shop_post_id" name="shop_post_id" value="<?php echo $shop['post_id']; ?>">
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row">
                        <label for="shop_group_id">Group ID</label>
                    </th>
                    <td>
                        <input type="text" id="shop_group_id" name="shop_group_id" value="<?php echo $shop['group_id']; ?>">
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <input type="submit" class="button button-primary" value="Save">
        </p>
    </form>
</div>
