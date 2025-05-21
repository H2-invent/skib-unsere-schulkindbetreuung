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
import 'datatables.net-dt';

// Import TinyMCE
// Import TinyMCE
import niceScroll from 'jquery.nicescroll';

import trumbowgy from './trumbowyg';
import icon from 'trumbowyg/dist/ui/icons.svg'


import('jquery-ui/ui/widgets/sortable');
global.Popper = Popper;
import('bootstrap-material-design');
import('moment');
import {Chart} from 'chart.js';
import 'chartjs-color-string';

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
import ('formBuilder/dist/form-builder.min');
import snackbar from 'snackbarjs';
import {initTabs} from 'h2-invent-material-tabs';
import {Dropzone} from "dropzone";
import {initKeycloakGroups} from "./PersonenberechtigterInit";

global.Chart = Chart;
let formBuilderLoc;
var sucessFkt;
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
initTable();
function initTable() {
    try {
        const tables = document.querySelectorAll('table');
        tables.forEach((element) => {
            if (element.querySelector('thead')) {
                $(element).DataTable({
                    dom: 'Bfrtip',
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
                });
            }
        });
    } catch (e) {
        console.error(e);
    }
}



$(document).ready(function () {
    if (typeof optionsSnack !== 'undefined') {
        $.snackbar(optionsSnack);
    }

    if ($('.dropzone').length) {
        let myDropzone = new Dropzone(".dropzone");
    }

    if ($('#loerrach_eltern_kinderImKiga').prop('checked')) {
        $('#kigaOfKids').addClass('show')
    } else {
        $('#kigaOfKids').removeClass('show')
    }
    $('#loerrach_eltern_kinderImKiga').change(function () {
        if ($('#loerrach_eltern_kinderImKiga').prop('checked')) {
            $('#kigaOfKids').addClass('show')
        } else {
            $('#kigaOfKids').removeClass('show')
        }
    });


})

$(window).on('load', function () {
    if (typeof survey != 'undefined') {
        var options = {
            i18n: {
                locale: 'de-DE'
            },
            onSave: function (formData) {
                sendSurveyToServer(orgId, ferienId, formBuilderLoc.actions.getData('json', true));
            },
            dataType: 'json',
            formData: JSON.stringify(survey),
            notify: {
                error: function (message) {
                    $.snackbar({
                        text: message, // text of the snackbar
                        timeout: 10000, // time in milliseconds after the snackbar autohides, 0 is disabled
                    });
                },
                success: function (message) {
                    $.snackbar({
                        text: message, // text of the snackbar
                        timeout: 10000, // time in milliseconds after the snackbar autohides, 0 is disabled
                    });
                },
                warning: function (message) {
                    $.snackbar({
                        text: message, // text of the snackbar
                        timeout: 10000, // time in milliseconds after the snackbar autohides, 0 is disabled
                    });
                }
            }
        };
        formBuilderLoc = $('#surveyBuilder').formBuilder(options);
    }

// Load a plugin.
    $.trumbowyg.svgPath = icon;
    $('.onlineEditor').trumbowyg({
        autogrow: true,
        semantic: false,
        tagClasses: {
            'h1': ['h1-responsive'],
            'h2': ['h2-responsive'],
            'h3': ['h3-responsive'],
            'h4': ['h4-responsive'],
            'blockquote': ['note', 'note-primary', 'z-depth-2'],
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

    initKeycloakGroups();


    initdateRangepicker();
    initDeleteBtn();
    initSelectDate();
    inintDeleteAjax();
    initSendFictiveDate();
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
        $('#loadContentModal .modal-content').load(url, function (e) {
            $('input[type="time"]').jqclockpicker({
                autoclose: true,
                donetext: "OK"
            });
        });

    });


});

function initdateRangepicker() {
    $('input[type="date"]').each(function () {
        const ele = $(this); // Speichert das aktuelle Element
        console.log("minDate:", ele.data('mindate')); // Debugging
        ele.daterangepicker({
            "singleDatePicker": true,
            autoUpdateInput: false,
            minDate: ele.data('mindate'), // Zugriff auf data-mindate
            locale: {
                "format": "YYYY-MM-DD",
                "separator": " - ",
            }
        }, function (chosen_date) {
            ele.val(chosen_date.format('YYYY-MM-DD'));
        }).on('apply.daterangepicker', function (ev, picker) {
            const nativeInput = ele[0]; // Hole das native DOM-Element aus dem jQuery-Objekt
            const event = new Event('change', {bubbles: true}); // Erstelle ein 'change'-Event mit bubbling
            nativeInput.dispatchEvent(event); // Löst das Event aus
        });
    });

    $('input[type="time"]').jqclockpicker({
        autoclose: true,
        donetext: "OK"
    });
}

function sendSurveyToServer($orgId, $id, $question) {
    $.ajax({
        url: surveUrl,
        type: "POST",
        data: {org_id: $orgId, id: $id, survey: $question},
        success: function (data) {
            let $options = {
                content: data.text, // text of the snackbar
                timeout: 10000, // time in milliseconds after the snackbar autohides, 0 is disabled
            };
            var snack = $.snackbar($options);
        },

    });
}

function initDeleteBtn() {
    $(document).on('click', '.deleteBtn', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var type = $(this).attr('type');
        sucessFkt = undefined;
        sucessFkt = $(this).attr('successFKT');
        var $text = $(this).attr('text');
        if ($text) {
            var confirmText = $text
        }
        $.confirm({
            title: confirmTitle,
            content: confirmText,
            theme: 'material',
            buttons: {
                confirm: function () {
                    $.ajax({
                        url: url,
                        type: type,
                        success: success,
                    });
                },
                cancel: function () {
                },

            }
        });
    });

    function success(data) {
        if (typeof data.redirect !== 'undefined') {
            window.location.href = data.redirect;
        } else {
            $.snackbar({content: data.snack});
            console.log('test');
            if (typeof sucessFkt !== 'undefined') {
                var fn = window[sucessFkt];
                if (typeof fn === "function") fn();
            }
        }
    }

}


