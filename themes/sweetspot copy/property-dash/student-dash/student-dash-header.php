<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Sweetspot Dash Build</title>
	
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	
		<link rel="stylesheet" type="text/css" href="<?php bloginfo( 'template_url' ); ?>/property-dash/student-dash/css/reset.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="<?php bloginfo( 'template_url' ); ?>/property-dash/student-dash/css/grid.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="<?php bloginfo( 'template_url' ); ?>/property-dash/student-dash/css/jbase.css" media="screen" /> 
		<link rel="stylesheet" type="text/css" href="<?php bloginfo( 'template_url' ); ?>/property-dash/student-dash/css/stickyfooter.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="<?php bloginfo( 'template_url' ); ?>/property-dash/student-dash/css/style.css" media="screen" />  
		
			<script type="text/javascript" src="<?php bloginfo( 'template_url' ); ?>/property-dash/student-dash/js/jquery-1.4.3.min.js"></script> 
			<script type="text/javascript" src="<?php bloginfo( 'template_url' ); ?>/property-dash/student-dash/js/jquery.easing.1.3.js"></script> 
			<script type="text/javascript" src="<?php bloginfo( 'template_url' ); ?>/property-dash/student-dash/js/slider.js"></script> 
			<script type="text/javascript" src="<?php bloginfo( 'template_url' ); ?>/property-dash/student-dash/js/jquery.scrollTo-1.4.2-min.js"></script> 
			<script type="text/javascript" src="<?php bloginfo( 'template_url' ); ?>/property-dash/student-dash/js/jquery.validate.js"></script>
			
			<script type="text/javascript" src="<?php bloginfo( 'template_url' ); ?>/property-dash/student-dash/js/custom-forms.js"></script>
			
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

					$('a.pay')
						.siblings('.pay_deposit_form')
						.hide()
						.end()
						.click(function(e) {
							e.preventDefault();
							var $that = $(this);
							if($that.hasClass('disabled')) {
								return false;
							}
							$that.addClass('disabled')
								.siblings('.pay_deposit_form')
								.slideToggle('slow', function() {
									$that.removeClass('disabled');
								});
						});
				});
			</script>

	
	</head>
	<body>