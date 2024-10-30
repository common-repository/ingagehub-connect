<?php
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}
?>

<div id="ih_admin_campaigns">
    <img class="ih_admin_logo ih_admin_logo_small" src="<?php echo com_ingagehub_plugin_dir_url() . 'images/banner-541x71.png' ?>"/>

    <?php include com_ingagehub_plugin_dir_path() . 'menu_pages/message.php' ?>

    <?php
    if (isset($_REQUEST['action'])) {
        if ($_REQUEST['action'] == 'edit_campaign') {
            include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_edit1.php';
        } elseif ($_REQUEST['action'] == 'edit_attachment') {
            include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_edit2.php';
        } elseif ($_REQUEST['action'] == 'edit_response') {
            include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_edit3.php';
        } elseif ($_REQUEST['action'] == 'add_campaign') {
            include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_add1.php';
        } elseif ($_REQUEST['action'] == 'add_attachment') {
            include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_add2.php';
        } elseif ($_REQUEST['action'] == 'add_response') {
            include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_add3.php';
        } else {
            echo '<h1>Unknown action!</h1>';
        }
    } else {
        include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_table.php';
    }
    ?>
</div>