function inintDeleteAjax() {
    $(document).on('click', '.deleteAjax', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        $.ajax({
            url: url,
            success: function (data) {
                $.snackbar({content: data.snack});
                $('#blockContent').load(loadUrl)
            }
        });
    });


    $(document).on('click', '.deleteAjaxConfirm', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var $text = $(this).data('text');
        var $title = $(this).data('title');
        if ($text) {
            var confirmText = $text
        }
        if ($title) {
            confirmTitle = $title;
        }
        $.confirm({
            title: confirmTitle,
            content: confirmText,
            theme: 'material',
            buttons: {
                confirm: function () {
                    $.ajax({
                        url: url,
                        success: function (data) {
                            $.snackbar({content: data.snack});
                            $('#blockContent').load(loadUrl,()=>{
                                initdateRangepicker();
                                initSelectDate();
                                updateViewFromCookie();
                            })
                        }
                    });
                },
                cancel: function () {
                },

            }
        });
    });
}
function initSendFictiveDate() {
    $('#sendFictiveDate').click(function (e) {
            e.preventDefault();
            var target = $(this).attr('href') + '&fictiveDate=' + $('#fictiveDate').val();
            console.log(target);
            location.href = target;
        }
    )
}



function initSelectDate() {
    document.querySelectorAll('.startDateSelector').forEach(input => {
        input.addEventListener('change', event => {
            const newDate = event.target.value; // Das neue Datum
            const target = document.querySelector(event.target.dataset.target);
            if (target) {
                const url = new URL(target.href);
                url.searchParams.set('date', newDate); // Aktualisiere den "date"-Parameter in der URL
                target.href = url.toString(); // Setze die aktualisierte URL zurück
            }
        });
    });
}

function setCookie(name, value, days = 30) {
    const expires = new Date(Date.now() + days * 864e5).toUTCString();
    document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/`;
}

// Hilfsfunktion: Cookie lesen
function getCookie(name) {
    return document.cookie.split('; ').reduce((r, v) => {
        const parts = v.split('=');
        return parts[0] === name ? decodeURIComponent(parts[1]) : r;
    }, null);
}

// Ansicht aktualisieren (Tabelle oder Kachel)
function updateView(view) {
    const tableView = document.getElementById('tableView');
    const gridView = document.getElementById('tileView');

    if (view === 'tableView') {
        tableView.style.display = 'block';
        gridView.style.display = 'none';


    } else if (view === 'tileView') {
        tableView.style.display = 'none';
        gridView.style.display = 'block';
    }

    // Auswahl im Dropdown synchronisieren
    const selector = document.getElementById('tableSelector');
    if (selector) selector.value = view;
}

// Event-Listener für Dropdown-Auswahl
document.addEventListener('change', (e) => {
    const selector = e.target.closest('#tableSelector');
    if (selector) {
        const selectedView = selector.value;
        setCookie('preferredView', selectedView);
        updateView(selectedView);
    }
});

// Beim Laden: Cookie prüfen & Ansicht initial setzen
document.addEventListener('DOMContentLoaded', () => {
 updateViewFromCookie();
});

function updateViewFromCookie() {
    const savedView = getCookie('preferredView') || 'table'; // Standard: Tabelle
    updateView(savedView);
}