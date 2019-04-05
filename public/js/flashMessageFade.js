var flashMessageFadeOutGlobal = function() {
    setTimeout(function(){
        $('div.notice').each(function(){
            if(!$(this).hasClass('hidden')) {
                $(this).addClass('hidden');
            }
        });
    }, 2500);
};

$(document).ready(function(){
    flashMessageFadeOutGlobal();
});