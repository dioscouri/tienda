<?php defined('_JEXEC') or die('Restricted access'); ?>
<div id="payment_paypal">
                    <div class="prepayment_message">
                        <?php echo JText::_('COM_TIENDA_TIENDA_GOOGLE_CHECKOUT_PREPARATION_MESSAGE'); ?>
                    </div>
                    <div class="prepayment_action">
                   	     <?php echo @$vars->cart->CheckoutButtonCode("SMALL"); ?>
                   	     <div style="float: left; padding: 10px;"><?php echo "<b>".JText::_('COM_TIENDA_CHECKOUT_AMOUNT').":</b> ".TiendaHelperBase::currency( @$vars->orderpayment_amount ); ?></div>
                       <div style="clear: both;"></div>
                    </div>
                </div>
