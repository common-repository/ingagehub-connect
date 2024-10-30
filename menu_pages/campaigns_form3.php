<?php
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}
?>
<?php
if ($campaign->check_needs_save()) {
    echo '<h3 class="status status_warning">' . INgageHubConnectMessages::stdMessageCampaignNeedsSave() . '</h3>';
}
?>

<h2><?php echo $response->id == 0 ? 'Add' : 'Edit'; ?> Response</h2>

<form id="ih_admin_response_form" name="ih_admin_response_form"
      class="ih_admin_data_form"
      method="post"
      action="<?php echo add_query_arg(array(
          'action' => 'save_response',
          'campaign_id' => $campaign->id,
          'attachment_id' => $attachment->id,
          'response_id' => $response->id
      )) ?>"
>

    <label for="ih_admin_response_label">Label</label>
    <input type="text" name="ih_admin_response_label" id="ih_admin_response_label" value="<?php echo $response->label ?>" /><br/>
    <br/>

    <input type="button" class="submit"
           name="ih_admin_save_response" id="ih_admin_save_response"
           value="Save"
    />
    <input type="button"
           name="ih_admin_cancel_response" id="ih_admin_cancel_response"
           value="<< Back" class="warning"
           data-href="<?php echo add_query_arg(array(
               'action' => 'edit_attachment',
               'campaign_id' => $campaign->id,
               'attachment_id' => $attachment->id,
               'response_id' => false
           )) ?>"
    />
</form>
