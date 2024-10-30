<?php
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

$campaign = INgageHubConnectCampaign::load_one($_REQUEST['campaign_id']);

if ($campaign == null) {
    INgageHubConnectMessages::stdMessageCampaignNotFound();

} else {
    $campaign->destroy();

    com_ingagehub_clear_cached_loadables();

    INgageHubConnectMessages::stdMessageCampaignDeleted();
}

wp_redirect(add_query_arg(array(
    'action' => false,
    'campaign_id' => false,
    'attachment_id' => false,
    'response_id' => false
)));

exit;
?>
