<?php
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

$campaign = new INgageHubConnectCampaign();

include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_form1.php';
?>
