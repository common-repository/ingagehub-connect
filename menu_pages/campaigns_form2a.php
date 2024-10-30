<?php
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

$base_question_data = INgageHubConnectBaseQuestion::load_all();
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

    <h3>Select a Basic Question Template Below</h3>

    <input type="hidden" name="ih_admin_stage" id="ih_admin_stage" value="add_attachment"/>

    <input type="hidden" name="ih_admin_base_question_id" id="ih_admin_base_question_id" value=""/>
    <ul class="ih_admin_radio_list" data-target="ih_admin_base_question_id">
        <?php
        foreach ($base_question_data['base_questions'] as $base_question) {
            if ($base_question->question_type !== 0) continue;
            ?>
            <li data-value="<?php echo $base_question->id ?>">
                <strong><?php echo $base_question->question_text ?></strong>
                <br/>
                <span class="ih_admin_base_question_details">(format: <?php echo $base_question->response_format_description() ?>)</span>
            </li>
            <?php
        }
        ?>
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
