<?php defined('_JEXEC') or die('Restricted access');

switch( $vars->mode )
{
	case 0: // everything went OK; payment was received so let's redirect to the confirm page
	{
		?>
		<?php echo JText::_( 'PLG_TIENDA_PAYMENT_PAYPAL_PRO_PROCESSING' ); ?>
		<script type="text/javascript">
			parent.location = '<?php echo JRoute::_( JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$vars->orderpayment_type."&paction=process_done" );?>';
		</script>

		<?php
		break;
	}
	
	case 1:// redirect to displaying standalone message
	{
		?>
		<?php echo JText::_( 'PLG_TIENDA_PAYMENT_PAYPAL_PRO_PROCESSING' ); ?>
		<script type="text/javascript">
			parent.location = '<?php echo JRoute::_( JURI::root()."index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=".$vars->orderpayment_type."&paction=display_standalone_message" );?>';
		</script>

		<?php
		break;
	}

	case 2: // operation was cancelled
	{
		echo JText::_('PLG_TIENDA_PAYMENT_PAYPALPRO_MESSAGE_OPERATION_CANCELLED');
		break;
	}

	case 3: // security check has failed
	{
		?>
		<p>
			<?php echo JText::_('PLG_TIENDA_PAYMENY_PAYPALPRO_MESSAGE_SECURITY_FAIL'); ?>
		</p>
		<?php
		break;
	}
	
	case 4:
	{		?>
		<p>
			<?php echo JText::_('PLG_TIENDA_PAYMENT_PAYPALPRO_SUBMITTED_MESSAGE'); ?>
		</p>
		<?php		
		break;
	}
}
