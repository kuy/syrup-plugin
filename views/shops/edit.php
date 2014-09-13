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
                <tr class="form-field">
                    <th scope="row">
                        <label for="shop_group_id">Group</label>
                    </th>
                    <td>
                        <select id="shop_group_id" name="shop_group_id" value="<?= $shop['group_id'] ?>">
                            <option value=""></option>
                            <?php foreach ( Syrup::get_groups() as $group ): ?>
                            <option value="<?= $group['group_id'] ?>" <?= $shop['group_id'] == $group['group_id'] ? 'selected="selected"' : '' ?>>
                                <?= $group['name'] ?>
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

    <h2>
        Edit Shop Hours
        <a href="#" class="add-new-h2" id="syrup-shop-hours-editor-new">Add New</a>
    </h2>

    <?php
    $wd_list = array( 'wd0' => 'Sun', 'wd1' => 'Mon', 'wd2' => 'Tue',
                      'wd3' => 'Wed', 'wd4' => 'Thu', 'wd5' => 'Fri', 'wd6' => 'Sat' );
    ?>

    <div id="syrup-shop-hours-editor">
        <div id="syrup-shop-hours-editor-template">
            <li>
                <input type="text" name="hour_open_h[]" size="2" maxlength="2" />
                <span>:</span>
                <input type="text" name="hour_open_m[]" size="2" maxlength="2" />
                <span>-</span>
                <input type="text" name="hour_close_h[]" size="2" maxlength="2" />
                <span>:</span>
                <input type="text" name="hour_close_m[]" size="2" maxlength="2" />

                <span>(L.O. </span>
                <input type="text" name="hour_last_h[]" size="2" maxlength="2" />
                <span>:</span>
                <input type="text" name="hour_last_m[]" size="2" maxlength="2" />
                <span>)</span>

                <span class="wd-group">
                    <?php foreach ( $wd_list as $key => $wd ): ?>
                    <label class="syrup-toggle"">
                        <input type="hidden" name="hour_<?= $key ?>[]" />
                        <input type="checkbox" />
                        <?= $wd ?>
                    </label>
                    <?php endforeach; ?>
                </span>

                <a href="#" class="delete">Delete</a>
            </li>
        </div>

        <form action="admin-post.php" method="post">
            <input type="hidden" name="action" value="syrup_shop_hours_update">
            <input type="hidden" name="shop_id" value="<?= $shop['shop_id'] ?>">

            <ul>
                <?php foreach ( $hours as $shop_hour ): ?>
                <?php $open = sprintf( '%04d', $shop_hour['open'] ); ?>
                <?php $close = sprintf( '%04d', $shop_hour['close'] ); ?>
                <?php $last = sprintf( '%04d', $shop_hour['last_order'] ); ?>
                <li>
                    <input type="text" name="hour_open_h[]" value="<?= intval( mb_substr( $open, 0, 2 ) ) ?>" size="2" maxlength="2" />
                    <span>:</span>
                    <input type="text" name="hour_open_m[]" value="<?= intval( mb_substr( $open, 2, 2 ) ) ?>" size="2" maxlength="2" />
                    <span>-</span>
                    <input type="text" name="hour_close_h[]" value="<?= intval( mb_substr( $close, 0, 2 ) ) ?>" size="2" maxlength="2" />
                    <span>:</span>
                    <input type="text" name="hour_close_m[]" value="<?= intval( mb_substr( $close, 2, 2 ) ) ?>" size="2" maxlength="2" />

                    <span>(L.O. </span>
                    <input type="text" name="hour_last_h[]" value="<?= intval( mb_substr( $last, 0, 2 ) ) ?>" size="2" maxlength="2" />
                    <span>:</span>
                    <input type="text" name="hour_last_m[]" value="<?= intval( mb_substr( $last, 2, 2 ) ) ?>" size="2" maxlength="2" />
                    <span>)</span>

                    <span class="wd-group">
                        <?php foreach ( $wd_list as $key => $wd ): ?>
                        <label class="syrup-toggle"">
                            <input type="hidden" name="hour_<?= $key ?>[]" />
                            <input type="checkbox" <?= $shop_hour[$key] ? 'checked="checked"' : '' ?> />
                            <?= $wd ?>
                        </label>
                        <?php endforeach; ?>
                    </span>

                    <a href="#" class="delete">Delete</a>
                </li>
                <?php endforeach; ?>
            </ul>

            <p class="submit">
                <input type="submit" class="button button-primary" value="Save">
            </p>
        </form>
    </div>
</div>
