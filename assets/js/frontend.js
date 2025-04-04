/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../css/frontend.css';
// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
import $ from 'jquery';

import moment from 'moment';
import {jarallax, jarallaxElement, jarallaxVideo} from 'jarallax';

global.$ = global.jQuery = $;
global.moment = moment;
import('snackbarjs');
import ('morecontent-js/dist/jquery.morecontent');
import('jquery-confirm');
import('./jquery.bs.gdpr.cookies');
import('jquery-clockpicker');
import('daterangepicker');
import ('jquery-lazy');
import {initKeycloakGroups} from "./PersonenberechtigterInit";
import {Dropzone} from "dropzone";


jarallaxVideo();
jarallaxElement();
jarallax(document.querySelectorAll('.jarallax'), {
    speed: 0.2
});

$(document).ready(function () {
    let drop= [];
    if($('.dropzone').length){
        $('.dropzone').each(function () {
            console.log($(this));
           drop.push(new Dropzone('#'+($(this).attr('id'))));
        })
    }
    toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "md-toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": 300,
        "hideDuration": 1000,
        "timeOut": 5000,
        "extendedTimeOut": 1000,
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
    if (typeof errors !== 'undefined') {
        $(errors).each(function (i) {
            toastr[errors[i].type](errors[i].text);
        });
    }
    if ($('#loerrach_eltern_kinderImKiga').prop('checked')) {
        $('#kigaOfKids').collapse('show')
    } else {
        $('#kigaOfKids').collapse('hide')
    }
    $('#loerrach_eltern_kinderImKiga').change(function () {
        if ($('#loerrach_eltern_kinderImKiga').prop('checked')) {
            $('#kigaOfKids').collapse('show')
        } else {
            $('#kigaOfKids').collapse('hide')
        }
    });

    $('input.disablecopypaste').bind('copy paste', function (e) {
        e.preventDefault();
        alert('Please typ and dont Copy/Paste');
    });
    initKeycloakGroups();
});


$(document).on('click', '.deleteBtn', function (e) {
    e.preventDefault();
    var url = $(this).attr('href');
    var type = $(this).attr('type');
    var $text = $(this).attr('text');
    if ($text){
       var confirmText = $text
    }
    $.confirm({
        title: confirmTitle,
        content: confirmText,
        theme: 'modern',
        buttons: {
            confirm: {
                btnClass: 'btn btn-primary',
                text: 'OK',
                action: function () {
                    $.ajax({
                        url: url,
                        type: type,
                        success: function (data) {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            }
                        }
                    });
                }
            },
            cancel: {
                btnClass: 'btn btn-secondry',
                text: 'Cancel',
                action: function () {

                }
            },

        }
    });
});


$(document).on('click', '.loadContent', function (e) {
    e.preventDefault();
    var url = $(this).attr('href');
    $('#loadContentModal').load(url, function () {
        $('#loadContentModal ').modal('show');

    });
});

$('#loadContentModal').on('show.bs.modal', function (e) {
    var picker = $('.pickadate');
    for (var pTemp of picker){
        var p =$(pTemp);
        var minDate = '01.01.2000';
        var maxDate = new Date();
        if (p.data('min')){
            minDate = p.data('min')
        }
        if ( p.data('max')){
            maxDate = p.data('max')
        }
        p.pickadate({
            format: 'dd.mm.yyyy',
            formatSubmit: 'yyyy-mm-dd',
            selectYears: 2010,
            min: minDate,
            max: maxDate,
        });
    }

    $('input').trigger('change');
    $(this)
        .find('.mdb-select')
        .materialSelect();

});

$(window).on('load', function () {

        $(function () {
            $('.lazy').show().Lazy({
                // your configuration goes here
                scrollDirection: 'vertical',
                effect: 'fadeIn',
                visibleOnly: true,
                onError: function (element) {
                    console.log('error loading ' + element.data('src'));
                }
            });
        });


        $(function () {
            $('[data-toggle="popover"]').popover()
        });
// here is the daterangepicker stuff
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

// here is the clockpicker stuff
        $('input[type="time"]').jqclockpicker({
            autoclose: true,
            donetext: "OK"
        });
        $('body').bsgdprcookies(bssettings);

        $('#cookiesBtn').on('click', function () {
            $('body').bsgdprcookies(settings, 'reinit');
        });
    }
);

$(document).on('change', '.preisliste_trigger', function (e) {
    e.preventDefault();
    var $url = $('#preisliste_schule option:checked').val();
    var $gehalt = $('#preisliste_gehalt option:checked').val();
    var $art = $("input[name='preisliste_schulart']:checked").val();
    ;
    $('#preislisteWrappre').load($url + '?' + $.param({
        art: $art,
        gehalt: $gehalt
    }) + ' #preisliste_content', function () {

    })
});