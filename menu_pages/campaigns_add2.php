<?php
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

$campaign = INgageHubConnectCampaign::load_one($_REQUEST['campaign_id']);

if (!isset($_REQUEST['ih_admin_stage'])) { // template, qi, or blank?
    include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_form2.php';

} elseif ($_REQUEST['ih_admin_stage'] === 'select_template') { // select template
    include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_form2a.php';

} elseif ($_REQUEST['ih_admin_stage'] === 'find_qi_questions') { // answer QI questions
    include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_form2b1.php';

} elseif ($_REQUEST['ih_admin_stage'] === 'select_qi_question') { // select template
    include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_form2b2.php';

} elseif ($_REQUEST['ih_admin_stage'] === 'add_attachment') { // create
    $attachment = new INgageHubConnectCampaignAttachment();

    if (isset($_REQUEST['ih_admin_base_question_id']) && (int) $_REQUEST['ih_admin_base_question_id'] > 0) {
        $attachment = INgageHubConnectCampaignAttachment::from_template((int) $_REQUEST['ih_admin_base_question_id']);

        $campaign->save_attachment($attachment);
    }

    include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_form2c.php';
}
?>
