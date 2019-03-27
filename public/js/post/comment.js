$(document).ready(function(){
	$("button#comment-send").click(function(){
		var data = {
			content: $('textarea#comment-content').val()
		};
		if(data.content !== null && data.content !== "") {
			$.ajax({
				url: path.post_comment,
				type: 'POST',
				data: data,

				success: function (data, status) {
					console.log("success");
					console.log(data);
					$('div.comments').prepend('<div class="comment" id="' + data["id"] + '"></div>');
					$('div.comment#' + data["id"]).append('<span id="author">' + data["author"] + '</span><br>');
					$('div.comment#' + data["id"]).append('<span id="date">(' + data["date"] + ')</span>');
					$('div.comment#' + data["id"]).append('<span id="comment-edit" for="' + data["id"] + '"> - <a href="" id="comment-edit">EDIT</a></span>');
					$('div.comment#' + data["id"]).append('<span id="comment-delete" for="' + data["id"] + '"> - <a href="" id="comment-delete">DELETE</a></span><br>');
					$('div.comment#' + data["id"]).append('<span id="content" for="' + data["id"] + '">' + data["content"] + '</span>');
				},
				error: function (xhr, status, errorThrown) {
					console.log("error");
					console.log(xhr);
				}
			});
		} else {
			$('textarea#comment-content').attr("placeholder", "Text area is empty!");
		}
	});

	$(document).on('click', 'a#comment-edit', function(e) {
		e.preventDefault();
		parentDiv = $(this).parent().parent();
		var content = parentDiv.find('span#content').text();
		parentDiv.find('span#content').remove();
		parentDiv.append('<div class="comment-edit"><textarea id="comment-edit-content" for="'+parentDiv.attr('id')+'"></textarea><br><button type="button" id="comment-edit-send" for="'+parentDiv.attr('id')+'">Send</button><button type="button" id="comment-edit-cancel" for='+parentDiv.attr('id')+'>Cancel</button></div>')
		parentDiv.find('textarea#comment-edit-content').text(content);
		$(this).parent().remove();
	});

	$(document).on('click', 'a#comment-delete', function(e) {
		e.preventDefault();

		parentDiv = $(this).parent().parent();

		var comment_id = $(this).parent().attr('for');
		var post_id = path.post_id;

		var data = {
			current_user: path.current_user
		};

		var temp_url = path.post_comment_delete;
		temp_url = temp_url.replace(/__post-id__/g, post_id);
		temp_url = temp_url.replace(/__comment-id__/g, comment_id);

		$.ajax({
			url: temp_url,
			type: 'POST',
			data: data,

			success: function(data, status) {
				console.log("success");
				parentDiv.remove();
			},
			error: function(xhr, status, errorThrown) {
				console.log(status);
				console.log(xhr.responseText);
			}
		});
	});

	$(document).on('click', 'button#comment-edit-cancel', function() {
		var content = $(this).parent().find('textarea#comment-edit-content').val();

		$(this).parent().parent().append('<span id="content" for="'+$(this).parent().parent().attr('id')+'">'+content+'</span>');
		$('<span id="comment-edit" for="'+$(this).parent().parent().attr('id')+'"> - <a href="" id="comment-edit">EDIT</a></span>').insertAfter($(this).parent().parent().find('span#date'));
		$(this).parent().parent().find('div.comment-edit').remove();
	});

	$(document).on('click', 'button#comment-edit-send', function() {
		var _self = $(this);
		var comment_id = $(this).attr('for');
		var post_id = path.post_id;
		var data = {
			current_user: path.current_user
		};
		$('textarea#comment-edit-content').each(function(){
			var temp_id = $(this).attr('for');
			if($(this).attr('for') === comment_id) {
				if($(this).val() === "" && $(this).val() === null) {
					$(this).attr("placeholder", "Text area is empty!")
				} else {
					data.content = $(this).val();
				}
			}
		});

		var temp_url = path.post_comment_edit;
		temp_url = temp_url.replace(/__post-id__/g, post_id);
		temp_url = temp_url.replace(/__comment-id__/g, comment_id);
		if(data.content !== null && data.content !== "") {
			$.ajax({
				url: temp_url,
				type: 'POST',
				data: data,

				success: function (data, status) {
					_self.parent().parent().append('<span id="content" for="' + $(this).parent().parent().attr('id') + '">' + data["content"] + '</span>');
					$('<span id="comment-edit" for="' + $(this).parent().parent().attr('id') + '"> - <a href="" id="comment-edit">EDIT</a></span>').insertAfter(_self.parent().parent().find('span#date'));
					_self.parent().parent().find('div.comment-edit').remove();
				},
				error: function (xhr, status, errorThrown) {
					console.log(xhr.responseText);
				}
			});
		}
	});
});