var init = function() {
	$(document).on('click','.show-hide.small',function(){
		var id = $(this).attr('id');
		var name = $(this).attr('name');

		if($(this).html() == "+") {
			$(this).html("-");
		} else {
			$(this).html("+");
		}

		$("span").each(function(){
			if($(this).attr("id") == id && $(this).attr("name") == name) {
				if($(this).attr("hidden")) {
					$(this).attr("hidden", false);
				} else {
					$(this).attr("hidden", true);
				}
			}

		});
	});
}

$(document).ready(function(){
	init();
});