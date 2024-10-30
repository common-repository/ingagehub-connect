<?php
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

$campaign = INgageHubConnectCampaign::load_one($_REQUEST['campaign_id']);
$attachment = null;
$is_new = false;

if ((int)$_REQUEST['attachment_id'] == 0) {
    $attachment = new INgageHubConnectCampaignAttachment();
    $is_new = true;

} else {
    $attachment = $campaign->get_attachment($_REQUEST['attachment_id']);
}

if ($attachment == null) {
    INgageHubConnectMessages::stdMessageAttachmentNotFound();

} else {
    $attachment->contents->question_text = $_REQUEST['ih_admin_question_text'];
    $attachment->contents->question_layout = (int)$_REQUEST['ih_admin_question_layout'];
    $attachment->contents->response_format = $_REQUEST['ih_admin_response_format'];
    $attachment->contents->has_other_box = isset($_REQUEST['ih_admin_has_other_box']) ? '1' : '0';

    $campaign->save_attachment($attachment);

    INgageHubConnectMessages::stdMessageAttachmentSaved();
}

wp_redirect(add_query_arg(array(
    'action' => $is_new ? 'edit_attachment' : 'edit_campaign',
    'campaign_id' => $campaign->id,
    'attachment_id' => $is_new ? $attachment->id : false,
    'response_id' => false
)));

exit;
?>
