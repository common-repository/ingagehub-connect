<?php
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

$current_uid = get_current_user_id();

$status = '';

if (isset($_POST['ih_site_url'])) {
    $ih_site_url = $_POST['ih_site_url'];
    $ih_user_name = $_POST['ih_user_name'];

    if (strlen($_POST['ih_password']) == 0 && $ih_user_name == get_user_option('ih_user_name') && $ih_site_url == get_user_option('ih_site_url')) {
        $ih_password = get_user_option('ih_password');
    } else {
        $ih_password = $_POST['ih_password'];
    }

    $auth = new IngageHubConnectAuth();
    $auth->logout();
    $success = $auth->set_site_url($ih_site_url)->set_user_name($ih_user_name)->set_password($ih_password)->login();

    if ($success === true) {
        update_user_option($current_uid, 'ih_site_url', $ih_site_url);
        update_user_option($current_uid, 'ih_user_name', $ih_user_name);
        update_user_option($current_uid, 'ih_password', $ih_password);

        INgageHubConnectMessages::stdMessageConnectionTestSucceeded();

    } else {
        com_ingagehub_clear_cached_objects();

        INgageHubConnectMessages::stdMessageConnectionTestFailed();

        $status = 'ERROR';
    }
}

$ih_site_url = get_user_option('ih_site_url');
$ih_user_name = get_user_option('ih_user_name');

?>
<div id="ih_admin_options">
    <img class="ih_admin_logo" src="<?php echo com_ingagehub_plugin_dir_url() . 'images/banner-541x175.png' ?>"/>

    <?php include com_ingagehub_plugin_dir_path() . 'menu_pages/message.php' ?>

    <h1>Connection Options</h1>

    <?php
    if ($status === 'ERROR' || empty($ih_site_url)) {
        echo '<h4>If you do not have an INgageHub site, <a href="http://www.ingagehub.com/features/wordpress-integration" target="_new">click here</a> to learn more about becoming an INgageHub user and the benefits to your business.</h4>';
    }
    ?>

    <form id="ih_admin_options_form" name="ih_admin_options_form"
        method="post"
        action="<?php echo $_SERVER["REQUEST_URI"]; ?>"
    >
        <label for="ih_site_url">Site URL</label>
        <input id="ih_site_url" name="ih_site_url" type="text" value="<?php echo $ih_site_url ?>" class="large"/><br/>
        <small>(include the http:// or https://)</small><br/>
        <br/>

        <label for="ih_user_name">User Name</label>
        <input id="ih_user_name" name="ih_user_name" type="text" value="<?php echo $ih_user_name ?>"/><br/>

        <label for="ih_password">Password</label>
        <input id="ih_password" name="ih_password" type="password"/><br/>
        <small>(leave password blank to keep the existing password)</small><br/>
        <br/>

        <input type="button" class="submit"
               value="Save Settings and Test"
        /><br/>
        <br/>
    </form>
</div>
