<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $vars->document->addScriptDeclaration("
	window.addEvent('domready', function(e) {
		var express_checkout_input = $('express_checkout');
		var express_checkout_button = $('express_checkout_button');
		var direct_payment_input = $('direct_payment');
		
		if (express_checkout_input && direct_payment_input && express_checkout_button) {
			$('direct_payment_form').setStyle('display', 'none');
				
			[express_checkout_input, express_checkout_button].each(function(el) {
				el.addEvent('click', function(e) {
					$('direct_payment_form').setStyle('display', 'none');
				});
			});
			
			direct_payment_input.addEvent('click', function(e) {
				$('direct_payment_form').setStyle('display', 'block');
			});
		}	
	});
")?>

<p><?php echo plg_tienda_escape($vars->note); ?></p>

<table>
	<tr>
		<td><input type="radio" id="express_checkout" name="payment_method" value="express_checkout" /></td>
		<td>
			<?php echo $vars->expresscheckout_form ?>		
		</td>		
	</tr>
	<tr>
		<td style="vertical-align:top"><input type="radio" id="direct_payment" name="payment_method" value="direct_payment" /></td>
		<td>
			<label for="direct_payment"><?php echo JText::_('COM_TIENDA_PAYPALPRO_DIRECT_PAYMENT_TITLE') ?></label>
			<?php echo $vars->directpayment_form ?>
		</td>
	</tr>
	
</table>