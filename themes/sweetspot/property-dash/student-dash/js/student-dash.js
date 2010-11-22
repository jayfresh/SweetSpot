$(document).ready(function() {

	// validation for housemate form
	var addressNamesObj = {},
		messages = {},
		name;
	$('input.houseNo, input.postCode').each(function(i, elem) {
		name = $(elem).attr('name');
		addressNamesObj[name] = {
			required: {
				depends: function(element) {
					return !$(element).closest('div.housemateForm').find('input[type=checkbox]:checked').length;
				}
			}
		};
		messages[name] = {
			required: 'Required.'
		};
	});
	$("#housemateDetails").validate({
		rules: addressNamesObj,
		messages: messages
	});

	// hiding and showing holding deposit fine print
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