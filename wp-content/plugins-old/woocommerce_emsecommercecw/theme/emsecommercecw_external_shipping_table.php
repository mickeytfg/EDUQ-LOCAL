
<div class="emsecommercecw-external-checkout-shipping">
	<?php if (!empty($errorMessage)): ?>
		<p class="payment-error woocommerce-error">
			<?php print $errorMessage; ?>
		</p>
	<?php endif; ?>
	<h3><?php echo __("Shipping Option", "woocommerce_emsecommercecw")?></h3>
	<table class="emsecommercecw-external-checkout-shipping-table">
	
	<?php 
	echo $rows;
	?>
	
	</table>
	<input type="submit" class="emsecommercecw-external-checkout-shipping-method-save-btn button btn btn-success emsecommercecw-external-checkout-button" name="save" value="<?php echo __("Save Shipping Method", "woocommerce_emsecommercecw"); ?>" data-loading-text="<?php echo __("Processing...", "woocommerce_emsecommercecw"); ?>">
	
	
	<script type="text/javascript">
	jQuery(function(){
		jQuery('.emsecommercecw-external-checkout-shipping-method-save-btn').hide();
	
		
		jQuery('.emsecommercecw-external-checkout-shipping-table  input:radio').on('change', function(){
					jQuery('.emsecommercecw-external-checkout-shipping-method-save-btn').click();
				
		});
	});
	</script>
</div>
