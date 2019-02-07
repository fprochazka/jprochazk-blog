var resizeBody = function() {
	var contentHeight = $('.wrapper.content').height();
	if(contentHeight < window.innerHeight-(window.innerHeight/5) ) {
		$(".container-main").css("bottom", "0");
		$(".container-main").css("height", "100%");
	} else {
		$(".container-main").removeAttr("style");
	}
}

$(document).ready(function(){
	resizeBody();

	$(document).on('click', '*', function() {
		resizeBody();
	});
});