<?php
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

INgageHubConnectCampaign::clear_cached();

INgageHubConnectMessages::stdMessageCampaignReloaded();

wp_redirect(add_query_arg(array(
    'action' => false,
    'campaign_id' => false,
    'attachment_id' => false,
    'response_id' => false,
)));

exit;
?>
