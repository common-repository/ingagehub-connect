<?php
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}
?>
<div id="ih_admin_campaigns_table_filter">
    <form id="ih_admin_campaigns_table_filter_form" name="ih_admin_campaigns_table_filter_form"
          method="post"
          action="<?php echo $_SERVER['REQUEST_URI'] ?>"
    >
        <?php

        $filter = null;
        if (!isset($_REQUEST['ih_admin_campaign_clear_search'])) {
            $filter = isset($_REQUEST['ih_admin_campaign_name_search']) && strlen($_REQUEST['ih_admin_campaign_name_search']) > 0 ? $_REQUEST['ih_admin_campaign_name_search'] : $filter;
            $filter = is_null($filter) && isset($_SESSION['ih_connect_campaign_table_filter']) && strlen($_SESSION['ih_connect_campaign_table_filter']) > 0 ? $_SESSION['ih_connect_campaign_table_filter'] : $filter;
        }

        if (is_null($filter)) {
            unset($_REQUEST['ih_admin_campaign_name_search']);
            unset($_SESSION['ih_connect_campaign_table_filter']);

            ?>
            <input type="text"
                   name="ih_admin_campaign_name_search" id="ih_admin_campaign_name_search"
                   placeholder="Name to Find"/>
            <a name="ih_admin_campaign_search" id="ih_admin_campaign_search"><img src="<?php
                echo com_ingagehub_plugin_dir_url() . 'images/search_24x24.png';
                ?>" alt="Search" /></a>
            <?php

        } else {
            $_REQUEST['ih_admin_campaign_name_search'] = $filter;
            $_SESSION['ih_connect_campaign_table_filter'] = $filter;

            ?>
            <h2>Showing campaigns matching <em><?php echo $filter ?></em></h2>
            <input type="button"
                   name="ih_admin_campaign_clear_search" id="ih_admin_campaign_clear_search"
                   value="Clear" class="submit small"/>
            <?php
        }
        ?>
        <a name="ih_admin_campaign_refresh" id="ih_admin_campaign_refresh"><img src="<?php
            echo com_ingagehub_plugin_dir_url() . 'images/refresh_24x24.png';
            ?>" alt="Refresh" /></a>
    </form>
</div>
