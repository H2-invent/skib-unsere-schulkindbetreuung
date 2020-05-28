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

import('snackbarjs');
import('moment');
import ('morecontent-js/dist/jquery.morecontent');
import('jquery-confirm');
import ('jquery-lazy');
import('./jquery.bs.gdpr.cookies');
import {
    jarallax,
    jarallaxElement,
    jarallaxVideo
} from 'jarallax';

import('./frontend');
import scrollingTabs from 'jquery-bootstrap-scrolling-tabs';

jarallaxVideo();
jarallaxElement();
jarallax(document.querySelectorAll('.jarallax'), {
    speed: 0.2
});
$(function () {
    $(function () {
        $(".scroller").on('click', function (e) {
            e.preventDefault();
            $('html, body').animate({scrollTop: $($(this).attr('href')).offset().top}, 500, 'linear');
        });
    });
});


$(document).ready(function () {
    $('.nav-tabs').scrollingTabs({
        bootstrapVersion: 4,
        cssClassLeftArrow: 'fa fa-chevron-left',
        cssClassRightArrow: 'fa fa-chevron-right',
        disableScrollArrowsOnFullyScrolled: true
    });

});

$(window).on('load', function () {
    $('body').bsgdprcookies(bssettings);
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
$(document).on('change', '.preisliste_trigger', function (e) {
    e.preventDefault();
    var $url = $('#preisliste_schule option:checked').val();
    var $gehalt = $('#preisliste_gehalt option:checked').val() ;
    var $art = $('#preisliste_schulart option:checked').val() ;
    $('#preislisteWrappre').load($url+'?'+$.param({
        art: $art,
        gehalt: $gehalt})+' #preisliste_content',function () {
        $(this)
            .find('.mdb-select')
            .materialSelect();
    })
});
$(document).on('click', '.loadInTarget', function (e) {
    e.preventDefault();
    var ele = $(this);
    $(ele.attr('data-wrapper')).load(ele.attr('href') + ' ' + ele.attr('data-target'), function () {
        $('.nav-tabs').scrollingTabs({
            bootstrapVersion: 4,
            cssClassLeftArrow: 'fa fa-chevron-left',
            cssClassRightArrow: 'fa fa-chevron-right',
            disableScrollArrowsOnFullyScrolled: true
        });
        initSocial();
    });

    window.history.pushState('test', "test", ele.attr('href'));

})
