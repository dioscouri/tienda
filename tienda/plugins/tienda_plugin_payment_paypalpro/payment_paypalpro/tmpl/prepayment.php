<?php defined('_JEXEC') or die('Restricted access'); ?>

<div class="payment-local">
	<?php 
		if( $vars->security['SECURETOKEN'] == plgTiendaPayment_paypalpro::NO_SECURE_DATA && $vars->security['SECURETOKENID'] == plgTiendaPayment_paypalpro::NO_SECURE_DATA ) {
			?>
			<p><?php echo JText::_( 'PLG_TIENDA_PAYMENT_PAYPALPRO_MESSAGE_SECURITY_FAILED' );?></p>
			<?php
		}
	?>
	<iframe src="https://payflowlink.paypal.com?
<?php echo empty( $vars->mode ) ? '' : 'MODE='.$vars->mode.'&' ; ?>SECURETOKENID=<?php echo $vars->security['SECURETOKENID']; ?>&SECURETOKEN=<?php echo $vars->security['SECURETOKEN']; ?>" scrolling="no" width="490px" height="565px"></iframe>
</div>