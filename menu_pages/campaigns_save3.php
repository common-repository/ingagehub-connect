<?php
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

$campaign = INgageHubConnectCampaign::load_one($_REQUEST['campaign_id']);
$attachment = $campaign->get_attachment($_REQUEST['attachment_id']);
$response = null;

if ((int)$_REQUEST['response_id'] == 0) {
    $response = new INgageHubConnectCampaignAttachmentContentsResponse();

} else {
    $response = $attachment->get_response($_REQUEST['response_id']);
}

if ($response == null) {
    INgageHubConnectMessages::stdMessageResponseNotFound();

} else {
    $response->label = $_REQUEST['ih_admin_response_label'];

    $attachment->contents->save_response($response);

    INgageHubConnectMessages::stdMessageResponseSaved();
}

wp_redirect(add_query_arg(array(
    'action' => 'edit_attachment',
    'campaign_id' => $campaign->id,
    'attachment_id' => $attachment->id,
    'response_id' => false
)));

exit;
?>
