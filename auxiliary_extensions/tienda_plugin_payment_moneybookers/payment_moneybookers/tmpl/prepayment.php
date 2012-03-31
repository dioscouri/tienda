<?php 
defined('_JEXEC') or die('Restricted access'); 
?>
<style>
	#main{
	height:220px;
	}
	#payment_moneybookers{
	height:40px;
	}
	#gateway{
	border:0px;
	height:20px;
	}
</style>

<div id="main">
	<form action="<?php echo plg_tienda_escape($vars->action_url); ?>" method="post" target="gateway" onsubmit="return onSubmit()" >
	
	 	<?php  if ($vars->mixed_cart) { ?>
	    	<div class="note_green">
	        	<span class="alert"><?php  echo JText::_('TIENDA MONEYBOOKERS PLEASE NOTE') ?></span>
	            <?php echo JText::_('TIENDA MONEYBOOKERS MIXED CART MESSAGE'); ?>
	        </div>
	    <?php }  ?>
	    <div align="center" id="payment_moneybookers">
	    	<div align="center" class="prepayment_message">
	       		<?php  echo JText::_('Tienda Moneybookers Payment Standard Preparation Message'); ?>
	       	</div>
	       	<div class="prepayment_action">
	           	<div align="center">
	           		<input id="logo" type="image" src="media/com_tienda/images/moneybookers-logo.jpg" alt="Pay Here">
	           	</div>
	       		<div align="center" style=" padding: 10px;">        		
	       			<?php /*
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
       						echo "<b>".JText::_('COM_TIENDA_CHECKOUT_AMOUNT').":</b> ".TiendaHelperBase::currency( @$vars->amount );	
       					}       					
	       				*/
	       			?>
	       		</div>         	
	       <iframe id="gateway" name="gateway" width="100%" height="400px">
			Your Browser does not Support IFrames. Please use another browser or upgrade your browser.
			</iframe>	
	       	</div>


	    </div>
	    
	    <!-- MERCHANT DETAILS (Moneybookers Gateway Manual) -->
		<input type="hidden" name="emailcloak" value="{emailcloak=off}">
		<input type="hidden" name="pay_to_email" value="<?php echo plg_tienda_escape($vars->pay_to_email); ?>" />
		<input type="hidden" name="transaction_id" value="<?php echo plg_tienda_escape($vars->transaction_id); ?>" />
		<input type="hidden" name="return_url" value="<?php echo plg_tienda_escape($vars->return_url); ?>" />
		<input type="hidden" name="return_url_text" value="<?php echo plg_tienda_escape($vars->return_url_text); ?>" /> 	
		<input type="hidden" name="cancel_url" value="<?php echo plg_tienda_escape($vars->cancel_url); ?>" /> 
		<input type="hidden" name="status_url" value="<?php echo plg_tienda_escape($vars->status_url); ?>" /> 
		<input type="hidden" name="status_url2" value="<?php echo plg_tienda_escape($vars->status_url2); ?>" />
		<input type="hidden" name="language" value="<?php echo plg_tienda_escape($vars->language); ?>" /> 
		<input type="hidden" name="confirmation_note" value="<?php echo plg_tienda_escape($vars->confirmation_note); ?>" />	

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
		
		<input type="hidden" name="firstname" value="<?php echo plg_tienda_escape($vars->first_name); ?>" /> 	
		<input type="hidden" name="lastname" value="<?php echo plg_tienda_escape($vars->last_name); ?>" /> 	
		<input type="hidden" name="phone_number" value="<?php echo plg_tienda_escape($vars->phone_number); ?>" /> 	
		<input type="hidden" name="address" value="<?php echo plg_tienda_escape($vars->address); ?>" /> 	
		<input type="hidden" name="postal_code" value="<?php echo plg_tienda_escape($vars->postal_code); ?>" /> 	
		<input type="hidden" name="state" value="<?php echo plg_tienda_escape($vars->state); ?>" /> 
		<input type="hidden" name="pay_from_email" value="<?php echo plg_tienda_escape($vars->email); ?>" />	
		<input type="hidden" name="country" value="<?php echo plg_tienda_escape($vars->country); ?>" />
		<input type="hidden" name="city" value="<?php echo plg_tienda_escape($vars->city); ?>" />
	</form>
<script type="text/javascript">
function onSubmit() {
   document.getElementById('gateway').style.height ="550px";
   document.getElementById('main').style.height ="650px";
   document.getElementById('payment_moneybookers').style.height ="450px";
   document.getElementById('logo').style.height ="0px";
}
</script>

</div>

