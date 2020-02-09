var $ = jQuery.noConflict();
$(document).ready(function() {
    $('#custom-editor-input-wrapper').hide();
    $('select[name="bootstrap_css_path"]').on('change', function(event) {
        if($(this).val() == 'custom') {
            $('#custom-editor-input-wrapper').fadeIn(200);
        } else {
            $('#custom-editor-input-wrapper').fadeOut(200);
        }
    });
    $('select[name="bootstrap_css_path"]').trigger('change');

    $('#custom-editor-background-wrapper').hide();
    $('input[name="tinymce_custom_background"]').on('change', function(event) {
        if(this.checked === true) {
            $('#custom-editor-background-wrapper').fadeIn(200);
        } else {
            $('#custom-editor-background-wrapper').fadeOut(200);
        }
    });
    $('input[name="tinymce_custom_background"]').trigger('change');

    $('input[name="checkall"]').on('change', function(event) {
        if(this.checked === true) {
            $('input[name^="tbel_element_choice_"]').prop('checked', true);
        } else {
            $('input[name^="tbel_element_choice_"]').prop('checked', false);
        }
    });

    $('#colorpicker').ColorPicker({
        onShow: function (colpkr) {
            $(colpkr).fadeIn(500);

            return false;
        },
        onHide: function (colpkr) {
            $(colpkr).fadeOut(500);

            return false;
        },
        onChange: function (hsb, hex, rgb) {
            color = '#' + hex;
            $('#colorpicker').val(color);
        }
    });
});
