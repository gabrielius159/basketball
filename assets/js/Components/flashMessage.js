export function showFlashMessage(message = '', type = 'success') {
    clearFlashMessageBlock();

    switch(type) {
        case 'success': {
            let messageBlock = $('#flash-message-js');
            console.log(messageBlock);

            messageBlock.removeClass('d-none');

            messageBlock.html('<div class="container-fluid text-center bg-warning" style="min-height: 50px; color: white; padding-top: 13px; padding-bottom: 13px; font-weight: bold;"><b>' + message + '</b></div>');
            messageBlock.show();
            global.flashMessage = setTimeout(function() {
                messageBlock.addClass('d-none');
            }, 6000);
            break;
        }
        case 'warning': {
            let messageBlock = $('#flash-message-js');

            messageBlock.removeClass('d-none');

            messageBlock.html('<div class="container-fluid text-center bg-danger" style="min-height: 50px; color: white; padding-top: 13px; padding-bottom: 13px; font-weight: bold;"><b>' + message + '</b></div>');
            messageBlock.show();
            global.flashMessage = setTimeout(function() {
                messageBlock.addClass('d-none');
            }, 6000);
            break;
        }
    }
}

function clearFlashMessageBlock() {
    let messageBlock = $('#flash-message-js');

    if(!messageBlock.hasClass('d-none')) {
        messageBlock.innerHTML = '';
        messageBlock.addClass('d-none');
        clearTimeout(global.flashMessage);
    }
}