/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)

require('../css/app.css');
require('../css/global.scss');
require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');


// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
const $ = require('jquery');

global.flashMessage = null;

global.$ = global.jQuery = $;

require('bootstrap');

$(document).ready(function() {
    let flashMessageSuccess = $('#flashMessageSuccess');
    let flashMessageWarning = $('#flashMessageWarning');
    let flashMessageDraftPick = $('#flashMessageDraftPick');

    if(!flashMessageSuccess.hasClass('d-none') && flashMessageSuccess[0]) {
        setTimeout(function() {
            let navbar = $('#navbar');

            if(navbar.hasClass('') === false) { // mb-5
                navbar.addClass(''); // mb-5
            }

            flashMessageSuccess.addClass('d-none');
        }, 6000);
    } else if(!flashMessageWarning.hasClass('d-none') && flashMessageWarning[0]) {
        setTimeout(function() {
            let navbar = $('#navbar');

            if(navbar.hasClass('') === false) { // mb-5
                navbar.addClass(''); // mb-5
            }

            flashMessageWarning.addClass('d-none');
        }, 6000);
    } else if(!flashMessageDraftPick.hasClass('d-none') && flashMessageDraftPick[0]) {
        setTimeout(function() {
            let navbar = $('#navbar');

            if(navbar.hasClass('') === false) { // mb-5
                navbar.addClass(''); // mb-5
            }

            flashMessageDraftPick.addClass('d-none');
        }, 6000);
    }

});
