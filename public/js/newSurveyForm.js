var newOptionButton = '<button type="button" class="add_option_link">+</button>';
var deleteOptionButton = '<button type="button" class="remove_option_link">x</button>';
var _prototype = '<div class="survey_option"><label for="survey_options___name___title">__num__: </label><input type="text" class="survey_option" id="survey_options___name___title" name="survey[options][__name__][title]" required="required" maxlength="500" /> '+deleteOptionButton+'</div>';

$(document).ready(function() {
	$("div#survey_options").data('index', $("div#survey_options").find(':input').length);
	$("div#survey_options").append('<div class="survey_options_list"></div>')
	$("div#survey_options").append(newOptionButton);

	$("div#survey_options").attr("data-prototype", null);

	$("button.add_option_link").click(function(){
		//original prototype - uncomment the .attr("data-prototype", null); as well for it to work!
		//var prototype = $("div#survey_options").attr("data-prototype");
		var prototype = _prototype;
		var index = $("div#survey_options").data('index');

		prototype = prototype.replace(/__name__/g, index);
		prototype = prototype.replace(/__num__/g, index+1);

		$("div.survey_options_list").append(prototype);
    	$("div#survey_options").data('index', index + 1);
	});

	$(document).on('click','.remove_option_link',function(){
		id = $(this).parent().find("input").attr("name");
		id = id.replace(/^\D+|\D+$/g, "");

		$("div.survey_option").each(function(){
			temp_id = $(this).find("input").attr("name");
			temp_id = temp_id.replace(/^\D+|\D+$/g, "");

			if(temp_id > id) {
				$(this).find("label").html((temp_id)+": ");
				$(this).find("label").attr("for", "survey_options_"+(temp_id-1)+"_title");
				$(this).find("input").attr("id", "survey_options_"+(temp_id-1)+"_title");
				$(this).find("input").attr("name", "survey[options]["+(temp_id-1)+"][title]");
			}
		});

		$(this).parent().remove();

		$("div#survey_options").data('index', $("div#survey_options").data('index') - 1);
	});

	autosize($("textarea.form-field"));
});