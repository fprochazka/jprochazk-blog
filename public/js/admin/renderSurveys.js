
var admin = {
	countVotes: function() {
		var total_votes = [];
		$("div.list-item.survey").each(function(){
			var id = $(this).attr("id");
			total_votes[id] = 0;
		});
		var n = 0;
		$("span.votecount").each(function(){
			console.log(n);
			var id = $(this).attr("id");
			var val = $(this).attr("value");
			console.log("id: "+id+", value: "+val);
			total_votes[id] += parseInt(val);
			n++;
		});
		$("span.votecount").each(function(){
			var id = $(this).attr("id");
			$(this).css("padding-left", function(){
				if(total_votes[id] != 0) {
					var val = $(this).attr("value");
					var str = ((val/total_votes[id])*100)+"px";
					var f_str = (Math.floor((val/total_votes[id])*100))+"%";
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
	admin.countVotes();
	$(document).on('click', '', admin.countVotes());
});