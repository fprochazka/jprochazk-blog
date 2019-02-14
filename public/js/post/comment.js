$(document).ready(function(){
	$("button#comment-send").click(function(){
		var data = {
			content: $('#comment-content').val()
		};
		$.ajax({
			url: path.post_comment,
			type: 'POST',
			data: data,

			success: function(data, status) {
				console.log("success");
				console.log(data);
				$('div.comments').prepend(
					'<div class="comment" id="'+ data.message["id"] +'"><span id="author">'+ data.message["author"] + '</span>: <br><span id="date">('+ data.message["date"] +')</span><span id="comment-edit" for="'+ data.message["id"] +'"> - <a href="" id="comment-edit">EDIT</a></span><br><span id="content" for="'+ data.message["id"] +'">'+ data.message["content"] + '</span></div>'
				);
			}
		});
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
		}

		var temp_url = path.post_comment_delete;
		temp_url = temp_url.replace(/__post-id__/g, post_id);
		temp_url = temp_url.replace(/__comment-id__/g, comment_id);

		$.ajax({
			url: temp_url,
			type: 'POST',
			data: data,

			success: function(data, status) {
				if (data.message == "xml") {
					console.log("request is not XML");
				} else if(data.message == "perm") {
					console.log("user is unauthorized to delete this comment");
				} else {
					console.log("success, " + status);
					parentDiv.remove();
				}
			}
		});
	});

	$(document).on('click', 'button#comment-edit-cancel', function() {
		var content = $(this).parent().find('textarea#comment-edit-content').text();

		$(this).parent().parent().append('<span id="content" for="'+$(this).parent().parent().attr('id')+'">'+content+'</span>');
		$('<span id="comment-edit" for="'+$(this).parent().parent().attr('id')+'"> - <a href="" id="comment-edit">EDIT</a></span>').insertAfter($(this).parent().parent().find('span#date'));
		$(this).parent().parent().find('div.comment-edit').remove();
	});

	$(document).on('click', 'button#comment-edit-send', function() {
		var _self = $(this);
		var comment_id = $(this).attr('for');
		var post_id = path.post_id;
		var data = {
			content: "",
			current_user: path.current_user
		};
		$('textarea#comment-edit-content').each(function(){
			var temp_id = $(this).attr('for');
			console.log(temp_id);
			if($(this).attr('for') == comment_id) {
				data.content = $(this).val();
			}
		});

		var temp_url = path.post_comment_edit;
		temp_url = temp_url.replace(/__post-id__/g, post_id);
		temp_url = temp_url.replace(/__comment-id__/g, comment_id);
		if(data.content != "") {
			$.ajax({
				url: temp_url,
				type: 'POST',
				data: data,

				success: function(data, status) {
					if(data.message == "str") {
						console.log("data is not of type string");
					} else if(data.message == "perm") {
						console.log("user is unauthorized to edit this comment");
					} else {
						console.log("success, " + status);
						_self.parent().parent().append('<span id="content" for="'+$(this).parent().parent().attr('id')+'">'+data.message["content"]+'</span>');
						$('<span id="comment-edit" for="'+$(this).parent().parent().attr('id')+'"> - <a href="" id="comment-edit">EDIT</a></span>').insertAfter(_self.parent().parent().find('span#date'));
						_self.parent().parent().find('div.comment-edit').remove();
					}
				}
			});
		} else {
			console.log("content is empty");
		}
	});
});