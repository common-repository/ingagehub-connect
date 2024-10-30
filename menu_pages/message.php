<?php
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

if (INgageHubConnectMessages::haveMessage()) {
    echo '<h3 class="status ' . INgageHubConnectMessages::messageClass() . '">' . INgageHubConnectMessages::message() . '</h3>';

    INgageHubConnectMessages::resetMessage();
}
?>
