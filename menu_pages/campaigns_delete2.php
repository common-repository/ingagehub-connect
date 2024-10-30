<?php
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

$campaign = INgageHubConnectCampaign::load_one($_REQUEST['campaign_id']);
$attachment = $campaign->get_attachment($_REQUEST['attachment_id']);

if ($attachment == null) {
    INgageHubConnectMessages::stdMessageAttachmentNotFound();

} else {
    $campaign->remove_attachment($attachment);

    INgageHubConnectMessages::stdMessageAttachmentDeleted();
}

wp_redirect(add_query_arg(array(
    'action' => 'edit_campaign',
    'campaign_id' => $campaign->id,
    'attachment_id' => false,
    'response_id' => false
)));

exit;
?>
