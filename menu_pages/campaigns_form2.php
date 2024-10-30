<?php
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}
?>

<h2>Add Question</h2>

<form id="ih_admin_attachment_form" name="ih_admin_attachment_form"
      class="ih_admin_data_form"
      method="post"
      action="<?php echo add_query_arg(array(
          'action' => 'add_attachment',
          'campaign_id' => $campaign->id,
          'attachment_id' => false,
          'response_id' => false
      )) ?>"
>

    <h3>How would you like to start?</h3>

    <input type="hidden" name="ih_admin_stage" id="ih_admin_stage" value="" />
    <ul class="ih_admin_radio_list" data-target="ih_admin_stage">
        <li data-value="add_attachment">Add a Completely Blank Question</li>
        <li data-value="select_template">Add a Question from a Basic Template</li>
        <li data-value="find_qi_questions">Find a Question Using Question Intelligence</li>
    </ul>

    <br/>

    <input type="button"
           name="ih_admin_cancel_attachment" id="ih_admin_cancel_attachment"
           value="<< Back" class="warning"
           data-href="<?php echo add_query_arg(array(
               'action' => 'edit_campaign',
               'campaign_id' => $campaign->id,
               'attachment_id' => false,
               'response_id' => false
           )) ?>"
    />
</form>
