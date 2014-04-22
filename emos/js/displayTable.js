$(document).ready(function() {
	$("div.result-row").click(function() {
		var $delay = 200;
		
		$(this).children("div").slideToggle($delay);
		
		
		if($($(this).children("i")).attr('class') == 'icon-chevron-right') {
		    $($(this).children("i")).attr('class', 'icon-chevron-down');
		    $(this).css("border-bottom", "none");
		} else {
		    $($(this).children("i")).attr('class', 'icon-chevron-right');
		    $(this).css("border-bottom", "1px solid lightgrey");
		}
	});
});