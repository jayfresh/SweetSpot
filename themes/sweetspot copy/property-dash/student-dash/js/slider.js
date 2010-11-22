// JavaScript Document

$(document).ready(function(){
	$('.housemateForm:gt(0)').hide().css({opacity:0});
	$('.housemateDetails h5').
		addClass('clickable').
		click(function(){
			var $clicked = $(this);
			if($clicked.hasClass('disabled')) {
				return;
			}
			$clicked.addClass('disabled');
			toggleSection($(this).next('.housemateForm'), function() {
				$clicked.removeClass('disabled');
			});
			$(this).blur();
			return false;
		}).
		keypress(function(e){
			if(e.which == 13){
				toggleSection($(e.target).next(section));
				$(this).blur();
			}
		});
});

function toggleSection(section, callback){
	if(section.is(':visible')){
		section.animate({opacity:0});
		section.slideUp({duration:500, easing:'easeInOutCubic', complete:callback, queue:false});

	} else {
		section.slideDown(400, 'easeInOutCubic', callback);
		section.animate({opacity:1, duration:300, easing:'easeInOutCubic'});
	}	
}