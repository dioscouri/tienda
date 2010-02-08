<?php 
    defined('_JEXEC') or die('Restricted access'); 
	JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); 
	JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); 
	JHTML::_('script', 'tienda_checkout.js', 'media/com_tienda/js/');
	$form = @$this->form; 
	$row = @$this->row; 
?>
<div class='componentheading'>
    <span><?php echo JText::_( "Select Addresses and Shipping Method" ); ?></span>
</div>

    <?php // echo TiendaMenu::display(); ?>
    
<div id='onCheckout_wrapper'>

    <form action="<?php echo JRoute::_( @$form['action'] ); ?>" method="post" name="adminForm" enctype="multipart/form-data">
        
        <!--    ORDER SUMMARY   -->
        <h3><?php echo JText::_("Order Summary") ?></h3>
        <div id='onCheckoutCart_wrapper'> 
			<?php
                echo @$this->orderSummary;
 		    ?>
        </div>

        <h3>
            <?php echo JText::_("Select Shipping and Billing Addresses") ?>
        </h3>

        <table class="adminlist" style="clear: both;">
        <tr>
            <td colspan="2">
                <div class='note'>
	                <?php $text = JText::_( "Click Here to Manage Your Stored Addresses" )."."; ?>
	                <?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=addresses", $text, '', '', '', '', '', true );  ?>
                    <?php echo JText::_( "CHECKOUT MANAGE ADDRESSES INSTRUCTIONS" ); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="width: 50px; text-align: left;">
                <!--    SHIPPING ADDRESS   -->
	            <h4 id='onCheckoutShipping'><?php echo JText::_("Shipping Address") ?></h4>
	            <?php
	            $baseurl = "index.php?option=com_tienda&format=raw&controller=addresses&task=getAddress&address_id=";
	            $url = JRoute::_($baseurl . @$this->shipping_address->address_id);
                if (!empty($this->addresses))
                {
	                $shipattribs = array('class' => 'inputbox',    'size' => '1','onchange' => "tiendaDoTask('$baseurl'+this.options[this.selectedIndex].value, 'shippingDefaultAddress', '')");
	                echo TiendaSelect::address( JFactory::getUser()->id, @$this->shipping_address->address_id, 'shipping_address_id', 2, $shipattribs, 'shipping_address_id', false );
	               	if(count($this->addresses) == 1){
	               		echo "<input type=\"hidden\" id=\"shipping_address_id\" name=\"shipping_address_id\" value=\"" . @$this->shipping_address->address_id . "\" />";
	               	}
				}?>
	            
                <script type="text/javascript">tiendaDoTask('<?php echo $url ?>', 'shippingDefaultAddress', '');</script>
                	            
	            <p id="shippingDefaultAddress"></p>
	            
            </td>
            <td style="width: 50px; text-align: left;">
                <!--    BILLING ADDRESS   -->             
	            <h4 id='onCheckoutBilling'><?php echo JText::_("Billing Address") ?></h4>
	            <?php 
	                $url = JRoute::_($baseurl . @$this->billing_address->address_id);
	                if (!empty($this->addresses))
	                {
	                    $billattribs = array('class' => 'inputbox',    'size' => '1','onchange' => "tiendaDoTask('$baseurl'+this.options[this.selectedIndex].value, 'billingDefaultAddress', '')");
	                    echo TiendaSelect::address( JFactory::getUser()->id, @$this->billing_address->address_id, 'billing_address_id', 1, $billattribs, 'billing_address_id', false );
	                	if(count($this->addresses) == 1)
	                	{
	                			echo "<input type=\"hidden\" id=\"billing_address_id\" name=\"billing_address_id\" value=\"" . @$this->billing_address->address_id . "\" />";
	               		}
					}?>
	            <script type="text/javascript">tiendaDoTask('<?php echo $url ?>', 'billingDefaultAddress', '');</script>
	            <p id="billingDefaultAddress"></p>
            </td>
        </tr>
        </table>        

        <!-- SHIPPING METHODS -->
        <h3><?php echo JText::_("Shipping Method") ?></h3>
        <div id=shippingmethods>
	    	<?php 
	    		$attribs = array( 'class' => 'inputbox', 'size' => '1', 'onchange' => 'tiendaGetCheckoutTotals();');
		    	echo TiendaSelect::shippingmethod( $this->order->shipping_method_id, 'shipping_method_id', $attribs, 'shipping_method_id', true ); 
		    ?>	
        </div>      

        <!--    COMMENTS   -->        
        <h3><?php echo JText::_("Shipping Notes") ?></h3>
        <?php echo JText::_( "Add optional notes for shipment here" ); ?>:
        <br/>
        <textarea id="customer_note" name="customer_note" rows="5" cols="70"></textarea>
        
        <p>            
        <!--    SUBMIT   -->
        <input type="button" class="button" onclick="window.location = '<?php echo JRoute::_('index.php?option=com_tienda&view=carts'); ?>'" value="<?php echo JText::_('Return to Shopping Cart'); ?>" />
        <input type="button" class="button" onclick="tiendaFormValidation( '<?php echo @$form['validation']; ?>', 'validationmessage', 'review', document.adminForm )" value="<?php echo JText::_('Select Payment Method'); ?>" />	
		<input type="hidden" id="currency_id" name="currency_id" value="<?php echo $this->order->currency_id; ?>" />
		<input type="hidden" id="task" name="task" value="" />
        </p>
        
        <?php echo $this->form['validate']; ?>
    </form>
</div>
