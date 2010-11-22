$(document).ready(function() {
	$('.properties > div > ul').tabs({ fx: { opacity: 'toggle' } });
	
	$('form.deposit_payment').submit(function() {
		var $custom = $(this.custom);
		if(!$custom.val()) {
			$custom.val('please supply your name!');
			return false;
		} else {
			return true;
		}
	});
});
