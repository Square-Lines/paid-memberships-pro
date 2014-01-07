<?php
	global $wpdb, $pmpro_msg, $pmpro_msgt, $pmpro_levels, $current_user, $levels, $pmpro_currency_symbol;
	
	//if a member is logged in, show them some info here (1. past invoices. 2. billing information with button to update.)
	if($current_user->membership_level->ID)
	{
	?>	
	<div id="pmpro_account">		
		<div id="pmpro_account-membership" class="pmpro_box">
			<h3><?php _e("My Memberships", "pmpro");?></h3>
			<table width="100%" cellpadding="0" cellspacing="0" border="0">
				<thead>
					<tr>
						<th><?php _e("Level", "pmpro");?></th>
						<th><?php _e("Membership Fee", "pmpro"); ?></th>
						<th><?php _e("Expiration", "pmpro"); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="pmpro_account-membership-levelname">
							<?php echo $current_user->membership_level->name?>
							<div class="pmpro_actionlinks">
								<?php do_action("pmpro_member_action_links_before"); ?>
								<a href="<?php echo pmpro_url("checkout", "?level=" . $current_user->membership_level->id, "https")?>"><?php _e("Renew", "pmpro");?></a>
								<?php if((isset($ssorder->status) && $ssorder->status == "success") && (isset($ssorder->gateway) && in_array($ssorder->gateway, array("authorizenet", "paypal", "stripe", "braintree", "payflow", "cybersource")))) { ?>
									<a href="<?php echo pmpro_url("billing", "", "https")?>"><?php _e("Update Billing Info", "pmpro"); ?></a>
								<?php } ?>
								<?php if(count($pmpro_levels) > 1 && !defined("PMPRO_DEFAULT_LEVEL")) { ?>
									<a href="<?php echo pmpro_url("levels")?>"><?php _e("Change", "pmpro");?></a>
								<?php } ?>
								<a href="<?php echo pmpro_url("cancel", "?level=" . $current_user->membership_level->id)?>"><?php _e("Cancel", "pmpro");?></a>
								<?php do_action("pmpro_member_action_links_after"); ?>
							</div> <!-- end pmpro_actionlinks -->
						</td>
						<td class="pmpro_account-membership-levelfee">
							<p><?php echo $pmpro_currency_symbol?><?php echo $current_user->membership_level->billing_amount?>
							<?php if($current_user->membership_level->cycle_number > 1) { ?>
								per <?php echo $current_user->membership_level->cycle_number?> <?php echo sornot($current_user->membership_level->cycle_period,$current_user->membership_level->cycle_number)?>
							<?php } elseif($current_user->membership_level->cycle_number == 1) { ?>
								per <?php echo $current_user->membership_level->cycle_period?>
							<?php } ?>
							<?php if($current_user->membership_level->billing_limit) { ?>
								<div><strong><?php _e("Duration", "pmpro");?>:</strong> <?php echo $current_user->membership_level->billing_limit.' '.sornot($current_user->membership_level->cycle_period,$current_user->membership_level->billing_limit)?></div>
							<?php } ?>						
							<?php 
								if($current_user->membership_level->trial_limit == 1) 
								{ 
									?>
									<div><?php printf(__("Your first payment will cost %s.", "pmpro"), $pmpro_currency_symbol . $current_user->membership_level->trial_amount); ?></div>
									<?php
								}
								elseif(!empty($current_user->membership_level->trial_limit)) 
								{
									?>
									<div><?php printf(__("Your first %d payments will cost %s.", "pmpro"), $current_user->membership_level->trial_limit, $pmpro_currency_symbol . $current_user->membership_level->trial_amount); ?></div>
									<?php
								}
							?>
						</td>
						<td class="pmpro_account-membership-expiration">
						<?php 
							if($current_user->membership_level->enddate) 
								echo date(get_option('date_format'), $current_user->membership_level->enddate);
							else
								echo "---";
						?>
						</td>
					</tr>
				</tbody>
			</table>
		</div> <!-- end pmpro_account-membership -->
		
		<div id="pmpro_account-profile" class="pmpro_box">	
			<?php get_currentuserinfo(); ?> 
			<h3><?php _e("My Account", "pmpro");?></h3>
			<?php if($current_user->user_firstname) { ?>
				<p><?php echo $current_user->user_firstname?> <?php echo $current_user->user_lastname?></p>
			<?php } ?>
			<ul>
				<li><strong><?php _e("Username", "pmpro");?>:</strong> <?php echo $current_user->user_login?></li>
				<li><strong><?php _e("Email", "pmpro");?>:</strong> <?php echo $current_user->user_email?></li>
			</ul>
			<div class="pmpro_actionlinks">
				<a href="<?php echo admin_url('profile.php')?>"><?php _e("Edit Profile", "pmpro");?></a>
				<a href="<?php echo admin_url('profile.php')?>"><?php _ex("Change Password", "As in 'change password'.", "pmpro");?></a>
			</div>
		</div> <!-- end pmpro_account-profile -->
	
		<?php
			//last invoice for current info
			//$ssorder = $wpdb->get_row("SELECT *, UNIX_TIMESTAMP(timestamp) as timestamp FROM $wpdb->pmpro_membership_orders WHERE user_id = '$current_user->ID' AND membership_id = '" . $current_user->membership_level->ID . "' AND status = 'success' ORDER BY timestamp DESC LIMIT 1");				
			$ssorder = new MemberOrder();
			$ssorder->getLastMemberOrder();
			$invoices = $wpdb->get_results("SELECT *, UNIX_TIMESTAMP(timestamp) as timestamp FROM $wpdb->pmpro_membership_orders WHERE user_id = '$current_user->ID' ORDER BY timestamp DESC LIMIT 6");				
			if(!empty($ssorder->id) && $ssorder->gateway != "check" && $ssorder->gateway != "paypalexpress" && $ssorder->gateway != "paypalstandard" && $ssorder->gateway != "twocheckout")
			{
				//default values from DB (should be last order or last update)
				$bfirstname = get_user_meta($current_user->ID, "pmpro_bfirstname", true);
				$blastname = get_user_meta($current_user->ID, "pmpro_blastname", true);
				$baddress1 = get_user_meta($current_user->ID, "pmpro_baddress1", true);
				$baddress2 = get_user_meta($current_user->ID, "pmpro_baddress2", true);
				$bcity = get_user_meta($current_user->ID, "pmpro_bcity", true);
				$bstate = get_user_meta($current_user->ID, "pmpro_bstate", true);
				$bzipcode = get_user_meta($current_user->ID, "pmpro_bzipcode", true);
				$bcountry = get_user_meta($current_user->ID, "pmpro_bcountry", true);
				$bphone = get_user_meta($current_user->ID, "pmpro_bphone", true);
				$bemail = get_user_meta($current_user->ID, "pmpro_bemail", true);
				$bconfirmemail = get_user_meta($current_user->ID, "pmpro_bconfirmemail", true);
				$CardType = get_user_meta($current_user->ID, "pmpro_CardType", true);
				$AccountNumber = hideCardNumber(get_user_meta($current_user->ID, "pmpro_AccountNumber", true), false);
				$ExpirationMonth = get_user_meta($current_user->ID, "pmpro_ExpirationMonth", true);
				$ExpirationYear = get_user_meta($current_user->ID, "pmpro_ExpirationYear", true);	
				?>	
				
				<div id="pmpro_account-billing" class="pmpro_box">
					<h3><?php _e("Billing Information", "pmpro");?></h3>
					<?php if(!empty($baddress1)) { ?>
					<p>
						<strong><?php _e("Billing Address", "pmpro");?></strong><br />
						<?php echo $bfirstname . " " . $blastname?>
						<br />		
						<?php echo $baddress1?><br />
						<?php if($baddress2) echo $baddress2 . "<br />";?>
						<?php if($bcity && $bstate) { ?>
							<?php echo $bcity?>, <?php echo $bstate?> <?php echo $bzipcode?> <?php echo $bcountry?>
						<?php } ?>                         
						<br />
						<?php echo formatPhone($bphone)?>
					</p>
					<?php } ?>
					
					<?php if(!empty($AccountNumber)) { ?>
					<p>
						<strong><?php _e("Payment Method", "pmpro");?></strong><br />
						<?php echo $CardType?>: <?php echo last4($AccountNumber)?> (<?php echo $ExpirationMonth?>/<?php echo $ExpirationYear?>)
					</p>
					<?php } ?>
					
					<?php 
						if((isset($ssorder->status) && $ssorder->status == "success") && (isset($ssorder->gateway) && in_array($ssorder->gateway, array("authorizenet", "paypal", "stripe", "braintree", "payflow", "cybersource")))) 
						{ 
							?>
							<div class="pmpro_actionlinks"><a href="<?php echo pmpro_url("billing", "")?>"><?php _e("Edit Billing Information", "pmpro"); ?></a></div>
							<?php 
						} 
					?>
				</div> <!-- end pmpro_account-billing -->				
			<?php
			}
		?>
		
		<?php if(!empty($invoices)) { ?>
		<div id="pmpro_account-invoices" class="pmpro_box">
			<h3><?php _e("Past Invoices", "pmpro");?></h3>
			<table width="100%" cellpadding="0" cellspacing="0" border="0">
				<thead>
					<tr>
						<th><?php _e("Date", "pmpro"); ?></th>
						<th><?php _e("Level", "pmpro"); ?></th>
						<th><?php _e("Amount", "pmpro"); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php 
					$count = 0;
					foreach($invoices as $invoice) 
					{ 
						if($count++ > 4)
							break;
						
						//get an member order object
						$invoice_id = $invoice->id;
						$invoice = new MemberOrder;
						$invoice->getMemberOrderByID($invoice_id);
						$invoice->getMembershipLevel();						
						?>
						<tr id="pmpro_account-invoice-<?php echo $invoice->code; ?>">
							<td><a href="<?php echo pmpro_url("invoice", "?invoice=" . $invoice->code)?>"><?php echo date(get_option("date_format"), $invoice->timestamp)?></td>
							<td><?php echo $invoice->membership_level->name?></td>
							<td><?php echo $pmpro_currency_symbol?><?php echo $invoice->total?></td>
						</tr>
						<?php 
					} 
				?>
				</tbody>
			</table>						
			<?php if($count == 6) { ?>
				<div class="pmpro_actionlinks"><a href="<?php echo pmpro_url("invoice"); ?>"><?php _e("View All Invoices", "pmpro");?></a></div>
			<?php } ?>
		</div> <!-- end pmpro_account-invoices -->
		<?php } ?>
		
		<?php if(has_filter('pmpro_member_links_top') || has_filter('pmpro_member_links_bottom')) { ?>
		<div id="pmpro_account-links" class="pmpro_box">
			<h3><?php _e("Member Links", "pmpro");?></h3>
			<ul>
				<?php 
					do_action("pmpro_member_links_top");
				?>
				
				<?php 
					do_action("pmpro_member_links_bottom");
				?>
			</ul>
		</div> <!-- end pmpro_account-links -->		
		<?php } ?>
	</div> <!-- end pmpro_account -->		
	<?php
	}
?>
