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

<h2><?php echo $attachment->id == 0 ? 'Add' : 'Edit'; ?> Question</h2>

<form id="ih_admin_attachment_form" name="ih_admin_attachment_form"
      class="ih_admin_data_form"
      method="post"
      action="<?php echo add_query_arg(array(
          'action' => 'save_attachment',
          'campaign_id' => $campaign->id,
          'attachment_id' => $attachment->id,
          'response_id' => false
      )) ?>"
>

    <h3>Question Text</h3>
    <div id="ih_admin_question_text_div">
        <?php wp_editor(
            $attachment->contents->question_text,
            'ih_admin_question_text',
            array(
                'textarea_name' => 'ih_admin_question_text',
                'textarea_rows' => 6,
                'teeny' => true
            )
        ) ?>
    </div>
    <br/>

    <label for="ih_admin_question_layout">Question Layout</label>
    <select size="1" name="ih_admin_question_layout" id="ih_admin_question_layout">
        <?php foreach (INgageHubConnectQuestionLayout::load_all() as $question_layout) { ?>
            <option <?php echo (int)$attachment->contents->question_layout == $question_layout->id ? 'selected="selected"' : '' ?> value="<?php echo $question_layout->id ?>"><?php echo $question_layout->label ?></option>
        <?php } ?>
    </select>

    <label for="ih_admin_response_format">Response Format</label>
    <select size="1" name="ih_admin_response_format" id="ih_admin_response_format">
        <option <?php echo $attachment->contents->response_format == 0 ? 'selected="selected"' : '' ?> value="0">Select One</option>
        <option <?php echo $attachment->contents->response_format == 1 ? 'selected="selected"' : '' ?> value="1">Select Multiple</option>
        <option <?php echo $attachment->contents->response_format == 2 ? 'selected="selected"' : '' ?> value="2">Ranking</option>
        <option <?php echo $attachment->contents->response_format == 3 ? 'selected="selected"' : '' ?> value="3">Free Text</option>
    </select>

    <label for="ih_admin_has_other_box">Has Other Box</label>
    <input type="checkbox" name="ih_admin_has_other_box" id="ih_admin_has_other_box" <?php echo $attachment->contents->has_other_box == 0 ? '' : 'checked="checked"' ?> value="1" />

    <?php if ($attachment->id != 0)  { ?>
    <h3>Responses</h3>

    <table>
        <thead>
        <tr>
            <th>Label</th>
            <th>Value</th>
            <th>Action</th>
        </tr>
        <tr>
            <th colspan="3">
                <a href="<?php echo add_query_arg(array(
                    'action' => 'add_response',
                    'campaign_id' => $campaign->id,
                    'attachment_id' => $attachment->id,
                    'response_id' => false
                )) ?>"
                ><img src="<?php echo com_ingagehub_plugin_dir_url() . 'images/add_24x24.png'; ?>" alt="Add" /><span>Add Response</span></a>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($attachment->contents->responses as $response) {
            ?>
            <tr>
                <td><?php echo INgageHubConnectMessages::truncate($response->label, 100) ?></td>
                <td><?php echo $response->value ?></td>
                <td>
                    <a href="<?php echo add_query_arg(array(
                        'action' => 'move_response_up',
                        'campaign_id' => $campaign->id,
                        'attachment_id' => $attachment->id,
                        'response_id' => $response->id
                    )) ?>"
                    ><img src="<?php echo com_ingagehub_plugin_dir_url() . 'images/arrow_up_24x24.png'; ?>" alt="Delete" /></a>
                    <a href="<?php echo add_query_arg(array(
                        'action' => 'move_response_down',
                        'campaign_id' => $campaign->id,
                        'attachment_id' => $attachment->id,
                        'response_id' => $response->id
                    )) ?>"
                    ><img src="<?php echo com_ingagehub_plugin_dir_url() . 'images/arrow_down_24x24.png'; ?>" alt="Delete" /></a>
                    <a href="<?php echo add_query_arg(array(
                        'action' => 'edit_response',
                        'campaign_id' => $campaign->id,
                        'attachment_id' => $attachment->id,
                        'response_id' => $response->id
                        )) ?>"
                    ><img src="<?php echo com_ingagehub_plugin_dir_url() . 'images/edit_24x24.png'; ?>" alt="Edit" /></a>
                    <a href="<?php echo add_query_arg(array(
                        'action' => 'delete_response',
                        'campaign_id' => $campaign->id,
                        'attachment_id' => $attachment->id,
                        'response_id' => $response->id
                    )) ?>"
                       data-confirm="<?php echo INgageHubConnectMessages::stdConfirmDeleteResponse() ?>"
                    ><img src="<?php echo com_ingagehub_plugin_dir_url() . 'images/delete_24x24.png'; ?>" alt="Delete" /></a>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
        <?php
    } else {
        echo '<br/><br/>';
    }
    ?>

    <input type="button" class="submit"
           name="ih_admin_save_attachment" id="ih_admin_save_attachment"
           value="Save"
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
