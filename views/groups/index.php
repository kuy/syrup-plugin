<div class="wrap">
    <h2>
        Groups
        <a href="admin.php?page=syrup-groups-new" class="add-new-h2">Add New</a>
    </h2>

    <table class="wp-list-table widefat fixed">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th># of Shops</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $groups as $group ): ?>
            <tr>
                <td><?= $group['group_id'] ?></td>
                <td>
                    <a href="<?= Syrup_Admin::url_groups_edit( $group['group_id'] ) ?>">
                        <?php echo $group['name']; ?>
                    </a>
                </td>
                <td><?= Syrup::get_num_of_shops( $group['group_id'] ) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
