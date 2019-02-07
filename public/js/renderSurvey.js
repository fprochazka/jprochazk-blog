var sidebar = {
	countVotes: function() {
		var total_votes = 0;
		$("span.survey.option").each(function(){
			var val = $(this).attr("value");
			total_votes += parseInt(val);
		});
		$("span.survey.option").each(function(){
			$(this).css("padding-left", function(){
				if(total_votes != 0) {
					var val = $(this).attr("value");
					var str = ((val/total_votes)*100)+"px";
					var f_str = (Math.floor((val/total_votes)*100))+"%";
					$(this).html(f_str);
					return str;
				} else {
					var str = 0+"px";
					return str;
				}
			});
		});
	}
}
$(document).ready(function(){
	$('input.survey.checkbox').click(function(){
		var temp_id = $(this).attr("value");
		$('input.survey.checkbox').each(function(){
			if($(this).attr("value") != temp_id) {
				$(this).prop("checked", false);
			}
		});
	});

	sidebar.countVotes();
	$('button.survey.send').click(function(){
		var data = {
			survey_id: 0,
			vote_id: 0,
		};

		$("input.survey.checkbox").each(function() {
			if($(this).prop("checked") == true) {
				data.survey_id = $("div.survey.container").attr("id");
				data.vote_id = $(this).attr("value");
			}
		});	
		
		if(data.vote_id != "n" && data.survey_id != "n") {
			//send request
			$.ajax({
				url: '/survey/vote',
				type: 'POST',
				data: data,

				success: function(data, status) {
					//show vote results
					$("ul.survey.option.output").prop("hidden", false);

					//hide vote options
					$("ul.survey.option.input").remove();
					$("button.survey.option.send").remove();

					$("span.survey.option").each(function(){
						if($(this).attr("id") == data.message["vote_id"]) {
							$(this).attr("value", (parseInt($(this).attr("value"))+1));
						}
					});

					sidebar.countVotes();
				},
				error: function(xhr, textStatus, errorThrown) { 
					//console.log(textStatus+": "+xhr.responseJSON);
	       		}  
			});
		}
	});
});