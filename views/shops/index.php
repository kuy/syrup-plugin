<div class="wrap">
    <h2>
        Shops
        <a href="admin.php?page=syrup-shops-new" class="add-new-h2">Add New</a>
    </h2>

    <table class="wp-list-table widefat fixed">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Post</th>
                <th>Group</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $shops as $shop ): ?>
            <tr>
                <td><?php echo $shop['shop_id']; ?></td>
                <td>
                    <a href="<?php echo Syrup_Admin::url_shops_edit( $shop['shop_id'] ); ?>">
                        <?php echo $shop['name']; ?>
                    </a>
                </td>
                <td>
                    <?php
                    $post = get_post( $shop['post_id'] );
                    if ($post): ?>
                        <a href="<?php echo get_edit_post_link( $post->ID ); ?>">
                            <?php echo $post->post_title; ?>
                        </a>
                    <?php endif; ?>
                </td>
                <td><?php echo $shop['group_id']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
