<?php
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}
?>
<h1>Campaigns</h1>

<?php include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_table_filter.php'; ?>

<?php include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_table_sort.php'; ?>

<table>
    <thead>
    <tr>
        <th>Name</th>
        <th>Updated</th>
        <th>By</th>
        <th>Action</th>
    </tr>
    <tr>
        <th colspan="4">
            <a href="<?php echo add_query_arg(array(
                'action' => 'add_campaign',
                'campaign_id' => false,
                'attachment_id' => false,
                'response_id' => false
            )) ?>"
            ><img src="<?php echo com_ingagehub_plugin_dir_url() . 'images/add_24x24.png'; ?>" alt="Add" /><span>Add Campaign</span></a>
        </th>
    </tr>
    </thead>
    <tbody>
    <?php
    $campaigns = INgageHubConnectCampaign::load_all(isset($_SESSION['ih_connect_campaign_table_sort']) ? $_SESSION['ih_connect_campaign_table_sort'] : null);
    foreach ($campaigns->campaigns as $campaign) {
        if (isset($_SESSION['ih_connect_campaign_table_filter'])) {
            if (stripos($campaign->name, $_SESSION['ih_connect_campaign_table_filter']) === FALSE) {
                continue;
            }
        }
        ?>
        <tr>
            <td><?php echo INgageHubConnectMessages::truncate($campaign->name, 70) ?></td>
            <td><?php echo date_format(new DateTime($campaign->updated_at), "Y-m-d H:i:s") ?></td>
            <td><?php echo $campaign->updated_by ?></td>
            <td>
                <a href="<?php echo add_query_arg(array(
                        'action' => 'edit_campaign',
                        'campaign_id' => $campaign->id,
                        'attachment_id' => false,
                        'response_id' => false
                    )); ?>"
                ><img src="<?php echo com_ingagehub_plugin_dir_url() . 'images/edit_24x24.png'; ?>" alt="Edit" /></a>
                <a href="<?php echo add_query_arg(array(
                        'action' => 'delete_campaign',
                        'campaign_id' => $campaign->id,
                        'attachment_id' => false,
                        'response_id' => false
                    )); ?>"
                   data-confirm="<?php echo INgageHubConnectMessages::stdConfirmDeleteCampaign() ?>"
                ><img src="<?php echo com_ingagehub_plugin_dir_url() . 'images/delete_24x24.png'; ?>" alt="Delete" /></a>
            </td>
        </tr>
        <?php
    }
    ?>
    </tbody>
</table>
