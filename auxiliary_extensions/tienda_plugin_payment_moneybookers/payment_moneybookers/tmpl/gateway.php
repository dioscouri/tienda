
<form action="<?php echo plg_tienda_escape($vars->action_url); ?>" method="post" target="_self">
<div style="height:121px">

	       	<div class="prepayment_action">
	           	<div align="center">
	           		<img type="image" src="media/com_tienda/images/moneybookers-logo.jpg" alt="Pay Here">
	           	</div>
	       		<div align="center" style=" padding: 10px;">        		
	       			<?php
	       				if ( $vars->is_recurring )
	       				{
	       					echo "<b>".JText::_('Recurring Checkout Amount').":</b> ".TiendaHelperBase::currency( @$vars->rec_amount ); 
	       				}
	       				elseif ( $vars->mixed_cart )
       					{
       						echo "<b>".JText::_('Non-recurring Items Checkout Amount').":</b> ".TiendaHelperBase::currency( @$vars->amount );
       					}
       					else //Only Non-Recurring
       					{
       						echo "<b>".JText::_('Checkout Amount').":</b> ".TiendaHelperBase::currency( @$vars->amount );	
       					}       					
	       				
	       			?>
	       		</div>         	
	       	</div>
</div>

	    <!-- MERCHANT DETAILS (Moneybookers Gateway Manual) -->
		<input type="hidden" name="pay_to_email" value="<?php echo plg_tienda_escape($vars->pay_to_email); ?>" />
		<input type="hidden" name="transaction_id" value="<?php echo plg_tienda_escape($vars->transaction_id); ?>" />
		<input type="hidden" name="return_url" value="<?php echo plg_tienda_escape($vars->return_url); ?>" />
		<input type="hidden" name="return_url_text" value="<?php echo plg_tienda_escape($vars->return_url_text); ?>" /> 	
		<input type="hidden" name="cancel_url" value="<?php echo plg_tienda_escape($vars->cancel_url); ?>" /> 
		<input type="hidden" name="status_url" value="<?php echo plg_tienda_escape($vars->status_url); ?>" /> 
		<input type="hidden" name="status_url2" value="<?php echo plg_tienda_escape($vars->status_url2); ?>" />
		<input type="hidden" name="language" value="<?php echo plg_tienda_escape($vars->language); ?>" /> 
		<input type="hidden" name="confirmation_note" value="<?php echo plg_tienda_escape($vars->confirmation_note); ?>" />	
		<input type="hidden" name="logo_url" value="<?php echo plg_tienda_escape($vars->logo_url); ?>" />		
		<input type="hidden" name="merchant_fields" value="user_id, order_id, orderpayment_id, orderpayment_type" /> 
		<input type="hidden" name="user_id" value="<?php echo plg_tienda_escape($vars->user_id); ?>" /> 
		<input type="hidden" name="order_id" value="<?php echo plg_tienda_escape($vars->order_id); ?>" />
		<input type="hidden" name="orderpayment_id" value="<?php echo plg_tienda_escape($vars->orderpayment_id); ?>" />
		<input type="hidden" name="orderpayment_type" value="<?php echo plg_tienda_escape($vars->orderpayment_type); ?>" />
		
		<!-- PAYMENT DETAILS (Moneybookers Gateway Manual) -->	 
		<?php if ($vars->is_recurring): ?>
				<input type="hidden" name="rec_amount" value="<?php echo plg_tienda_escape($vars->rec_amount); ?>" />
				<input type="hidden" name="rec_start_date" value="<?php echo plg_tienda_escape($vars->rec_start_date); ?>" />
				<input type="hidden" name="rec_period" value="<?php echo plg_tienda_escape($vars->rec_period); ?>" />
				<input type="hidden" name="rec_cycle" value="<?php echo plg_tienda_escape($vars->rec_cycle); ?>" />
				<input type="hidden" name="rec_grace_period" value="<?php echo plg_tienda_escape($vars->rec_grace_period); ?>" />
		<?php else: ?>
				<input type="hidden" name="amount" value="<?php echo plg_tienda_escape($vars->amount); ?>" />
		<?php endif; ?>
		
		<input type="hidden" name="currency" value="<?php echo plg_tienda_escape($vars->currency); ?>" />			
		<input type="hidden" name="detail1_description" value="<?php echo plg_tienda_escape($vars->detail1_description); ?>" /> 
		<input type="hidden" name="detail1_text" value="<?php echo plg_tienda_escape($vars->detail1_text); ?>" /> 
		<input type="hidden" name="detail2_description" value="<?php echo plg_tienda_escape($vars->detail2_description); ?>" /> 
		<input type="hidden" name="detail2_text" value="<?php echo plg_tienda_escape($vars->detail2_text); ?>" /> 	
			
	</form>