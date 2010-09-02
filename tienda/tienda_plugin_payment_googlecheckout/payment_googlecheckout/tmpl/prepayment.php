<?php defined('_JEXEC') or die('Restricted access'); ?>
<div id="payment_paypal">
                    <div class="prepayment_message">
                        <?php echo JText::_( "Tienda Google Check Out Preparation Message" ); ?>
                    </div>
                    <div class="prepayment_action">
                   	     <?php echo @$vars->cart->CheckoutButtonCode("SMALL"); ?>
                   	     <div style="float: left; padding: 10px;"><?php echo "<b>".JText::_( "Checkout Amount").":</b> ".TiendaHelperBase::currency( @$vars->orderpayment_amount ); ?></div>
                       <div style="clear: both;"></div>
                    </div>
                </div>
