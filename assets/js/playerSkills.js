import { showFlashMessage } from './Components/flashMessage';
import { getErrorMessageByCode } from "./Components/errorMessages";

$(window).on('load', function() {
   showPlayerAttributes();
});

$(document).ready(function () {
   $('body').on('click', 'span.player-skill-improve-button', function(e) {
      $.ajax({
         type: "POST",
         url: "/api/player-attribute/improve/" + $(this).attr('id').split('-')[1],
         data: {
         },
         success: function (data) {
            if(data['success'] === undefined) {
               showFlashMessage(getErrorMessageByCode(data['error']), 'warning');
               $("html, body").animate({ scrollTop: 0 }, "slow");
            } else {
               showPlayerAttributes();
               showFlashMessage('Skill improved successfully!', 'success');
            }
         }
      });
   });
});

function showPlayerAttributes() {
   let block = document.getElementById('playerAttributes');
   let lightMode = false;

   $.ajax({
      type: "GET",
      url: "/api/player-attribute/" + $('#playerAttributes').data('player'),
      data: {
      },
      success: function (data) {
         let message = '';
         let attributeList = data['items'];
         let index = 0;
         lightMode = data['lightMode'];

         for(var i = 0; i < attributeList.length; i++) {

            if(attributeList[i]['attributeImprovePrice']) {
               message += '<div class="row" style="' + (index === 0 ? '">' : 'border-top: 1px solid rgba(0, 0, 0, 0.125);">');
               message += '<div class="col-4 text-center pt-1 pb-1 ' + (lightMode ? 'text-dark' : 'text-white') + ' card-small-text">'+ attributeList[i]['attributeName'] +'</div>';
               message += '<div class="col text-center pt-1 pb-1 ' + (lightMode ? 'text-black-50' : 'text-white-50') + ' card-small-text">' + attributeList[i]['attributeLevel'] + '</div>';
               if(attributeList[i]['attributeLevelInNumber'] < 99) {
                  message += '<div class="col-4 text-center pt-1 pb-1  ' + (lightMode ? 'text-black-50' : 'text-white-50') + '  card-small-text">$' + attributeList[i]['attributeImprovePrice'] + '</div>';
                  message += '<div class="col text-center pt-1 pb-1  ' + (lightMode ? 'text-black-50' : 'text-white-50') + '  card-small-text"><span class="player-skill-improve-button" id="skill-' + attributeList[i]['attributeId'] + '" style="cursor:pointer;"><i class="fas fa-plus yellowColor"></i></span></div>';
               } else {
                  message += '<div class="col-4 text-center pt-1 pb-1  ' + (lightMode ? 'text-black-50' : 'text-white-50') + '  card-small-text"><i class="fas fa-lock"></i></div>';
                  message += '<div class="col text-center pt-1 pb-1  ' + (lightMode ? 'text-black-50' : 'text-white-50') + '  card-small-text"><i class="fas fa-lock"></i></div>';
               }
               message += '</div>';
            } else {
               message += '<div class="row" style="' + (index === 0 ? '">' : 'border-top: 1px solid rgba(0, 0, 0, 0.125);">');
               message += '<div class="col text-center pt-1 pb-1  ' + (lightMode ? 'text-dark' : 'text-white') + '  card-small-text">'+ attributeList[i]['attributeName'] +'</div>';
               message += '<div class="col text-center pt-1 pb-1  ' + (lightMode ? 'text-black-50' : 'text-white-50') + '  card-small-text">' + attributeList[i]['attributeLevel'] + '</div>';
               message += '</div>';
            }

            index++;
         }

         block.innerHTML = message;
         showPlayerDetails(lightMode);
      }
   });
}

function showPlayerDetails(lightMode = false) {
   let block = document.getElementById('playerDetails');

   $.ajax({
      type: "GET",
      url: "/api/player/player-details/" + $('#playerDetails').data('player'),
      data: {
      },
      success: function (data) {
         let message = '';
         let details = data['details'];

         message += '<div class="col d-flex flex-column">' +
             '<div class="col d-flex justify-content-center">' +
             '<i class="text-success far fa-money-bill-alt fa-3x"></i>' +
             '</div>' +
             '<div class="col d-flex justify-content-center">' +
             '<small class="text-muted mt-2 text-center">Money</small>' +
             '</div>' +
             '<div class="col d-flex justify-content-center mt-2">' +
             '<span class="text-center  ' + (lightMode ? 'text-black-50' : 'text-white-50') + '">$ ' + details['money'] + '</span>' +
             '</div>' +
             '</div>';

         message += '<div class="col d-flex flex-column">' +
             '<div class="col d-flex justify-content-center">' +
             '<i class="text-danger fas fa-star-half-alt fa-3x"></i>' +
             '</div>' +
             '<div class="col d-flex justify-content-center">' +
             '<small class="text-muted mt-2 text-center">Rating</small>' +
             '</div>' +
             '<div class="col d-flex justify-content-center mt-2">' +
             '<span class="text-center ' + (lightMode ? 'text-black-50' : 'text-white-50') + '">' + details['playerRating'] + '</span>' +
             '</div>' +
             '</div>';

         message += '<div class="col d-flex flex-column">' +
             '<div class="col d-flex justify-content-center">' +
             '<i class="text-warning fas fa-trophy fa-3x"></i>' +
             '</div>' +
             '<div class="col d-flex justify-content-center">' +
             '<small class="text-muted mt-2 text-center">Champion rings</small>' +
             '</div>' +
             '<div class="col d-flex justify-content-center mt-2">' +
             '<span class="text-center ' + (lightMode ? 'text-black-50' : 'text-white-50') + '">x' + details['championRings'] + ' </span>' +
             '</div>' +
             '</div>';

         message += '<div class="col d-flex flex-column">' +
             '<div class="col d-flex justify-content-center">' +
             '<i class="text-secondary fas fa-shield-alt fa-3x"></i>' +
             '</div>' +
             '<div class="col d-flex justify-content-center">' +
             '<small class="text-muted mt-2 text-center">DPOY award</small>' +
             '</div>' +
             '<div class="col d-flex justify-content-center mt-2">' +
             '<span class="text-center ' + (lightMode ? 'text-black-50' : 'text-white-50') + '">x' + details['dpoyAwards'] + ' </span>' +
             '</div>' +
             '</div>';

         message += '<div class="col d-flex flex-column">' +
             '<div class="col d-flex justify-content-center">' +
             '<i class="text-info fas fa-medal fa-3x"></i>' +
             '</div>' +
             '<div class="col d-flex justify-content-center">' +
             '<small class="text-muted mt-2 text-center">MVP award</small>' +
             '</div>' +
             '<div class="col d-flex justify-content-center mt-2">' +
             '<span class="text-center ' + (lightMode ? 'text-black-50' : 'text-white-50') + '">x' + details['mvpAwards'] + ' </span>' +
             '</div>' +
             '</div>';

         block.innerHTML = message;
      }
   });
}
