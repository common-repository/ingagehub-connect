//<script type="text/javascript">
    (function($) {

        var I18n = {
            submitBlockedInPreview: 'You cannot submit a response because this is just a preview of the content.',
            submitEmailRequired: 'You must enter an email address before submitting.',
            submitEmailInvalid: 'The email you entered does not appear to be a valid email address.',
            submitNoAnswers: 'You must select an answer to at least one question before submitting.',
            submitSuccessCheckEmail: 'Your response has been submitted.  Thank you for participating!  Please check your email for instructions on verifying your submission.',
            submitSuccess: 'Your response has been submitted.  Thank you for participating!',
            submitError: 'Apologies!  An error has occurred while attempting to submit your responses.  Please try again in a moment.'
        };

        var ihElements = [];

        var submitHandler = function(e) {
            e.preventDefault();

            var campaignId = 0;
            var campaignIdMatch = e.target.id.match(/ingagehub_submit_([0-9]+)/);
            if (campaignIdMatch) {
                campaignId = campaignIdMatch[1];
            } else {
                return;
            }

            var suffix = '_' + campaignId;
            var suffixLength = suffix.length;

            var submitData = {
                action: 'com_ingagehub_cr',
                id: campaignId
            };

            var email = $('#ingagehub_collect_email_' + campaignId).val();
            var token = $('#ingagehub_token_' + campaignId).val();

            if (token.length === 0 || location.href.indexOf('&preview') > 0) {
                alert(I18n.submitBlockedInPreview);
                return;
            } else if (email.length == 0) {
                alert(I18n.submitEmailRequired);
                return;
            } else if (!email.match('.+\\@.+\\..+')) {
                alert(I18n.submitEmailInvalid);
                return;
            }

            var inputElementCount = 0;
            var inputElementTouchedCount = 0;

            $.each(ihElements, function(k, v) {
                var attachmentIdsMatch = v.id.match(/ingagehub_attachment.*_([0-9]+)_([0-9]+)$/);
                if (attachmentIdsMatch && attachmentIdsMatch[2] === campaignId) {
                    var attachmentId = attachmentIdsMatch[1];
                    var elementType = $(v)[0].tagName.toLowerCase() === 'input' ? $(v).attr('type').toLowerCase() : 'textarea';

                    var responseFormat = $('#ingagehub_attachment_response_format_' + attachmentId + '_' + campaignId).val();
                    if (responseFormat === '0' || responseFormat === '1') {
                        if (elementType === 'radio' || elementType === 'checkbox') {
                            submitData[v.id] = {
                                value: v.value,
                                checked: v.checked
                            };

                            var freeText = $('#ingagehub_attachment_free_text_' + attachmentId + '_' + campaignId);
                            var freeTextValue = $('#ingagehub_attachment_free_text_value_' + attachmentId + '_' + campaignId);
                            if (freeText.length > 0 && freeTextValue.length > 0 && freeTextValue.val() === v.value && v.checked) {
                                submitData[v.id].freeText = freeText.val();
                            }

                            inputElementCount++;
                            if (v.checked) {
                                inputElementTouchedCount++;
                            }
                        }

                    } else if (responseFormat === '2') {
                        var answerIdsMatch = v.id.match(/ingagehub_attachment_([0-9]+)_[0-9]+_[0-9]+$/);
                        if (answerIdsMatch && elementType === 'hidden') {
                            var rank = $('#ingagehub_attachment_rank_' + answerIdsMatch[1] + '_' + attachmentId + '_' + campaignId);

                            submitData[v.id] = {
                                value: v.value,
                                checked: true,
                                rank: rank.val()
                            };

                            inputElementCount++;
                            if (rank.val() !== v.value) {
                                inputElementTouchedCount++;
                            }
                        }

                    } else if (responseFormat === '3') {
                        if (v.id === 'ingagehub_attachment_1_' + attachmentId + '_' + campaignId) {
                            submitData[v.id] = {
                                value: v.value,
                                checked: true,
                                freeText: $('#ingagehub_attachment_free_text_' + attachmentId + '_' + campaignId).val()
                            };

                            inputElementCount++;
                            if (submitData[v.id].freeText.trim().length > 0) {
                                inputElementTouchedCount++;
                            }
                        }
                    }

                } else if (v.id.indexOf(suffix) == v.id.length - suffixLength) {
                    submitData[v.id] = {
                        value: v.value
                    };
                }
            });

            if (inputElementCount > 0 && inputElementTouchedCount === 0) {
                alert(I18n.submitNoAnswers);

            } else {
                $(e.target).prop('disabled', true);
                $(e.target).css('opacity', 0.5);
                jQuery.ajax(INgageHub.ajaxUrl, {
                    method: 'POST',
                    data: submitData,
                    dataType: 'json',
                    success: function(data) {
                        if (data.response.status === 'OK') {
                            if (data.response.sending_email === true) {
                                alert(I18n.submitSuccessCheckEmail);
                            } else {
                                alert(I18n.submitSuccess);
                            }
                            if (data.next_page.length > 0) {
                                location.href = data.next_page;
                            }
                        } else {
                            alert(I18n.submitError);
                        }
                        $(e.target).prop('disabled', false);
                        $(e.target).css('opacity', 1.0);
                    },
                    error: function(x) {
                        alert(I18n.submitError);
                        $(e.target).prop('disabled', false);
                        $(e.target).css('opacity', 1.0);
                    }
                });
            }
        };

        var otherBoxHandler = function(e) {
            var target = $(e.target);
            var otherBoxListItem = target.parent('li');
            var otherBox = otherBoxListItem.find('.ihOtherBox');

            if (target.is(':checked')) {
                otherBox.show();
                otherBox.width(otherBoxListItem.width() * 0.8);
            } else {
                otherBox.hide();
            }
        };

        var rankedListHandler = function(e) {
            var target = $(e.target);
            var rankedList = target.parent('li').parent('ul');
            var rankedListItems = rankedList.find('.ihAnswerInputRank');
            var rank = parseInt(target.val());
            var originalRank = parseInt(target.attr('data-original-rank'));

            if (isNaN(rank) || !isFinite(rank) || rank < 1 || rank > rankedListItems.length) {
                target.val(originalRank);
                return;
            }

            if (rank != originalRank) {
                rankedListItems.each(function(i, v) {
                    var vv = $(v);
                    var vv_rank = parseInt($(v).val());

                    if (rank > originalRank) {
                        if (vv_rank >= originalRank && vv_rank <= rank) {
                            vv.val(vv_rank - 1);
                        }

                    } else {
                        if (vv_rank >= rank && vv_rank <= originalRank) {
                            vv.val(vv_rank + 1);
                        }
                    }

                    vv.attr('data-original-rank', vv.val());
                });
            }

            target.val(rank);
            target.attr('data-original-rank', rank);
        };

        jQuery(document).ready(function($) {
            $('*').each(function(k, v) {
                var vv = $(v);
                if (v.id.indexOf('ingagehub_') === 0 && (v.tagName.toLowerCase() === 'input' || v.tagName.toLowerCase() === 'textarea')) {
                    ihElements.push(v);

                    if (v.tagName.toLowerCase() === 'textarea') {
                        vv.width(vv.parent('ul').width() * 0.8);
                    }
                }

                if (v.id.indexOf('ingagehub_submit_') === 0 && v.tagName.toLowerCase() === 'button') {
                    vv.click(function(e) {
                        submitHandler(e);
                    });
                }

                if (vv.hasClass('ihOtherBoxTrigger')) {
                    vv.click(function(e) {
                        otherBoxHandler(e);
                    });
                }

                if (vv.hasClass('ihAnswerInputRank')) {
                    vv.attr('data-original-rank', vv.val());
                    vv.blur(function(e) {
                        rankedListHandler(e);
                    });
                }
            });
        });
    })(jQuery);
//</script>
