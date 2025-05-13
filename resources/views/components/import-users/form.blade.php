<form
    method="post"
    enctype="multipart/form-data"
    action=""
>
    <table class="form-table widefat wp-list-table">
        <tr>
            <td>
                <label for="kleinweb-auth-import-users-form-file-upload">
                    CSV file
                </label>
            </td>
            <td>
                <input
                    type="file"
                    id="kleinweb-auth-import-users-form-file-upload"
                    name="file"
                    required
                    class="all-options"
                    accept="text/csv"
                />
            </td>
        </tr>
    </table>

    <p class="submit">
        <button type="submit" class="button-primary">Import Users</button>
    </p>

    @php(wp_nonce_field('kleinweb-auth-import-users'))

</form>
