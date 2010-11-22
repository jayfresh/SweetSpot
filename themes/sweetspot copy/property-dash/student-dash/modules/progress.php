<?php
	/* use the flags to figure out which things 1-4 should be active vs. inactive
		1 - required nothing
		2 - required $paid_holding_deposit
		3 - required 2 + $paid_security_deposit + $submitted_housemate_info + $submitted_guarantor_info + $set_up_DD
		4 - required 3 + $signed_AST
	*/
	$progress_step = 1;
	if ($paid_holding_deposit) {
		$progress_step = 2;
		if($paid_security_deposit && $submitted_housemate_info && $submitted_guarantor_info && $set_up_DD) {
			$progress_step = 3;
			if($signed_AST) {
				$progress_step = 4;
			}
		}
	}
?>
<div class="module">
					<a href="#top" rel="self" class="right topLink">top &#94;</a>
					<a name="progress"></a><h3 class="green">Moving-in Progress<?php echo $is_lead_tenant ? " (Lead tenant)" : "" ?></h3>
					<div>
						<div id="progressPanel" class="grid6col left">
							<p<?php echo $progress_step==1 ? ' class="current"' : ''; ?>>First of all you have to place a holding deposit to indicate your interest in the property and hold it for a week. There's button for this under all your <a href="#past_viewings" rel="self">past viewings</a>. You can see all your <a href="#future_viewings" rel="self">upcoming viewings</a> too.</p>
							<p<?php echo $progress_step==2 ? ' class="current"' : ''; ?>>There's a few things to do now:<?php if($is_lead_tenant) : ?> let us know <a href="#housemates" rel="self">who your friends are</a> that you'll be moving in with;<?php endif; ?> pay your <a href="#deposit" rel="self">security deposit and 1st month's rent</a> in advance; let us know who your <a href="#guarantor" rel="self">guarantor</a> is so we can get in touch with them.</p>
							<p<?php echo $progress_step==3 ? ' class="current"' : ''; ?>>You're almost there! We've prepared your <a href="#ast" rel="self">tenancy agreement</a> for you to sign.</p>
							<p<?php echo $progress_step==4 ? ' class="current"' : ''; ?>>You're all set to move in. You should be getting the keys any time soon, and you can talk to your SweetSpot property manager about booking a van to help move your things.</p>
						</div>
						<div class="stages left grid6col">
							<div class="stage<?php echo $progress_step==1 ? " current" : ""; ?>"><span class="number">1</span>Pay Holding Deposit</div>
							<div class="stage<?php echo $progress_step==2 ? " current" : ""; ?>"><span class="number">2</span>Get Approved</div>
							<div class="stage<?php echo $progress_step==3 ? " current" : ""; ?>"><span class="number">3</span>Sign Tenancy Agreement</div>
							<div class="stage<?php echo $progress_step==4 ? " current" : ""; ?>"><span class="number">4</span>Move in!</div>
						</div>
					</div>
				</div>