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

import('./frontend');

$(document).on('click', '.chooseBlock', function (e) {
    e.preventDefault();
    var ele = $(this);
    var url = ele.attr('href');
    ele.closest('.card').find('.loader').removeClass('d-none');
    $.ajax({
        url: url,
        method: 'PATCH',
        success: function (data) {
            console.log(data);
            if(typeof(data.snack)!='undefined' ){
                $(data.snack).each(function (i) {
                    toastr[data.snack[i].type](data.snack[i].text);

                });
            }
            for(var c in data.blocks){

                changeState(data.blocks[c].id,data.blocks[c].state,data.blocks[c]);
            }
            ele.closest('.modal-body').find('.price').text(data.text);
            ele.closest('.card').find('.loader').addClass('d-none');
            if(data.error == 0){
                fetchPrice(ele,data.preisUrl);
            }



        }
    })
});


    $(document).on('change','#startDate',function () {
        var start = $(this).val();
        console.log(start);
        $.post($(this).data('url'),{startDate:start,kind_id:$(this).data('kindid')})
    })

function changeState(id, state, data) {
    var ele = $('#block-' + id);
    if (state == 0) {
        ele.addClass('bg-success');
        ele.find('.gebucht-text').text(data.cardText);
    } else if (state == 1) {
        ele.addClass('bg-success');
        ele.find('.gebucht-text').text(data.cardText);
    } else if (state == 2) {
        ele.removeClass('bg-success');
        ele.removeClass('bg-warning');
        ele.find('.gebucht-text').text(data.cardText);
    }

}

function fetchPrice(ele,url) {
    $.ajax({
        url: url,
        method: 'GET',
        success: function (data) {
            ele.closest('.modal-body').find('.price').text(data.betrag + '€')
        }

    });
}

$("#loadContentModal").on('hide.bs.modal', function () {
    $('#schulenShow').load(urlShool);

});