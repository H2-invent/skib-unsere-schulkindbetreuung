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
import Popper from 'popper.js';
import 'datatables.net-dt';
// Import TinyMCE
// Import TinyMCE
import niceScroll from 'jquery.nicescroll';

import trumbowgy from './trumbowyg';
import icon from 'trumbowyg/dist/ui/icons.svg'
import Chart from 'chart.js';
import prettyPrintJson from 'pretty-print-json';

global.$ = global.jQuery = $;

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
import('daterangepicker');
import('datatables.net');

import ('trumbowyg/dist/plugins/colors/trumbowyg.colors');
import ('trumbowyg/dist/plugins/cleanpaste/trumbowyg.cleanpaste');
import ('trumbowyg/dist/plugins/template/trumbowyg.template');
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

    $('#example').html(prettyPrintJson.toHtml(jsonData));
});
$(document).ready(function () {
    if (typeof optionsSnack !== 'undefined') {
        $.snackbar(optionsSnack);
    }


    var ctx = $('#myChart');
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            datasets: [{
                label: 'Erfolgreich',
                data: mailgunTimeSuccess,
                borderWidth: 1,
                borderColor:'#14ff1d',
                backgroundColor:'rgba(17,255,37,0.3)',
                lineTension: 0,
            },
                {
                    label: 'Temporary',
                    data: mailgunTimeWarning,
                    borderWidth: 1,
                    borderColor:'#ff2ef6',
                    backgroundColor:'rgba(255,8,237,0.3)',
                    lineTension: 0,
                },
                {
                    label: 'Permanent',
                    data: mailgunTimeFail,
                    borderWidth: 1,
                    borderColor:'#ff1100',
                    backgroundColor:'rgba(255,0,5,0.3)',
                    lineTension: 0,
                }]
        },
        options: {

            scales: {
                xAxes: [{
                    type: 'time',
                    time: {
                        unit: 'month',
                        distribution: 'series',
                        displayFormats: {
                            quarter: 'DD.MM.YYYY h:mm'
                        }
                    }
                }],
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]

            }
        }
    });


});
$(window).on('load', function () {


// Load a plugin.
    $.trumbowyg.svgPath = icon;
    $('.onlineEditor').trumbowyg({
        autogrow: true,
        semantic: false,
        tagClasses: {
            'h1':['h1-responsive'],
            'h2':['h2-responsive'],
            'h3':['h3-responsive'],
            'h4':['h4-responsive'],
            'blockquote':['note', 'note-primary', 'z-depth-2'],
        },

        btns: [
            ['viewHTML'],
            ['undo', 'redo'], // Only supported in Blink browsers
            ['formatting'],
            ['strong', 'em', 'del'],
            ['superscript', 'subscript'],
            ['link'],
            ['insertImage'],
            ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
            ['unorderedList', 'orderedList'],
            ['horizontalRule'],
            ['removeformat'],
            ['template'],
            ['foreColor', 'backColor'],
            ['fullscreen']
        ],
        plugins: {
            templates: [
                {
                    name: 'Template mit Zitat',
                    html: '<h1 class="h1-responsive">H1-Header</h1><hr><div class="row"><div class="col-md-8"><h2 class="h2-responsive">H2-Header</h2><hr><p>Text</p></div><div class="col-md-4"><p>Kleiner Text hier außerhalb des blauen Kastens</p><blockquote class="note note-primary z-depth-2"><p>Zitat kann hier eingefügt werden</p><footer class="blockquote-footer">byName</footer> </blockquote></div></div><hr>'
                },
                {
                    name: 'Template mit zwei Spalten',
                    html: '<h1 class="h1-responsive">H1-Header</h1><hr><div class="row"><div class="col-md-6"><h2 class="h2-responsive">H2-Header</h2><hr><p>Text</p></div><div class="col-md-6"><h2 class="h2-responsive">H2-Header</h2><hr><p>Text</p></div> </div>'
                }
            ]
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
        $('#loadContentModal .modal-content').load(url,function (e) {
            $('input[type="time"]').jqclockpicker({
                autoclose: true,
                donetext: "OK"
            });
        });

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