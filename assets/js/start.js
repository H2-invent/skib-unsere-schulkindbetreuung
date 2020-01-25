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


$(function () {
    $("a[href*=\\#]").on('click', function (e) {
        e.preventDefault();
        $('html, body').animate({scrollTop: $($(this).attr('href')).offset().top}, 500, 'linear');
    });
});

$(document).on('click', '.loadContent', function (e) {
    e.preventDefault();
    var url = $(this).attr('href');
    $('#loadContentModal').load(url, function () {
        $('#loadContentModal ').modal('show');
    });

});


$(window).on('load', function() {
    $('.more').moreContent(
        {
            textClose: mehrLesen,
            textOpen: wenigerLesen,
            tpl: {

                btn: '<button class="btn btn-primary" type="button"></button>',

            },
        }
    );

});

