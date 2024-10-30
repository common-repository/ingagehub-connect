<?php
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

$campaign = INgageHubConnectCampaign::load_one($_REQUEST['campaign_id']);
$attachment = $campaign->get_attachment($_REQUEST['attachment_id']);

include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_form2c.php';
?>
