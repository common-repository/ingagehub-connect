<?php
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

$campaign = null;
$is_new = false;

if ((int)$_REQUEST['campaign_id'] == 0) {
    $campaign = new INgageHubConnectCampaign();
    $is_new = true;

} else {
    $campaign = INgageHubConnectCampaign::load_one($_REQUEST['campaign_id']);
}

if ($campaign == null) {
    INgageHubConnectMessages::stdMessageCampaignNotFound();

} else {
    $campaign->name = $_REQUEST['ih_admin_campaign_name'];

    $new_id = $campaign->save(isset($_REQUEST['ih_admin_save_and_publish_campaign']));

    if ($new_id > 0) {
        INgageHubConnectMessages::stdMessageCampaignSaved();

        com_ingagehub_clear_cached_loadables();

        $campaign = INgageHubConnectCampaign::load_one($new_id);

    } else {
        INgageHubConnectMessages::stdMessageCampaignNotSaved();
        $is_new = false; // even if new, not saved so can't go to edit
    }
}

wp_redirect(add_query_arg(array(
    'action' => $is_new ? 'edit_campaign' : false,
    'campaign_id' => $is_new ? $campaign->id : false,
    'attachment_id' => false,
    'response_id' => false
)));

exit;
?>
