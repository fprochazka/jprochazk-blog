var newOptionButton = '<button type="button" class="add_option_link">+</button>';
var _prototype = '<div class="survey_option"><label for="survey_options___name___title" class="required">Answer: </label><input type="text" id="survey_options___name___title" name="survey[options][__name__][title]" required="required" maxlength="500" /></div>';

$(document).ready(function() {
	$("div#survey_options").data('index', $("div#survey_options").find(':input').length);
	$("div#survey_options").append('<div class="survey_options_list"></div>')
	$("div#survey_options").append(newOptionButton);

	console.log($("div.survey_options_list").attr("data-prototype"));

	$("div.survey_options_list").attr("data-prototype", null);

	$("button.add_option_link").click(function(){
		var prototype = _prototype;
		var index = $("div#survey_options").data('index');

		prototype = prototype.replace(/__name__/g, index);

		$("div.survey_options_list").append(prototype);
    	$("div#survey_options").data('index', index + 1);
	});
});