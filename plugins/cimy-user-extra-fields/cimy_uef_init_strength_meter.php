	<script type='text/javascript' src='<?php trailingslashit(get_option('siteurl'));?>wp-admin/js/password-strength-meter.dev.js?ver=20070405'></script>
	<script type="text/javascript">
		function check_pass_strength() {
			var pass1 = jQuery('#<?php echo $pass1_id; ?>').val(), pass2 = jQuery('#<?php echo $pass2_id; ?>').val(), user = jQuery('#<?php if (is_multisite()) echo "user_name"; else echo "user_login"; ?>').val(), strength;

			jQuery('#pass-strength-result').removeClass('short bad good strong');
			if ( ! pass1 ) {
				jQuery('#pass-strength-result').html( pwsL10n.empty );
				return;
			}

			strength = passwordStrength(pass1, user, pass2);

			switch ( strength ) {
				case 2:
					jQuery('#pass-strength-result').addClass('bad').html( pwsL10n['bad'] );
					break;
				case 3:
					jQuery('#pass-strength-result').addClass('good').html( pwsL10n['good'] );
					break;
				case 4:
					jQuery('#pass-strength-result').addClass('strong').html( pwsL10n['strong'] );
					break;
				case 5:
					jQuery('#pass-strength-result').addClass('short').html( pwsL10n['mismatch'] );
					break;
				default:
					jQuery('#pass-strength-result').addClass('short').html( pwsL10n['short'] );
			}
		}
		
		jQuery(document).ready( function() {
			jQuery('#<?php echo $pass1_id; ?>,').val('').keyup( check_pass_strength );
			jQuery('#<?php echo $pass2_id; ?>,').val('').keyup( check_pass_strength );
			jQuery('.color-palette').click(function(){$(this).siblings('input[name=admin_color]').attr('checked', 'checked')});
			check_pass_strength();
	    });
	</script>

	<script type='text/javascript'>
	/* <![CDATA[ */
	var commonL10n = {
		warnDelete: "You are about to permanently delete the selected items.\n  \'Cancel\' to stop, \'OK\' to delete."
	};
	try{convertEntities(commonL10n);}catch(e){};
	var pwsL10n = {
		empty: "Strength indicator",
		short: "Very weak",
		bad: "Weak",
		good: "Medium",
		strong: "Strong",
		mismatch: "Mismatch"
	};
	try{convertEntities(pwsL10n);}catch(e){};
	/* ]]> */
	</script>
