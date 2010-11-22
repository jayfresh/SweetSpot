$(document).ready(function() {
	$('a.pay_deposit')
		.next('.pay_deposit_form')
		.hide()
		.end()
		.click(function(e) {
			e.preventDefault();
			$(this).next().slideToggle('slow');
		});
});