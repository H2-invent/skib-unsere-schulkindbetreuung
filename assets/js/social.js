/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

import jssocial from 'jssocials';

function initSocial(){

    $("#share").jsSocials({
        shares: ["email", {
            share: "twitter",
            label: "Tweet",
            logo: "fab fa-twitter",
            shareIn: "blank",
        },
            {
                share: "facebook",
                label: "Facebook",
                logo: "fab fa-facebook",
                shareIn: "blank",
            },
            {
                share: "whatsapp",
                label: "Whatsapp",
                logo: "fab fa-whatsapp",
                shareIn: "blank",
            }],
        showCount: function(screenWidth) {
            return (screenWidth > 1920);
        },

        showLabel: function(screenWidth) {
            return (screenWidth > 1920);
        },

    });
}
export {initSocial};