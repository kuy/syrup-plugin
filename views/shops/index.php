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
                <th>Map?</th>
                <th>Shop Hours?</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $shops as $shop ): ?>
            <tr>
                <td><?= $shop['shop_id'] ?></td>
                <td>
                    <a href="<?= Syrup_Admin::url_shops_edit( $shop['shop_id'] ) ?>">
                        <?= $shop['name'] ?>
                    </a>
                </td>
                <td>
                    <?php
                    $post = get_post( $shop['post_id'] );
                    if ($post): ?>
                        <a href="<?= get_edit_post_link( $post->ID ) ?>">
                            <?= $post->post_title ?>
                        </a>
                    <?php endif; ?>
                </td>
                <td><?= Syrup_Admin::has_map( $shop ) ? 'YES' : 'NO' ?></td>
                <td><?= Syrup_Admin::has_shop_hours( $shop ) ? 'YES' : 'NO' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
