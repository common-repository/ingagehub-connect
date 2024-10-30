jQuery(document).ready(function($) {
    $('[data-confirm]').click(function(e) {
        var target = $(e.target).closest('[data-confirm]').first();
        var message = target.attr('data-confirm');
        if (!confirm(message)) {
            e.preventDefault();
        }
    });

    $('input[type=button][data-href]').click(function(e) {
        location.href = $(e.target).attr('data-href');
    });

    $('#ih_admin_campaign_search').click(function() {
        $('#ih_admin_campaigns_table_filter_form').submit();
    });

    $('#ih_admin_campaign_refresh').click(function() {
        location.href = INgageHub.campaignsUrl + '&action=refresh';
    });

    var formChanged = false;

    $('input.submit[type=button]').click(function(e) {
        formChanged = false;

        var target = $(e.target);
        var form = target.parent('form');

        if (typeof(target.attr('name')) !== 'undefined') {
            form.append('<input type="hidden" name="' + target.attr('name') + '" value="1" />');
        }

        form.submit();
    });

    if ($('form.ih_admin_data_form').length > 0) {

        $('form.ih_admin_data_form input').change(function(e) {
            formChanged = true;
        });

        $('form.ih_admin_data_form select').change(function() {
            formChanged = true;
        });

        $('form.ih_admin_data_form textarea').change(function() {
            formChanged = true;
        });

        $(window).on('beforeunload', function(e) {
            formChanged = formChanged || (typeof(tinyMCE) !== 'undefined' && typeof(tinyMCE.activeEditor) !== 'undefined' && tinyMCE.activeEditor.isDirty());
            if (formChanged) return ' ';
        });
    }

    $('.ih_admin_data_form .ih_admin_radio_list li').click(function(e) {
        var t = $(e.target).closest('[data-value]');
        var value_target = $('#' + t.parent('ul[data-target]').attr('data-target'));
        var form = t.closest('.ih_admin_data_form');
        value_target.val(t.attr('data-value'));
        form.submit();
    });
});
