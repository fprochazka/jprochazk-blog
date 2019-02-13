var parseTags = function(e) {
	var str = e.text();
	var parser = new DOMParser();
	var doc = parser.parseFromString(str, "text/html");

	var nodes = doc.body.childNodes;

	e.text(null);
	e.append(nodes);
}

$(document).ready(function() {
	var path = window.location.pathname.split('/');
	if(path[1] == "post") {
		$('p.post.content').each(function(){
			parseTags($(this));
		});
	} else if(path[1] == "search") {
		$('div.list-item.content').each(function(){
			parseTags($(this));
		});
		$('span.list-item.title').each(function(){
			parseTags($(this));
		});
	} else {
		$('span.post.content').each(function(){
			parseTags($(this));
		});
	}
})