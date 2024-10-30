<?php
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}
?>
<div id="ih_admin_campaigns_table_sort">
    <?php
    echo '<strong>Sort Options</strong>';

    $sort = null;
    $sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : $sort;
    $sort = is_null($sort) && isset($_SESSION['ih_connect_campaign_table_sort']) ? $_SESSION['ih_connect_campaign_table_sort'] : $sort;
    $sort = is_null($sort) ? 'name' : $sort;

    if ($sort == 'name') {
        echo '<strong>Name (A-Z)</strong>';
    } else {
        echo '<a href="' . add_query_arg(array('sort' => 'name')) . '">Name (A-Z)</a>';
    }

    if ($sort == 'name_desc') {
        echo '<strong>Name (Z-A)</strong>';
    } else {
        echo '<a href="' . add_query_arg(array('sort' => 'name_desc')) . '">Name (Z-A)</a>';
    }

    if ($sort == 'updated_at_desc') {
        echo '<strong>Last Updated (Most Recent First)</strong>';
    } else {
        echo '<a href="' . add_query_arg(array('sort' => 'updated_at_desc')) . '">Last Updated (Most Recent First)</a>';
    }

    if ($sort == 'updated_at') {
        echo '<strong>Last Updated (Oldest First)</strong>';
    } else {
        echo '<a href="' . add_query_arg(array('sort' => 'updated_at')) . '">Last Updated (Oldest First)</a>';
    }

    $_SESSION['ih_connect_campaign_table_sort'] = $sort;
    ?>
</div>
