$(document).ready(function() {

					function toggleSection(section, callback){
						if(section.is(':visible')){
							section.animate({opacity:0});
							section.slideUp({duration:500, easing:'easeInOutCubic', complete:callback, queue:false});
					
						} else {
							section.slideDown(400, 'easeInOutCubic', callback);
							section.animate({opacity:1, duration:300, easing:'easeInOutCubic'});
						}	
					}
					
					/* hide the property details, except for the first one */
					$('.propertyList li h5+div').hide().css({opacity:0});
					$('.propertyList h5').
						addClass('clickable').
						click(function(){
							var $clicked = $(this);
							if($clicked.hasClass('disabled')) {
								return;
							}
							$clicked.addClass('disabled');
							toggleSection($(this).next('div'), function() {
								$clicked.removeClass('disabled');
							});
							$(this).blur();
							return false;
						});
						
					/* show property reset details on button click */
					$('a.reset_property').click(function() {
						var $clicked = $(this);
						if($clicked.hasClass('disabled')) {
							return;
						}
						$clicked.addClass('disabled');
						toggleSection($(this).next(), function() {
							$clicked.removeClass('disabled');
						});
						$(this).blur();
						return false;
					});					
				});