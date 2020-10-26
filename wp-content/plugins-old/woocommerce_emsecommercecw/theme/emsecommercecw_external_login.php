
<div class="woocommerce emsecommercecw">
	<div class="emsecommercecw-external-checkout-login-info-message">
		<?php echo __("Dear customer we have received your billing and shipping information. In order to finish your order please create an account below or checkout as guest.", "woocommerce_emsecommercecw");?>
	
	</div>
	<div class="col2-set emsecommercecw-external-checkout-account">
		<?php if (!empty($errorMessage)): ?>
		<p class="payment-error woocommerce-error">
			<?php print $errorMessage; ?>
		</p>
		<?php endif; ?>
		<div class="col-1 emsecommercecw-external-checkout-login">
			<h3><?php echo __("Login", "woocommerce_emsecommercecw"); ?></h3>
			<form class="emsecommercecw-external-checkout-login-form" action="<?php echo $url;?>" method="POST">
				<label for="login-email" class="label"><?php echo __("Email / Username", "woocommerce_emsecommercecw"); ?></label>
				<input type="text" id="login-email" name="login-email" class="input-text" value="<?php echo $email;?>"/>
				<label for="login-password" class="label"><?php echo __("Password", "woocommerce_emsecommercecw"); ?></label>
				<input type="password" id="login-password" name="login-password" class="input-text"/>
				<input type="hidden" name="checkout-login" value="true">
				<input type="submit" class="button btn btn-success" value="<?php echo __("Login", "woocommerce_emsecommercecw");?>"/>
			</form>
		</div>
		
		<div class="col-2 emsecommercecw-external-checkout-register">
			<?php if($displayRegister || $displayGuest): ?>
				<h3><?php echo $displayGuest ? __("Confirm  email", "woocommerce_emsecommercecw") : __("Register", "woocommerce_emsecommercecw"); ?></h3>
				<form class="emsecommercecw-external-checkout-register-form" action="<?php echo $url;?>" method="POST">
					<label for="register-email" class="label"><?php echo __("Email", "woocommerce_emsecommercecw"); ?></label>
					<input type="text" id="register-email" name="register-email" class="input-text" value="<?php echo $email;?>"/>
					<?php if($displayRegister && $displayGuest): ?>		
						<label for="register-create-account" class="label"><?php echo __("Create account", "woocommerce_emsecommercecw"); ?></label>
						<input type="checkbox" id="register-create-account" name="register-create-account" class="input-checkbox"/>
					<?php endif; ?>
					<?php if($displayRegister): ?>
						<div id="create-account">
							<label for="register-password" class="label"><?php echo __("Password", "woocommerce_emsecommercecw"); ?></label>
							<input type="password" id="register-password" name="register-password" class="input-text"/>	
						</div>
					<?php endif; ?>
					
					<input type="hidden" name="checkout-register" value="true">
					<input type="submit" class="button btn btn-success" value="<?php echo $displayGuest ? __("Continue", "woocommerce_emsecommercecw") : __("Register", "woocommerce_emsecommercecw"); ?>"/>
				</form>
				<?php if($displayRegister && $displayGuest): ?>
					<script type="text/javascript">
						jQuery( 'input#register-create-account' ).on('change', function() {
						    	jQuery( 'div #create-account' ).hide();
					
								if ( jQuery( this ).is( ':checked' ) ) {
									jQuery( 'div #create-account' ).slideDown();
								}
							});
						jQuery( 'input#register-create-account' ).change();
					</script>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
</div>
	