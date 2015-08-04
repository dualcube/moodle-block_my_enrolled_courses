jQuery(document).ready(function($) {
    $('#hide').attr('disabled', 'disabled');
    $('#visible').change(function() {
        $('#hide').removeAttr('disabled');
    });

    $('#show').attr('disabled', 'disabled');
    $('#hidden').change(function() {
        $('#show').removeAttr('disabled');
    });
});