/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.css';

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
import $ from 'jquery';

global.$ = global.jQuery = $;
import Popper from 'popper.js';

global.Popper = Popper;
import('bootstrap-material-design');
import('moment');
import('chart.js');
import('chartjs-color-string');
import('jquery-clockpicker');
import('malihu-custom-scrollbar-plugin');
import('jquery.cookie');
import('jquery-validation');
import('jquery-confirm');
import('material-design-icons');
import snackbar from 'snackbarjs';

import('daterangepicker');
import('datatables.net');
import 'datatables.net-dt';
import niceScroll from 'jquery.nicescroll';
// Import TinyMCE
import tinymce from 'tinymce/tinymce';

// A theme is also required
import 'tinymce/themes/silver/theme';

// Any plugins you want to use has to be imported
import 'tinymce/plugins/paste';
import 'tinymce/plugins/link';
import 'tinymce/plugins/table';
import 'tinymce/plugins/code';
import FroalaEditor from 'froala-editor';
import ('froala-editor/js/plugins/align.min');
import ('froala-editor/js/plugins/paragraph_style.min');
import ('froala-editor/js/plugins/link.min');
import ('froala-editor/js/plugins/paragraph_style.min');
import ('froala-editor/js/plugins/paragraph_format.min');
import ('froala-editor/js/plugins/code_view.min');
import ('froala-editor/js/plugins/colors.min');

$(".side-navbar").niceScroll({cursorcolor: '#0058B0'});

$('#toggle-btn').on('click', function (e) {

    e.preventDefault();

    if ($(window).outerWidth() > 1194) {
        $('nav.side-navbar').toggleClass('shrink');
        $('.page').toggleClass('active');
    } else {
        $('nav.side-navbar').toggleClass('show-sm');
        $('.page').toggleClass('active-sm');
    }
});

$('table').DataTable({
        buttons: [
            {
                extend: 'excelHtml5',
                text: 'Save current page',
                exportOptions: {
                    modifier: {
                        page: 'current'
                    }
                }
            }
        ]
    }
);

$(document).ready(function () {
    if (typeof optionsSnack !== 'undefined') {
        $.snackbar(optionsSnack);
    }
})
$(window).on('load', function () {


// Load a plugin.


// Initialize editor.7
    FroalaEditor.DefineIcon('insert', {NAME: 'plus', SVG_KEY: 'add'});
    FroalaEditor.RegisterCommand('insert', {
        title: 'Insert HTML',
        focus: true,
        undo: true,
        refreshAfterCallback: true,
        callback: function () {
            this.html.insert('<div class="row"><div class="col-lg-6"><p>6/12</p></div><div class="col-lg-6"><p>6/12</p></div></div>');
        }
    });
    new FroalaEditor('textarea', {
          //  toolbarButtons: ['fontFamily','more_text', '|', 'fontSize', '|', 'paragraphFormat', '|', 'bold', 'italic', 'underline', 'undo', 'redo', 'more_misc','|','insert'],

        }
    );


    tinymce.init({
        selector: 'text',
        plugins: ['paste', 'link', 'table','code',''],
        toolbar: ' undo redo|bootrapRow bsCollum | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image|code',
        height: 500,
        content_css : '/myLayout.css',
        setup: function (editor) {

            editor.ui.registry.addButton('bootrapRow', {
                text: 'BS Row',
                onAction: function (_) {
                    editor.insertContent('<div class="row"><div class="col-lg-6"><p>6/12</p></div><div class="col-lg-6"><p>6/12</p></div></div>');
                }
            });
            editor.ui.registry.addMenuButton('bsCollum', {
                text: 'BS Col',
                fetch: function (callback) {
                    var item = [

                        {
                            type: 'menuitem',
                            text: 'col-lg-' + 1,
                            onAction: function () {
                                editor.insertContent('<div class="col-lg-1"><p>Here is the col-1 Collum</p></div>');
                            }
                        },
                        {
                            type: 'menuitem',
                            text: 'col-lg-6',
                            onAction: function () {
                                editor.insertContent('<div class="col-lg-6"><p>Here is the col-6 Collum</p></div>');
                            }
                        },
                        {
                            type: 'menuitem',
                            text: 'col-lg-12',
                            onAction: function () {
                                editor.insertContent('<div class="col-lg-12"><p>Here is the col-12 Collum</p></div>');
                            }
                        }


                    ];


                    var items = [
                        {
                            type: 'menuitem',
                            text: 'col-lg-1',
                            onAction: function () {
                                editor.insertContent('<div class="col-lg-1">You clicked menu item 1!</div>');
                            }
                        }

                    ];
                    callback(item);
                }
            });
        }
    });

    $('input[type="date"]').daterangepicker({
        "singleDatePicker": true,
        autoUpdateInput: false,
        locale: {
            "format": "YYYY-MM-DD",
            "separator": " - ",
        }
    }, function (chosen_date) {
        $(this).val(chosen_date.format('YYYY-MM-DD'));
    })
        .on('apply.daterangepicker', function (ev, picker) {
            //do something, like clearing an input
            var ele = $(this);
            ele.val(picker.startDate.format('YYYY-MM-DD'))
        });


    $('input[type="time"]').jqclockpicker({
        autoclose: true,
        donetext: "OK"
    });

    $(document).on('click', '.loadInTarget', function (e) {
        e.preventDefault();
        var ele = $(this);
        var url = ele.attr('href');
        var target = ele.attr('data-target');
        $(target).load(url + ' ' + target);
        var text = ele.text();
        var dropdown = ele.closest('.dropdown').find('button');
        dropdown.text(text);
    });

    $(document).on('click', '.loadContent', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        $('#loadContentModal').modal('show');
        $('#loadContentModal .modal-content').load(url);
    });


    $(document).on('click', '.deleteBtn', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var type = $(this).attr('type');

        $.confirm({
            title: confirmTitle,
            content: confirmText,
            theme: 'material',
            buttons: {
                confirm: function () {
                    $.ajax({
                        url: url,
                        type: type,
                        success: function (data) {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            }
                        }
                    });
                },
                cancel: function () {

                },

            }
        });
    });
});