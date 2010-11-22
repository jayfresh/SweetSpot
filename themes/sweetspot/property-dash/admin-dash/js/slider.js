// JavaScript Document

$(document).ready(function(){
	$('.flatmateForm').hide().css({opacity:0});
	$('.flatmateDetails h5').
		addClass('clickable').
		click(function(){				
			toggleSection($(this).next('.flatmateForm'));
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

function toggleSection(section){
	if(section.is(':visible')){
		section.animate({opacity:0});
		section.slideUp(500, 'easeInOutCubic');

	} else {
		section.slideDown({duration:400, queue: false, easing:'easeInOutCubic'});
		section.animate({opacity:1, duration:300, easing:'easeInOutCubic'});
	}	
}