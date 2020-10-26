<div id="emsecommercecw-payment-container" class="woocommerce">
	
	<script type="text/javascript" src="<?php echo $ajaxScriptUrl; ?>"></script>
	
	<?php if (isset($error_message) && !empty($error_message)): ?>
		<p class="payment-error woocommerce-error">
			<?php print $error_message; ?>
		</p>
	<?php endif; ?>
	
	<noscript><p class="payment-error woocommerce-error"><?php echo __('You have to activate JavaScript in your browser to complete the payment.', 'woocommerce_emsecommercecw'); ?></p></noscript>
	
	<?php if (isset($visible_fields) && !empty($visible_fields)): ?>
		<fieldset>
			<h3><?php print $paymentMethod; ?></h3>
			<?php print $visible_fields; ?>
		</fieldset>
	<?php endif; ?>
	
	<script type="text/javascript">
		var successCallback = function(valid){
			var callbackFunction = <?php echo $submitCallbackFunction ?>;

			var formFields = {};

			jQuery('#emsecommercecw-payment-container *[name]').each(function() {
				formFields[jQuery(this).attr('name')] = jQuery(this).val();
			});

			callbackFunction(formFields);
		}

		var failureCallback = function(errors, valid){
			alert(errors[Object.keys(errors)[0]]);
			jQuery('#emsecommercecw-submit').prop("disabled", false);

		}
	
		var submitFunction = function() {
			jQuery('#emsecommercecw-submit').prop("disabled", true);

			var validateFunctionName = 'cwValidateFields';
			var validateFunction = window[validateFunctionName];
			
			if (typeof validateFunction != 'undefined') {
				validateFunction(successCallback,failureCallback);
				return false;
			}
			successCallback([]);
			return false;
			
		};
	</script>
	
	<input type="submit" class="button alt btn btn-success emsecommercecw-payment-form-confirm" id="emsecommercecw-submit" name="submit" onclick="submitFunction();" value="<?php print __("I confirm my payment", "woocommerce_emsecommercecw"); ?>" />

</div>
<div id="emsecommercecw-back-to-checkout" class="emsecommercecw-back-to-checkout woocommerce">
	<a href="<?php
		$option = EMSeCommerceCw_Util::getCheckoutUrlPageId();
		echo get_permalink(EMSeCommerceCw_Util::getPermalinkIdModified($option));
	
	?>" class="button btn btn-danger"><?php print __("Change payment method", "woocommerce_emsecommercecw");?></a>
</div>
