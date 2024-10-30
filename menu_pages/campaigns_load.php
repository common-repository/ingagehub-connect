<?php
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

//
// Check for user logged in.
//

if (!is_user_logged_in()) {
    wp_redirect(network_admin_url());
    exit;
}

//
// Check for good INgageHub site connection.
//

$auth = new INgageHubConnectAuth();
if (!$auth->login()) {
    INgageHubConnectMessages::stdMessageConnectionFailed();

    wp_redirect(menu_page_url('ingagehub_menu', false));
    exit;
}

//
// If the action is to refresh, clear the session and reload.
//

if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'refresh') {
    com_ingagehub_clear_cached_loadables();
    wp_redirect(menu_page_url('ingagehub_menu_campaigns', false));
    exit;
}

//
// Check for clobbering pending changes to the current (cached) campaign.
//

if (INgageHubConnectCampaign::cached_needs_save() && (
        !isset($_REQUEST['campaign_id']) ||
        !isset($_REQUEST['action']) ||
        $_REQUEST['action'] == 'add_campaign' ||
        (int)$_REQUEST['campaign_id'] != INgageHubConnectCampaign::cached_id()
    )
) {
    INgageHubConnectMessages::stdMessageUnsavedChanges();

    wp_redirect(add_query_arg(array(
        'action' => 'edit_campaign',
        'campaign_id' => INgageHubConnectCampaign::cached_id(),
        'attachment_id' => false,
        'response_id' => false
    )));

    exit;
}

//
// Check for a valid campaign if required.
//

$campaign = null;

if (!isset($_REQUEST['action'])) {
    $campaign = new INgageHubConnectCampaign();
} elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_campaign') {
    $campaign = new INgageHubConnectCampaign();
} elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'save_campaign' && isset($_REQUEST['campaign_id']) && (int)$_REQUEST['campaign_id'] == 0) {
    $campaign = new INgageHubConnectCampaign();
} elseif (!isset($_REQUEST['campaign_id'])) {
    $campaign = new INgageHubConnectCampaign();
} elseif (isset($_REQUEST['campaign_id'])) {
    $campaign = INgageHubConnectCampaign::load_one($_REQUEST['campaign_id']);
}

if (is_null($campaign)) {
    INgageHubConnectMessages::stdMessageCampaignNotFound();

    wp_redirect(add_query_arg(array(
        'action' => false,
        'campaign_id' => false,
        'attachment_id' => false,
        'response_id' => false
    )));
    exit;
}

//
// Check for a valid attachment if required.
//

$attachment = null;

if (!isset($_REQUEST['action'])) {
    $attachment = new INgageHubConnectCampaignAttachment();
} elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_attachment') {
    $attachment = new INgageHubConnectCampaignAttachment();
} elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'save_attachment' && isset($_REQUEST['attachment_id']) && (int)$_REQUEST['attachment_id'] == 0) {
    $attachment = new INgageHubConnectCampaignAttachment();
} elseif (!isset($_REQUEST['attachment_id'])) {
    $attachment = new INgageHubConnectCampaignAttachment();
} elseif (isset($_REQUEST['attachment_id'])) {
    $attachment = $campaign->get_attachment($_REQUEST['attachment_id']);
}

if (is_null($attachment)) {
    INgageHubConnectMessages::stdMessageAttachmentNotFound();

    wp_redirect(add_query_arg(array(
        'action' => 'edit_campaign',
        'campaign_id' => $campaign->id,
        'attachment_id' => false,
        'response_id' => false
    )));
    exit;
}

//
// Check for a valid response if required.
//

$response = null;

if (!isset($_REQUEST['action'])) {
    $response = new INgageHubConnectCampaignAttachmentContentsResponse();
} elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_response') {
    $response = new INgageHubConnectCampaignAttachmentContentsResponse();
} elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'save_response' && isset($_REQUEST['response_id']) && (int)$_REQUEST['response_id'] == 0) {
    $response = new INgageHubConnectCampaignAttachmentContentsResponse();
} elseif (!isset($_REQUEST['response_id'])) {
    $response = new INgageHubConnectCampaignAttachmentContentsResponse();
} elseif (isset($_REQUEST['response_id'])) {
    $response = $attachment->get_response($_REQUEST['response_id']);
}

if (is_null($response)) {
    INgageHubConnectMessages::stdMessageResponseNotFound();

    wp_redirect(add_query_arg(array(
        'action' => 'edit_attachment',
        'campaign_id' => $campaign->id,
        'attachment_id' => $attachment->id,
        'response_id' => false
    )));
    exit;
}

//
// Process all of the actions that do redirects
//

if (isset($_REQUEST['action'])) {
    if ($_REQUEST['action'] == 'save_campaign') {
        include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_save1.php';
    } elseif ($_REQUEST['action'] == 'save_attachment') {
        include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_save2.php';
    } elseif ($_REQUEST['action'] == 'save_response') {
        include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_save3.php';
    } elseif ($_REQUEST['action'] == 'delete_campaign') {
        include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_delete1.php';
    } elseif ($_REQUEST['action'] == 'delete_attachment') {
        include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_delete2.php';
    } elseif ($_REQUEST['action'] == 'delete_response') {
        include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_delete3.php';
    } elseif ($_REQUEST['action'] == 'cancel_campaign') {
        include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_cancel1.php';
    } elseif ($_REQUEST['action'] == 'move_response_up' || $_REQUEST['action'] == 'move_response_down') {
        include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_move3.php';
    }
}

//
// Setup the page styles and scripts otherwise
//

wp_enqueue_style('ingagehub_admin_css', com_ingagehub_plugin_dir_url() . 'css/admin_style.min.css');
wp_enqueue_script('ingagehub_admin_js', com_ingagehub_plugin_dir_url() . 'js/admin.min.js');
?>
