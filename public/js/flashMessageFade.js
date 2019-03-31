var flashMessageFadeOut = function() {
    $('div.notice').each(function(){
        $(this).addClass('hidden');
    });
};

$(document).ready(function(){
    setTimeout(flashMessageFadeOut, 2500);
});