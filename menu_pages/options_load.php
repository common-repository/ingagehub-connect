<?php
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

if (!is_user_logged_in()) {
    wp_redirect(network_admin_url());
    exit;
}

wp_enqueue_style('ingagehub_admin_css', com_ingagehub_plugin_dir_url() . 'css/admin_style.min.css');
wp_enqueue_script('ingagehub_admin_js', com_ingagehub_plugin_dir_url() . 'js/admin.min.js');
?>
