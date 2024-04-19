
import './app'

import $ from 'jquery';

global.$ = global.jQuery = $;
import * as daterangepicker from 'daterangepicker';


$('.dropdown-item').click(function () {
    var ele = $(this);
    var text = ele.text();
    var dropdown = ele.closest('.dropdown').find('button');
    dropdown.text(text);
    var type = ele.attr('data-type');
    var value = ele.attr('data-value') == 'null' ? null : ele.attr('data-value');
    search.block = null;

    switch (type) {
        case 'schule':
            search.schule = value;
            break;
        case 'wochentag':
            search.wochentag = value;
            break;
        case 'schuljahr':
            search.schuljahr = value;
            break;
        case 'klasse':
            search.klasse = value;
            break;
        default:
            break;

    }
    $('#childTable').html('<div id="childTableSimulation"><div class="center">Loading ...</div></div>')
    $('#childTable').load(searchUrl, search);
});

$('.print').click(function () {
    search.print = true;
    console.log(search);
    window.location = searchUrl+'?' + $.param(search);

    delete search.print;
    console.log(search);
});
$('.spreadsheet').click(function () {
    search.spread = true;
    console.log(search);
    window.location = searchUrl+'?' + $.param(search);

    delete search.spread;
    console.log(search);
});

$('#childTable').load(searchUrl, search);
var simDate = $('#daterangesim').daterangepicker({
    "opens": "center",
    "drops": "up",
    'startDate': $('#daterangesim').data('startdate'),
    'endDate': $('#daterangesim').data('enddate'),
    "autoApply": true,
}, function(start, end, label) {
    $('#daterangesim').find('span').text(start.format('DD.MM.YYYY')+' - '+end.format('DD.MM.YYYY'));
    getSim(start.format('DD.MM.YYYY'),end.format('DD.MM.YYYY'))
});
getSim($('#daterangesim').data('startdate'),$('#daterangesim').data('enddate'));
function getSim(start, end) {
    var searchSim = { ...search};
    searchSim.startDate=start;
    searchSim.endDate = end;
    $('#childTableSimulation').html('<div id="childTableSimulation">\n' +
        '                                <div class="center">\n' +
        '                                    <div class="wave"></div>\n' +
        '                                    <div class="wave"></div>\n' +
        '                                    <div class="wave"></div>\n' +
        '                                    <div class="wave"></div>\n' +
        '                                    <div class="wave"></div>\n' +
        '                                    <div class="wave"></div>\n' +
        '                                    <div class="wave"></div>\n' +
        '                                    <div class="wave"></div>\n' +
        '                                    <div class="wave"></div>\n' +
        '                                    <div class="wave"></div>\n' +
        '                                </div>    ');
    $('#childTableSimulation').load(searchUrl, searchSim);
}

