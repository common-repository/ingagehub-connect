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

    <h3>What type of question are you looking for?</h3>

    <input type="hidden" name="ih_admin_stage" id="ih_admin_stage" value="select_qi_question"/>

    <label for="ih_admin_qi_industry">Industry</label>
    <select size="1" name="ih_admin_qi_industry" id="ih_admin_qi_industry">
        <?php
        foreach ($base_question_data['industries'] as $industry) {
            ?>
            <option value="<?php echo $industry ?>"><?php echo $industry ?></option>
            <?php
        }
        ?>
    </select>

    <label for="ih_admin_qi_audience">Audience</label>
    <select size="1" name="ih_admin_qi_audience" id="ih_admin_qi_audience">
        <?php
        foreach ($base_question_data['audiences'] as $audience) {
            ?>
            <option value="<?php echo $audience ?>"><?php echo $audience ?></option>
            <?php
        }
        ?>
    </select>

    <label for="ih_admin_qi_objective">Objective</label>
    <select size="1" name="ih_admin_qi_objective" id="ih_admin_qi_objective">
        <?php
        foreach ($base_question_data['objectives'] as $objective) {
            ?>
            <option value="<?php echo $objective ?>"><?php echo $objective ?></option>
            <?php
        }
        ?>
    </select>

    <br/>

    <input type="button" class="submit"
           name="ih_admin_qi_search" id="ih_admin_qi_search"
           value="Search"
    />
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
