<div class="wrap">
    <h2>CCB Report</h2>

    <table id="example" class="cell-border" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th>Name</th>
            <th>Created</th>
            <th>Active</th>
            <th>Report</th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <th>Name</th>
            <th>Created</th>
            <th>Active</th>
            <th>Report</th>
        </tr>
        </tfoot>
        <tbody>
        <?php
        foreach ($this->get('forms') as $v) {
            ?>
            <tr>
                <td><?php echo $v['title'] ?></td>
                <td><?php echo $v['date_created'] ?></td>
                <td><?php echo $v['is_active'] == '1' ? "Yes" : "No" ?></td>
                <td><a href="<?php echo admin_url() . 'admin.php?page=ccb_report&form_id=' . $v['id'] ?>" class="button">View</a></td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>

<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('#example').DataTable();
    });
</script>