<div class="wrap">
    <h2>Add New Group</h2>

    <p></p>

    <form action="admin-post.php" method="post" id="creategroup">
        <input type="hidden" name="action" value="syrup_groups_create">

        <table class="form-table">
            <tbody>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="group_name">Name</label>
                    </th>
                    <td>
                        <input type="text" id="group_name" name="group_name">
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <input type="submit" class="button button-primary" value="Add New Group">
        </p>
    </form>
</div>
