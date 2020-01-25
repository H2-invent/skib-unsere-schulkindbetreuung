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
import('bootstrap');
import('./mdb');
import('snackbarjs');
import('moment');
import ('morecontent-js/dist/jquery.morecontent');
import('jquery-confirm');
import('./jquery.bs.gdpr.cookies');

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


$('.deleteBtn').click(function (e) {
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

$(window).on('load', function () {
        $(function () {
            $('[data-toggle="popover"]').popover()
        });

    $('body').bsgdprcookies(bssettings);

    $('#cookiesBtn').on('click', function () {
        $('body').bsgdprcookies(settings, 'reinit');
    });
    }
);