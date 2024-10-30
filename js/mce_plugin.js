//<script type="text/javascript">
    (function($) {
        var campaigns = null;
        var campaign = {};

        var helpText = {
            submit: 'Place the submit form shortcode where you would like to position the user identification entry fields (email, etc.) and "Submit Your Response" button.',
            default_token: 'A default token will ensure that the engagement content is displayed regardless of the means by which the visitor arrives at this article.  If not present, only visitors arriving through a link shared via INgageHub (through email, social, etc.) will see the engagement content.  Visitors arriving by other means will see the article without engagement content.  If present, visitors arriving through an INgageHub link will still use the token specific to them.  The default token is only used if visitors arrive through other means.',
            next_page: 'You may specify an optional next page to which visitors will be redirected upon submitting responses.  If this shortcode is omitted, visitors will receive a confirmation message of their submission but remain on the same page.  If it is present, they will still receive confirmation but will also be redirected to the page you specify.',
            campaign_shortcode: 'The campaign shortcode does not appear first.  The campaign shortcode [ingagehub_campaign] must appear before any other INgageHub shortcodes [ingagehub_...].  INgageHub shortcodes appearing before the campaign shortcode will be ignored.  Please edit the content to ensure the campaign shortcode appears before any other INgageHub shortcodes.'
        };

        var I18n = {
            attachmentsDontBelongToCampaignTitle: 'Mismatched Questions',
            attachmentsDontBelongToCampaign: 'One or more questions referenced in shortcodes are for questions that could not be found in the connected campaign.',
            buttonAdd: 'Add',
            buttonAddDefaultToken: 'Add Default Token',
            buttonAddNextPageShortcode: 'Add Next Page',
            buttonAddSubmissionShortcode: 'Add Submission Form',
            buttonAttachments: 'Manage Questions',
            buttonCampaigns: 'Manage Campaigns',
            buttonCancel: 'Cancel',
            buttonEditorToolbar: 'INgageHub Connect',
            buttonInsertAttachment: 'Insert Question',
            buttonOK: 'OK',
            buttonOptions: 'Change Options',
            buttonRemoveDefaultToken: 'Remove Default Token',
            buttonSelect: 'Select',
            campaignListEmpty: 'No usable campaigns found.  Either no campaigns exist or none were found that have at least one question defined.',
            campaignListEmptyTitle: 'No Campaigns Found',
            campaignListRequestFailed: 'The request for a list of campaigns from your INgageHub site failed.  Please check your settings, INgageHub site, or try again in a moment.',
            campaignListRequestFailedTitle: 'Could Not Load the Campaign List',
            campaignSelectDialogConfirmation: 'The campaign has been selected.  As you continue to edit your content, you can use the INgageHub tool to insert specific campaign questions as desirable.',
            campaignSelectDialogIncludeDefaulTokenLabel: 'Include Default Token?*IMAGE_HELP*',
            campaignSelectDialogLabel: 'Your content is not connected to an INgageHub campaign.  Please select a campaign to which this content will be connected.',
            campaignSelectDialogNothingSelected: 'You must select a campaign.',
            campaignSelectDialogTitle: 'Select the Campaign',
            campaignUnknownTitle: 'Unknown Campaign',
            campaignUnknown: 'The campaign shortcode is present but is referencing a campaign that does not exist.',
            confirmAbandonChanges: 'The content on this page has changed.  Are you sure you want to leave this page?',
            customizeDialogAttachmentInserted: 'The INgageHub campaign question has been inserted.',
            customizeDialogAttachmentsAllInserted: 'All questions have already been inserted.',
            customizeDialogAttachmentsLabel: 'The following questions available to insert:',
            customizeDialogAttachmentsNothingSelected: 'You must select an question.',
            customizeDialogCampaignNameLabel: 'Connected Campaign: ',
            customizeDialogDefaultTokenNotPresent: 'The campaign shortcode does not have a default token specified.*IMAGE_HELP*',
            customizeDialogDefaultTokenPresent: 'A default token is specified in the campaign shortcode.*IMAGE_HELP*',
            customizeDialogDefaultTokenRemoved: 'The default token has been removed from the campaign shortcode.',
            customizeDialogNextPageShortcodeNotPresent: 'You have not yet added the shortcode for the next page.*IMAGE_HELP*',
            customizeDialogNextPageShortcodePresent: 'The shortcode for the next page has already been added.*IMAGE_HELP*',
            customizeDialogSubmissionShortcodeNotPresent: 'You have not yet added the shortcode for the submission form.*IMAGE_HELP*',
            customizeDialogSubmissionShortcodePresent: 'The shortcode for the submission form has already been added.*IMAGE_HELP*',
            customizeDialogTitle: 'Customize Engagement',
            defaultTokenRequestFailed: 'The request for a default token from your INgageHub site failed.  Please check your settings, INgageHub site, or try again in a moment.',
            defaultTokenRequestFailedTitle: 'Default Token Request Failed',
            nextPageShortcodeDialogDefaultText: '/',
            nextPageShortcodeDialogLabel: 'Please provide the URL to which visitors will be redirected upon submitting responses.',
            nextPageShortcodeDialogTitle: 'Add Next Page Shortcode',
            pleaseWaitDialogDefaultTokenRequestMessage: 'Please wait a moment while we connect to your INgageHub site and request a default token...',
            pleaseWaitDialogLoadingCampaignsMessage: 'Please wait a moment while we connect to your INgageHub site and retrieve the list of your campaigns...',
            pleaseWaitDialogTitle: 'Connecting to INgageHub',
            submissionShortcodeDialogDefaultText: 'Submit Your Responses',
            submissionShortcodeDialogLabel: 'Please provide the text you would like on the submit button.',
            submissionShortcodeDialogTitle: 'Add Submission Form Shortcode'
        };

        var buttonDialogHandler = function(ed) {

            ////////////////////////////////////////
            ////////////////////////////////////////
            var getCampaign = function() {
                return wp.shortcode.next('ingagehub_campaign', ed.getContent());
            };

            var openPleaseWaitDialogWindow = function(message) {
                return ed.windowManager.open({
                    title: I18n.pleaseWaitDialogTitle,
                    body: [
                        { type: 'label', text: message }
                    ],
                    buttons: []
                });
            };

            ////////////////////////////////////////
            ////////////////////////////////////////
            var redirectIfClean = function(url, windowToClose) {
                if (!ed.isDirty() || (ed.isDirty() && confirm(I18n.confirmAbandonChanges))) {
                    ed.windowManager.close(windowToClose);
                    ed.startContent = ed.save();

                    //var url2 = url + (url.indexOf('?') >= 0 ? '&' : '?');
                    //url2 += 'return_url=' + encodeURIComponent(location.href);
                    //
                    //location.href = url2;

                    location.href = url;
                }
            };

            ////////////////////////////////////////
            ////////////////////////////////////////
            var navigateDialog = function(title, message, button, url) {
                var navigateDialogWindow = ed.windowManager.open({
                    title: title,
                    body: [
                        {
                            type: 'label',
                            text: message
                        }
                    ],
                    buttons: [
                        {
                            text: button,
                            onClick: function () {
                                redirectIfClean(url, navigateDialogWindow);
                            }
                        },
                        {
                            text: I18n.buttonCancel,
                            onClick: function () {
                                ed.windowManager.close(navigateDialogWindow);
                            }
                        }
                    ]
                });
            };
            ////////////////////////////////////////
            ////////////////////////////////////////
            var loadCampaigns = function() {

                var pleaseWaitDialogWindow = openPleaseWaitDialogWindow(I18n.pleaseWaitDialogLoadingCampaignsMessage);

                var loadCampaignsError = function () {
                    ed.windowManager.close(pleaseWaitDialogWindow);

                    navigateDialog(I18n.campaignListRequestFailedTitle, I18n.campaignListRequestFailed, I18n.buttonOptions, INgageHub.optionsUrl);
                };

                $.ajax(INgageHub.ajaxUrl, {
                    data: {
                        action: 'com_ingagehub_clist'
                    },
                    success: function(data) {
                        if (data.status === 'OK') {
                            campaigns = data.campaigns;
                            ed.windowManager.close(pleaseWaitDialogWindow);
                            startDialog();
                        } else {
                            loadCampaignsError();
                        }
                    },
                    error: loadCampaignsError
                });
            };

            ////////////////////////////////////////
            ////////////////////////////////////////
            var setDefaultToken = function() {
                var pleaseWaitDialogWindow = openPleaseWaitDialogWindow(I18n.pleaseWaitDialogDefaultTokenRequestMessage);

                var setDefaultTokenError = function(m) {
                    ed.windowManager.close(pleaseWaitDialogWindow);

                    navigateDialog(I18n.defaultTokenRequestFailedTitle, I18n.defaultTokenRequestFailed, I18n.buttonOptions, INgageHub.optionsUrl);
                };

                var campaign_shortcode = getCampaign();
                if (typeof(campaign_shortcode) !== 'undefined') {
                    $.ajax(INgageHub.ajaxUrl, {
                        data: {
                            action: 'com_ingagehub_ctoken',
                            id: campaign_shortcode.shortcode.attrs.named.id
                        },
                        success: function(data) {
                            if (data.status === 'OK') {
                                ed.windowManager.close(pleaseWaitDialogWindow);
                                campaign_shortcode = getCampaign();
                                if (typeof(campaign_shortcode) !== 'undefined') {
                                    campaign_shortcode.shortcode.set('default_token', data.sharing_token);
                                    ed.setContent(wp.shortcode.replace('ingagehub_campaign', ed.getContent(), function() {
                                        return campaign_shortcode.shortcode.string();
                                    }));
                                }
                            } else {
                                setDefaultTokenError(data);
                            }
                        },
                        error: setDefaultTokenError
                    });
                }
            };

            ////////////////////////////////////////
            ////////////////////////////////////////
            var removeDefaultToken = function() {
                var campaign_shortcode = getCampaign();
                if (typeof(campaign_shortcode) !== 'undefined') {
                    delete campaign_shortcode.shortcode.attrs.named.default_token;
                    ed.setContent(wp.shortcode.replace('ingagehub_campaign', ed.getContent(), function() {
                        return campaign_shortcode.shortcode.string();
                    }));
                }
            };

            ////////////////////////////////////////
            ////////////////////////////////////////
            var onPostRenderDialog = function() {
                var help = $('.ih-connect-help');
                help.unbind('click');
                help.bind('click', function(e) {
                    e.preventDefault();
                    var helpId = $(e.target).attr('data-help-id');
                    ed.windowManager.alert(helpText[helpId]);
                    return false;
                });
            };

            ////////////////////////////////////////
            ////////////////////////////////////////
            var addHelpIconFor = function(element, helpId) {
                element.innerHTML = element.innerHTML.replace('*IMAGE_HELP*', function() {
                    return '&nbsp;<img class="ih-connect-help" data-help-id="' + helpId + '" src="' + INgageHub.pluginUrl + 'images/help_16x16.png' + '" />';
                });
            };

            ////////////////////////////////////////
            ////////////////////////////////////////
            var replaceImageFor = function(element) {
                element.innerHTML = element.innerHTML.replace('*IMAGE_CHECK*', function() {
                    return '&nbsp;<img valign="middle" src="' + INgageHub.pluginUrl + 'images/check_32x32.png' + '" />';
                }).replace('*IMAGE_ALERT*', function() {
                    return '&nbsp;<img valign="middle" src="' + INgageHub.pluginUrl + 'images/alert_32x32.png' + '" />';
                }).replace('*IMAGE_INFO*', function() {
                    return '&nbsp;<img valign="middle" src="' + INgageHub.pluginUrl + 'images/info_32x32.png' + '" />';
                });
            };

            ////////////////////////////////////////
            ////////////////////////////////////////
            var campaignsDialog = function() {
                var usableCampaigns = [];
                $.each(campaigns, function(k, v) {
                    if (v.campaign_attachments.length > 0) {
                        usableCampaigns.push({ value: v.id, text: v.name });
                    }
                });

                if (usableCampaigns.length === 0) {
                    navigateDialog(I18n.campaignListEmptyTitle, I18n.campaignListEmpty, I18n.buttonCampaigns, INgageHub.campaignsUrl);
                    return;
                }

                usableCampaigns.unshift({ text: '', value: 0 });

                // ok...select a campaign first
                var campaignDialogWindow = ed.windowManager.open({
                    title: I18n.campaignSelectDialogTitle,
                    onPostRender: onPostRenderDialog,
                    body: [
                        { type: 'label', text: I18n.campaignSelectDialogLabel },
                        { name: 'selcamp', type: 'listbox', values: usableCampaigns, value: 0 },
                        {
                            name: 'deftok',
                            type: 'checkbox',
                            text: I18n.campaignSelectDialogIncludeDefaulTokenLabel,
                            onPostRender: function() {
                                addHelpIconFor(this.getEl(), 'default_token');
                            }
                        }
                    ],
                    buttons: [
                        {
                            text: I18n.buttonSelect,
                            onclick: function() {
                                var sel = campaignDialogWindow.find('#selcamp')[0];
                                if (sel.value() === 0) {
                                    ed.windowManager.alert(I18n.campaignSelectDialogNothingSelected);
                                    return;
                                }

                                ed.selection.setContent('[ingagehub_campaign id=' + sel.value() + ']' + sel.text() + '[/ingagehub_campaign]');

                                var requestDefaultToken = (campaignDialogWindow.find('#deftok').length > 0 && campaignDialogWindow.find('#deftok')[0].value());

                                ed.windowManager.close(campaignDialogWindow);

                                if (requestDefaultToken) {
                                    setDefaultToken();
                                }

                                ed.windowManager.alert(I18n.campaignSelectDialogConfirmation);
                            }
                        },
                        {
                            text: I18n.buttonCancel,
                            onclick: function() {
                                ed.windowManager.close(campaignDialogWindow);
                            }
                        },
                        {
                            text: I18n.buttonCampaigns,
                            onclick: function() {
                                redirectIfClean(INgageHub.campaignsUrl, campaignDialogWindow);
                            }
                        }
                    ]
                });
            };

            ////////////////////////////////////////
            ////////////////////////////////////////
            var optionsDialog = function(campaign_shortcode) {
                //
                // Retrieve campaign and attachments from content and validate.
                //

                if (ed.getContent().indexOf('[ingagehub_') < ed.getContent().indexOf('[ingagehub_campaign')) {
                    ed.windowManager.alert(helpText.campaign_shortcode);
                    return;
                }

                var campaign_id = parseInt(campaign_shortcode.shortcode.attrs.named.id);
                $.each(campaigns, function (k, v) {
                    if (parseInt(v.id) === campaign_id) {
                        campaign = v;
                    }
                });

                if (typeof(campaign.id) === 'undefined') {
                    navigateDialog(I18n.campaignUnknownTitle, I18n.campaignUnknown, I18n.buttonCampaigns, INgageHub.campaignsUrl);
                    return;
                }

                var available_attachments = [];
                $.each(campaign.campaign_attachments, function(k,v) {
                    available_attachments.push({ text: v.question, value: v.id });
                });

                var content = ed.getContent();
                var attachment_shortcode = wp.shortcode.next('ingagehub_attachment', content);
                while (typeof(attachment_shortcode) !== 'undefined') {
                    var matching_attachments = $.grep(campaign.campaign_attachments, function (v, k) {
                        return (v.id === parseInt(attachment_shortcode.shortcode.attrs.named.id));
                    });

                    if (0 === matching_attachments.length) {
                        navigateDialog(I18n.attachmentsDontBelongToCampaignTitle, I18n.attachmentsDontBelongToCampaign, I18n.buttonCampaigns, INgageHub.campaignsUrl + '&action=edit_campaign&campaign_id=' + campaign_id);
                        return;
                    }

                    available_attachments = $.grep(available_attachments, function (v, k) {
                        return (v.value !== parseInt(attachment_shortcode.shortcode.attrs.named.id));
                    });

                    content = content.substr(attachment_shortcode.index + attachment_shortcode.content.length);
                    attachment_shortcode = wp.shortcode.next('ingagehub_attachment', content);
                }

                //
                // Set up basic options dialog elements.
                //

                var campaign_name = campaign.name;
                if (campaign_name.length > 60) {
                    campaign_name = campaign_name.substr(0, 57) + '...';
                }

                var dialog_body = [
                    { type: 'label', text: '' },
                    { type: 'label', text: I18n.customizeDialogCampaignNameLabel + ': ' + campaign_name, style: 'font-weight: bold;' },
                    { type: 'label', text: '' }
                ];

                var dialog_buttons = [
                    {
                        text: I18n.buttonCancel,
                        onclick: function() {
                            ed.windowManager.close(optionsDialogWindow);
                        }
                    }
                ];

                //
                // options dialog body elements for attachment list
                //

                if (0 === available_attachments.length) {
                    dialog_body.push(
                        {
                            type: 'label',
                            text: '*IMAGE_CHECK*',
                            minWidth: 38,
                            minHeight: 36,
                            onPostRender: function() {
                                replaceImageFor(this.getEl());
                            }
                        },
                        {
                            type: 'label',
                            text: I18n.customizeDialogAttachmentsAllInserted,
                            style: 'padding-top: 8px;'
                        },
                        {
                            type: 'button',
                            text: I18n.buttonAttachments,
                            onClick: function() {
                                redirectIfClean(INgageHub.campaignsUrl + '&action=edit_campaign&campaign_id=' + campaign_id, optionsDialogWindow);
                            }
                        }
                    );

                } else {
                    available_attachments.unshift({ text: '', value: 0 });

                    dialog_body.push(
                        {
                            type: 'label',
                            text: '*IMAGE_ALERT*',
                            minWidth: 38,
                            minHeight: 36,
                            onPostRender: function() {
                                replaceImageFor(this.getEl());
                            }
                        },
                        {
                            type: 'container',
                            layout: 'stack',
                            items: [
                                {
                                    type: 'label',
                                    text: I18n.customizeDialogAttachmentsLabel,
                                    style: 'margin-bottom: 5px;'
                                },
                                {
                                    type: 'listbox',
                                    name: 'selatt',
                                    style: 'overflow-x: hidden',
                                    values: available_attachments,
                                    value: 0
                                }                            ]
                        },
                        {
                            type: 'container',
                            layout: 'stack',
                            items: [
                                {
                                    type: 'button',
                                    text: I18n.buttonInsertAttachment,
                                    style: 'text-align: center; margin-bottom: 5px;',
                                    onclick: function() {
                                        var sel = optionsDialogWindow.find('#selatt')[0];
                                        if (sel.value() === 0) {
                                            ed.windowManager.alert(I18n.customizeDialogAttachmentsNothingSelected);
                                            return;
                                        }

                                        ed.selection.setContent('[ingagehub_attachment id=' + sel.value() + ']' + sel.text() + '[/ingagehub_attachment]');

                                        ed.windowManager.close(optionsDialogWindow);

                                        ed.windowManager.alert(I18n.customizeDialogAttachmentInserted);
                                    }
                                },
                                {
                                    type: 'button',
                                    text: I18n.buttonAttachments,
                                    style: 'text-align: center;',
                                    onClick: function() {
                                        redirectIfClean(INgageHub.campaignsUrl + '&action=edit_campaign&campaign_id=' + campaign_id, optionsDialogWindow);
                                    }
                                }
                            ]
                        }
                    );
                }

                //
                // options dialog body elements for submission form
                //

                if (wp.shortcode.next('ingagehub_submit', ed.getContent())) {
                    dialog_body.push(
                        {
                            type: 'label',
                            text: '*IMAGE_CHECK*',
                            minWidth: 38,
                            minHeight: 36,
                            onPostRender: function() {
                                replaceImageFor(this.getEl());
                            }
                        },
                        {
                            type: 'label',
                            text: I18n.customizeDialogSubmissionShortcodePresent,
                            style: 'padding-top: 8px;',
                            onPostRender: function () {
                                addHelpIconFor(this.getEl(), 'submit');
                            }
                        },
                        {
                            type: 'label',
                            text: ''
                        }
                    );

                } else {
                    dialog_body.push(
                        {
                            type: 'label',
                            text: '*IMAGE_ALERT*',
                            minWidth: 38,
                            minHeight: 36,
                            onPostRender: function() {
                                replaceImageFor(this.getEl());
                            }
                        },
                        {
                            type: 'label',
                            text: I18n.customizeDialogSubmissionShortcodeNotPresent,
                            style: 'padding-top: 8px;',
                            onPostRender: function () {
                                addHelpIconFor(this.getEl(), 'submit');
                            }
                        },
                        {
                            type: 'button',
                            text: I18n.buttonAddSubmissionShortcode,
                            onclick: function () {
                                ed.windowManager.close(optionsDialogWindow);

                                submitDialog();
                            }
                        });
                }

                //
                // options dialog body elements for submission form
                //

                if (wp.shortcode.next('ingagehub_next_page', ed.getContent())) {
                    dialog_body.push(
                        {
                            type: 'label',
                            text: '*IMAGE_CHECK*',
                            minWidth: 38,
                            minHeight: 36,
                            onPostRender: function() {
                                replaceImageFor(this.getEl());
                            }
                        },
                        {
                            type: 'label',
                            text: I18n.customizeDialogNextPageShortcodePresent,
                            style: 'padding-top: 8px;',
                            onPostRender: function () {
                                addHelpIconFor(this.getEl(), 'next_page');
                            }
                        },
                        {
                            type: 'label',
                            text: ''
                        }
                    );

                } else {
                    dialog_body.push(
                        {
                            type: 'label',
                            text: '*IMAGE_INFO*',
                            minWidth: 38,
                            minHeight: 36,
                            onPostRender: function() {
                                replaceImageFor(this.getEl());
                            }
                        },
                        {
                            type: 'label',
                            text: I18n.customizeDialogNextPageShortcodeNotPresent,
                            style: 'padding-top: 8px;',
                            onPostRender: function () {
                                addHelpIconFor(this.getEl(), 'next_page');
                            }
                        },
                        {
                            type: 'button',
                            text: I18n.buttonAddNextPageShortcode,
                            onclick: function () {
                                ed.windowManager.close(optionsDialogWindow);

                                nextPageDialog();
                            }
                        }
                    );
                }

                //
                // options dialog body elements for default token
                //

                if (typeof(campaign_shortcode.shortcode.attrs.named.default_token) === 'undefined' || campaign_shortcode.shortcode.attrs.named.default_token.length !== 19) {
                    dialog_body.push(
                        {
                            type: 'label',
                            text: '*IMAGE_INFO*',
                            minWidth: 38,
                            minHeight: 36,
                            onPostRender: function () {
                                replaceImageFor(this.getEl());
                            }
                        },
                        {
                            type: 'label',
                            text: I18n.customizeDialogDefaultTokenNotPresent,
                            style: 'padding-top: 8px;',
                            onPostRender: function () {
                                addHelpIconFor(this.getEl(), 'default_token');
                            }
                        },
                        {
                            type: 'button',
                            text: I18n.buttonAddDefaultToken,
                            onclick: function () {
                                ed.windowManager.close(optionsDialogWindow);

                                setDefaultToken();
                            }
                        }
                    );

                } else {
                    dialog_body.push(
                        {
                            type: 'label',
                            text: '*IMAGE_CHECK*',
                            minWidth: 38,
                            minHeight: 36,
                            onPostRender: function () {
                                replaceImageFor(this.getEl());
                            }
                        },
                        {
                            type: 'label',
                            text: I18n.customizeDialogDefaultTokenPresent,
                            style: 'padding-top: 8px;',
                            onPostRender: function () {
                                addHelpIconFor(this.getEl(), 'default_token');
                            }
                        },
                        {
                            type: 'button',
                            text: I18n.buttonRemoveDefaultToken,
                            onclick: function () {
                                ed.windowManager.close(optionsDialogWindow);

                                removeDefaultToken();

                                ed.windowManager.alert(I18n.customizeDialogDefaultTokenRemoved);
                            }
                        }
                    );
                }

                var optionsDialogWindow = ed.windowManager.open({
                    title: I18n.customizeDialogTitle,
                    onPostRender: onPostRenderDialog,
                    body: [{
                        type: 'container',
                        layout: 'grid',
                        columns: 3,
                        spacing: 10,
                        alignH: 'stretch',
                        items: dialog_body
                    }],
                    buttons: dialog_buttons
                });
            };

            ////////////////////////////////////////
            ////////////////////////////////////////
            var submitDialog = function() {
                var submitDialogWindow = ed.windowManager.open({
                    title: I18n.submissionShortcodeDialogTitle,
                    body: [
                        { type: 'label', text: I18n.submissionShortcodeDialogLabel },
                        { type: 'textbox', name: 'btntext', value: I18n.submissionShortcodeDialogDefaultText }
                    ],
                    buttons: [
                        {
                            text: I18n.buttonAdd,
                            onclick: function() {
                                var buttonText = submitDialogWindow.find('#btntext')[0].value();
                                buttonText = buttonText.replace(/"/g, "'");
                                if (buttonText.length === 0 || buttonText === I18n.submissionShortcodeDialogDefaultText ) {
                                    ed.selection.setContent('[ingagehub_submit]');
                                } else {
                                    ed.selection.setContent('[ingagehub_submit button_text="' + buttonText + '"]');
                                }

                                ed.windowManager.close(submitDialogWindow);
                            }
                        },
                        {
                            text: I18n.buttonCancel,
                            onclick: function() {
                                ed.windowManager.close(submitDialogWindow);
                            }
                        }
                    ]
                });
            };

            ////////////////////////////////////////
            ////////////////////////////////////////
            var nextPageDialog = function() {
                var nextPageDialogWindow = ed.windowManager.open({
                    title: I18n.nextPageShortcodeDialogTitle,
                    body: [
                        { type: 'label', text: I18n.nextPageShortcodeDialogLabel },
                        { type: 'textbox', name: 'nextpage', value: I18n.nextPageShortcodeDialogDefaultText }
                    ],
                    buttons: [
                        {
                            text: I18n.buttonAdd,
                            onclick: function() {
                                var nextPageText = nextPageDialogWindow.find('#nextpage')[0].value();
                                ed.selection.setContent('[ingagehub_next_page]' + nextPageText + '[/ingagehub_next_page]');

                                ed.windowManager.close(nextPageDialogWindow);
                            }
                        },
                        {
                            text: I18n.buttonCancel,
                            onclick: function() {
                                ed.windowManager.close(nextPageDialogWindow);
                            }
                        }
                    ]
                });
            };

            ////////////////////////////////////////
            ////////////////////////////////////////
            var startDialog = function() {
                if (campaigns === null) {
                    loadCampaigns();

                } else {
                    campaign = {};
                    var campaign_shortcode = getCampaign();
                    if (campaign_shortcode) {
                        optionsDialog(campaign_shortcode);

                    } else {
                        campaignsDialog();
                    }
                }
            };

            startDialog();
        };

        /* Register the buttons */
        tinymce.create('tinymce.plugins.INgageHub', {
            init : function(ed, url) {
                ed.addButton( 'button_ih_connect', {
                    title : I18n.buttonEditorToolbar,
                    image : INgageHub.pluginUrl + 'images/ih_button_20x20.png',
                    onclick : function() { buttonDialogHandler(ed); }
                });
            },
            createControl : function(n, cm) {
                return null;
            }
        });
        /* Start the buttons */
        tinymce.PluginManager.add( 'com_ingagehub_mce_plugin', tinymce.plugins.INgageHub );
    })(jQuery);
    //</script>
