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

    <h3>The following questions matched your criteria.  Please select one to add.</h3>

    <input type="hidden" name="ih_admin_stage" id="ih_admin_stage" value="add_attachment"/>

    <input type="hidden" name="ih_admin_base_question_id" id="ih_admin_base_question_id" value=""/>
    <ul class="ih_admin_radio_list" data-target="ih_admin_base_question_id">
        <?php
        $one_found = false;

        foreach ($base_question_data['base_questions'] as $base_question) {
            if ($base_question->question_type !== 1) continue;
            if (!($_REQUEST['ih_admin_qi_industry'] === '(all)' || $base_question->industry === '*') && $base_question->industry !== $_REQUEST['ih_admin_qi_industry']) continue;
            if (!($_REQUEST['ih_admin_qi_audience'] === '(all)' || $base_question->audience === '*') && $base_question->audience !== $_REQUEST['ih_admin_qi_audience']) continue;
            if (!($_REQUEST['ih_admin_qi_objective'] === '(all)' || $base_question->objective === '*') && $base_question->objective !== $_REQUEST['ih_admin_qi_objective']) continue;

            $one_found = true;
            ?>
            <li data-value="<?php echo $base_question->id ?>">
                <strong><?php echo $base_question->question_text ?></strong>
                <br/>
                <span class="ih_admin_base_question_details"><?php
                    echo $base_question->industry . '/' .
                        $base_question->audience . '/' .
                        $base_question->objective .
                        ' (format: ' . $base_question->response_format_description() . ')' ?></span>
            </li>
            <?php
        }
        ?>
    </ul>

    <?php
    if ($one_found === false) {
        echo '<h3 class="status_warning">No questions matched your criteria.</h3>';
    }
    ?>

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
