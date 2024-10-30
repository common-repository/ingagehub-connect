<?php
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}
?>
<?php
if ($campaign->check_needs_save()) {
    echo '<h3 class="status status_warning">' . INgageHubConnectMessages::stdMessageCampaignNeedsSaveMain() . '</h3>';
}
?>

<h2><?php echo $campaign->id == 0 ? 'Add' : 'Edit'; ?> Campaign</h2>

<form id="ih_admin_campaign_form" name="ih_admin_campaign_form"
      class="ih_admin_data_form"
      method="post"
      action="<?php echo add_query_arg(array(
          'action' => 'save_campaign',
          'campaign_id' => $campaign->id,
          'attachment_id' => false,
          'response_id' => false
      )) ?>"
>
    <label for="ih_admin_campaign_name">Name</label>
    <input type="text" name="ih_admin_campaign_name" id="ih_admin_campaign_name" value="<?php echo $campaign->name; ?>" class="xlarge" /><br/>

    <?php if ($campaign->id != 0)  { ?>
    <h3>Questions</h3>

    <table>
        <thead>
        <tr>
            <th>Question</th>
            <th>Format</th>
            <th>Action</th>
        </tr>
        <tr>
            <th colspan="3">
                <a href="<?php echo add_query_arg(array(
                    'action' => 'add_attachment',
                    'campaign_id' => $campaign->id,
                    'attachment_id' => false,
                    'response_id' => false
                )) ?>"
                ><img src="<?php echo com_ingagehub_plugin_dir_url() . 'images/add_24x24.png'; ?>" alt="Add" /><span>Add Question</span></a>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($campaign->attachments as $attachment) {
            if ($attachment->attachment_type != 'adhoc') continue;
            ?>
            <tr>
                <td><?php echo INgageHubConnectMessages::truncate($attachment->question_text_stripped(), 100) ?></td>
                <td><?php echo $attachment->response_format_description() ?></td>
                <td>
                    <a href="<?php echo add_query_arg(array(
                        'action' => 'edit_attachment',
                        'campaign_id' => $campaign->id,
                        'attachment_id' => $attachment->id,
                        'response_id' => false
                        )) ?>"
                    ><img src="<?php echo com_ingagehub_plugin_dir_url() . 'images/edit_24x24.png'; ?>" alt="Edit" /></a>
                    <a href="<?php echo add_query_arg(array(
                        'action' => 'delete_attachment',
                        'campaign_id' => $campaign->id,
                        'attachment_id' => $attachment->id,
                        'response_id' => false
                        )) ?>"
                       data-confirm="<?php echo INgageHubConnectMessages::stdConfirmDeleteAttachment() ?>"
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
           name="ih_admin_save_campaign" id="ih_admin_save_campaign"
           value="Save"
    />
    <input type="button" class="submit"
           name="ih_admin_save_and_publish_campaign" id="ih_admin_save_and_publish_campaign"
           value="Save and Publish"
    />
    <?php
    if ($campaign->check_needs_save()) {
        ?>
        <input type="button"
               name="ih_admin_cancel_campaign" id="ih_admin_cancel_campaign"
               value="Cancel" class="warning"
               data-href="<?php echo add_query_arg(array(
                   'action' => 'cancel_campaign',
                   'campaign_id' => $campaign->id,
                   'attachment_id' => false,
                   'response_id' => false
               )) ?>"
               data-confirm="<?php echo INgageHubConnectMessages::stdConfirmCancelCampaign() ?>"
        />
        <?php
    } else {
        ?>
        <input type="button"
               name="ih_admin_cancel_campaign" id="ih_admin_cancel_campaign"
               value="<< Back" class="warning"
               data-href="<?php echo add_query_arg(array(
                   'action' => false,
                   'campaign_id' => false,
                   'attachment_id' => false,
                   'response_id' => false
               )) ?>"
        />
        <?php
    }
    ?>
</form>
