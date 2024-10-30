<?php
class INgageHubConnectMessages
{
    public static function stdMessageCampaignNotFound() {
        INgageHubConnectMessages::setErrorMessage('The requested campaign could not be found.');
    }

    public static function stdMessageCampaignSaved() {
        INgageHubConnectMessages::setSuccessMessage('The campaign has been saved successfully.');
    }

    public static function stdMessageCampaignNotSaved() {
        INgageHubConnectMessages::setErrorMessage('The campaign could not be saved.');
    }

    public static function stdMessageCampaignNeedsSave() {
        return 'There are unsaved changes to this campaign.  Return to the main campaign edit page to save or cancel them.';
    }

    public static function stdMessageCampaignNeedsSaveMain() {
        return 'There are unsaved changes to this campaign.  Be sure to Save, Save and Publish, or Cancel them.';
    }

    public static function stdMessageCampaignDeleted() {
        INgageHubConnectMessages::setSuccessMessage('The campaign has been deleted successfully.');
    }

    public static function stdMessageAttachmentNotFound() {
        INgageHubConnectMessages::setErrorMessage('The requested question could not be found in the campaign.');
    }

    public static function stdMessageAttachmentSaved() {
        INgageHubConnectMessages::setSuccessMessage('The question has been saved successfully.');
    }

    public static function stdMessageAttachmentDeleted() {
        INgageHubConnectMessages::setSuccessMessage('The question has been deleted successfully.');
    }

    public static function stdMessageResponseNotFound() {
        INgageHubConnectMessages::setErrorMessage('The requested response could not be found for this question.');
    }

    public static function stdMessageResponseSaved() {
        INgageHubConnectMessages::setSuccessMessage('The response has been saved successfully.');
    }

    public static function stdMessageResponseDeleted() {
        INgageHubConnectMessages::setSuccessMessage('The response has been deleted successfully.');
    }

    public static function stdMessageResponseMoved() {
        INgageHubConnectMessages::setSuccessMessage('The response order has been changed successfully.');
    }

    public static function stdMessageUnsavedChanges() {
        INgageHubConnectMessages::setErrorMessage('You have unsaved changes to this campaign.  Please review this campaign and save it or cancel your changes before continuing.');
    }

    public static function stdMessageCampaignReloaded() {
        INgageHubConnectMessages::setSuccessMessage('The campaign has been reloaded and all changes have been discarded.');
    }

    public static function stdMessageConnectionTestSucceeded() {
        INgageHubConnectMessages::setSuccessMessage('The connection test succeeded!  The connection options have been updated.');
    }

    public static function stdMessageConnectionTestFailed() {
        INgageHubConnectMessages::setErrorMessage('The connection test failed.  The connection options could not be verified.  Please check the site URL, user, and password and try again.');
    }

    public static function stdMessageConnectionFailed() {
        INgageHubConnectMessages::setErrorMessage('A connection could not be made to your INgageHub site.');
    }

    public static function stdConfirmCancelCampaign() {
        return 'Are you sure you want to discard your changes to this campaign?';
    }

    public static function stdConfirmDeleteCampaign() {
        return 'Are you sure you want to delete this campaign?';
    }

    public static function stdConfirmDeleteAttachment() {
        return 'Are you sure you want to delete this question?';
    }

    public static function stdConfirmDeleteResponse() {
        return 'Are you sure you want to delete this response?';
    }

    public static function setSuccessMessage($message) {
        $_SESSION['ih_connect_message'] = $message;
        $_SESSION['ih_connect_message_class'] = 'status_ok';
    }

    public static function setErrorMessage($message) {
        $_SESSION['ih_connect_message'] = $message;
        $_SESSION['ih_connect_message_class'] = 'status_error';
    }

    public static function resetMessage() {
        unset($_SESSION['ih_connect_message']);
        unset($_SESSION['ih_connect_message_class']);
    }

    public static function message() {
        if (isset($_SESSION['ih_connect_message'])) {
            return $_SESSION['ih_connect_message'];
        } else {
            return '';
        }
    }

    public static function messageClass() {
        if (isset($_SESSION['ih_connect_message_class'])) {
            return $_SESSION['ih_connect_message_class'];
        } else {
            return 'status_ok';
        }
    }

    public static function haveMessage() {
        return isset($_SESSION['ih_connect_message']);
    }

    public static function truncate($string, $width) {
        if ($width < 1) {
            return '';
        } elseif ($width < 4) {
            return substr($string, 0, $width);
        } elseif (strlen($string) > $width) {
            return substr($string, 0, $width - 3) . '...';
        } else {
            return $string;
        }
    }
}
?>
