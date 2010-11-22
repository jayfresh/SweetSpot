<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Sweetspot Dash Build</title>
	
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	
		<link rel="stylesheet" type="text/css" href="<?php bloginfo( 'template_url' ); ?>/property-dash/admin-dash/css/reset.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="<?php bloginfo( 'template_url' ); ?>/property-dash/admin-dash/css/grid.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="<?php bloginfo( 'template_url' ); ?>/property-dash/admin-dash/css/jbase.css" media="screen" /> 
		<link rel="stylesheet" type="text/css" href="<?php bloginfo( 'template_url' ); ?>/property-dash/admin-dash/css/stickyfooter.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="<?php bloginfo( 'template_url' ); ?>/property-dash/admin-dash/css/style.css" media="screen" />  
		
			<script type="text/javascript" src="<?php bloginfo( 'template_url' ); ?>/property-dash/admin-dash/js/jquery-1.4.3.min.js"></script> 
			<script type="text/javascript" src="<?php bloginfo( 'template_url' ); ?>/property-dash/admin-dash/js/jquery.easing.1.3.js"></script> 
			<script type="text/javascript" src="<?php bloginfo( 'template_url' ); ?>/property-dash/admin-dash/js/slider.js"></script> 
			<script type="text/javascript" src="<?php bloginfo( 'template_url' ); ?>/property-dash/admin-dash/js/jquery.scrollTo-1.4.2-min.js"></script> 
			<script type="text/javascript" src="<?php bloginfo( 'template_url' ); ?>/property-dash/admin-dash/js/jquery.validate.js"></script>
			
			<script type="text/javascript" src="<?php bloginfo( 'template_url' ); ?>/property-dash/admin-dash/js/custom-forms.js"></script>
			
			<script type="text/javascript">
				$(document).ready(function() {
					var addressNamesObj = {},
						postCodeNamesObj = {};
					$('select.address, input.postCode').each(function(i, elem) {
						addressNamesObj[$(elem).attr('name')] = {
							required: {
								depends: function(element) {
									return !$(element).siblings('input[type=checkbox]:checked').length;
								}
							}
						};
					});
					$("#housemateDetails").validate({
						rules: addressNamesObj
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
						}).
						keypress(function(e){
							if(e.which == 13){
								toggleSection($(e.target).next(section));
								$(this).blur();
							}
						});
				});
			</script>

	
	</head>
	<body>