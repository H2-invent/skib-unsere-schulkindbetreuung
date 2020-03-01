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
global.$ = global.jQuery = $;

import Popper from 'popper.js';

global.Popper = Popper;
import moment from 'moment';
global.moment =moment;

import('bootstrap');
import('./mdb');
import('snackbarjs');

import ('morecontent-js/dist/jquery.morecontent');
import('jquery-confirm');
import('./jquery.bs.gdpr.cookies');
import('jquery-clockpicker');
import('daterangepicker');
import ('jquery-lazy');
import {initSocial} from './social';
import daterangepicker from 'daterangepicker';
import {
    jarallax,
    jarallaxElement,
    jarallaxVideo
} from 'jarallax';


jarallaxVideo();
jarallaxElement();
jarallax(document.querySelectorAll('.jarallax'), {
    speed: 0.2
});
$(document).ready(function () {
initSocial();
});

$(document).on('click', '.loadContent', function (e) {
    e.preventDefault();
    var url = $(this).attr('href');
    $('#loadContentModal').load(url, function () {
        $('#loadContentModal ').modal('show');

    });
});


$(document).on('click', '.deleteBtn', function (e) {
    e.preventDefault();
    var url = $(this).attr('href');
    var type = $(this).attr('type');
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



$(window).on('load', function () {


        $(function() {
            $('.lazy').show().Lazy({
                // your configuration goes here
                scrollDirection: 'vertical',
                effect: 'fadeIn',
                visibleOnly: true,
                onError: function(element) {
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