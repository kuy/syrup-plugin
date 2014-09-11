<div class="wrap">
    <h2>Edit Group</h2>

    <p></p>

    <form action="admin-post.php" method="post" id="updategroup">
        <input type="hidden" name="action" value="syrup_groups_update">
        <input type="hidden" name="group_id" value="<?= $group['group_id'] ?>">

        <table class="form-table">
            <tbody>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="group_name">Name</label>
                    </th>
                    <td>
                        <input type="text" id="group_name" name="group_name" value="<?= $group['name'] ?>">
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <input type="submit" class="button button-primary" value="Save">
        </p>
    </form>

    <h2>Shops</h2>

    <p></p>

    <ul>
        <?php foreach ( Syrup::get_shops_by_group( $group['group_id'] ) as $shop ): ?>
        <li>
            <a href="<?= Syrup_Admin::url_shops_edit( $shop['shop_id'] ) ?>">
                <?= $shop['name'] ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
