var notice = {
    noticeCount: 0,

    deleteOldNotices: function() {
        $('div.notice.hidden').each(function(){
            $(this).remove();
            this.noticeCount--;
        });
    },

    create: function(message) {
        this.deleteOldNotices();
        var id = this.noticeCount;

        $('div.container-flash').append('<div class="notice" id="'+id+'">'+message+'</div>');
        var noticeNode = $('div.notice#'+id);

        flashMessageFadeOutGlobal();
        this.noticeCount++;
    },

    init: function() {
        var self = this;
        $('div.notice').each(function(){
            self.noticeCount++;
        });
    }
};

$(document).ready(function(){
    notice.init();
});