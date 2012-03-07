<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
JHTML::_('script', 'tienda_orders.js', 'media/com_tienda/js/');
$form = @$this->form;
$row = @$this->row;
JFilterOutput::objectHTMLSafe( $row );
?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm">

<table>
<tr>
	<td style="width: 70%; vertical-align: top;">

        <!-- Start Products in Order section -->
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th style="text-align: left;">
                       <?php echo JText::_( "Products in Order" ); ?>
                    </th>
                    <th style="text-align: center; width: 20%;" >
                       <?php echo TiendaUrl::popup( "index.php?option=com_tienda&controller=orders&task=selectproducts&tmpl=component", JText::_( "Add Products to Order" ) ); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="2">
	                <div id="order_products_div">
	                    <?php include("orderproducts.php"); ?>
	                </div>                
                </td>
            </tr>
            </tbody>
        </table>
        <!-- End Products in Order section -->
	    
	    <!-- Start Addresses section -->
	    <table style="clear: both; width: 100%;">
	        <tr>
	            <td style="width: 50%; vertical-align: top;">
	            
                    <?php include("form_address_billing.php"); ?>
	                <div id="billingSelectedAddressDiv" style="padding-left: 5px;"></div>
	                
	            </td>
	            <td style="width: 50%; vertical-align: top;">
	            
	                <?php include("form_address_shipping.php"); ?>	                
	                <div id="shippingSelectedAddressDiv" style="padding-left: 5px;"></div>
	            </td>
	        </tr>
	    </table>
	    <!-- End Addresses section -->
	
	</td>
	<td style="width: 30%; vertical-align: top;">

        <!-- Start General information section -->
        <table class="adminlist">
        <thead>
           <tr>
               <th colspan="2" style="text-align: left;"><?php echo JText::_( "General Information" ); ?></th>
           </tr>
        </thead>
        <tbody>
            <tr>
                <th style="width: 100px;" class="key">
                     <?php echo JText::_( 'Order Currency' ); ?>:
                </th>
                <td>
                    <?php echo TiendaSelect::currency( @$row->order_currency_id, 'order_currency_id', '', 'order_currency_id', false ); ?>          
                </td>
            </tr>
            <tr>
                <th style="width: 100px;" class="key">
                     <?php echo JText::_( 'Customer Information' ); ?>:
                </th>
                <td>
                    <?php echo $row->userinfo->name .' [ '.$row->user_id.' ]'; ?>
                    <br/>
                    &nbsp;&bull;&nbsp;&nbsp;<?php echo $row->userinfo->email .' [ '.$row->userinfo->username.' ]'; ?>
                </td>
            </tr>            
            <tr>
                <th style="width: 100px;" class="key">
                     <?php echo JText::_("COM_TIENDA_EMAIL"); ?>:
                </th>
                <td>
                    <input type="text" name="user_email"
                            id="user_email" value="<?php echo @$row->userinfo->email; ?>"
                            size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <?php echo JText::_( "Email Order Confirmation to User" ); ?>
                    <input id="emailorderconfirmation" name="emailorderconfirmation" type="checkbox" checked="checked"/>
                </td>
            </tr>
        </tbody>
        </table>
        <!-- End General information section -->

        <!-- Start Shipping and Payment methods section -->
		<table class="adminlist">
		<thead>
		    <tr>
		        <th colspan="4" style="text-align: left;"><?php echo JText::_( "Shipping Method" ); ?></th>
		    </tr>
		</thead>
		<tbody>
		    <tr>
		        <th style="width: 100px;" class="key">
		            <?php echo JText::_( 'Select' ); ?>:
		        </th>
		        <td>
			        <?php $attribs = array( 'class' => 'inputbox', 'size' => '1', 'onchange' => 'tiendaGetOrderTotals();' ); ?>
		            <?php echo TiendaSelect::shippingmethod( 0, 'shipping_method_id', $attribs, 'shipping_method_id', true ); ?>
		        </td>
		    </tr>
        </tbody>
        </table>
        <!-- End Shipping and Payment methods section -->

	
	    <!-- Start Order totals section -->
	    <div id="order_totals_div">
	        <?php include("ordertotals.php"); ?>
	    </div>
	    <!-- End Order totals section -->

        <!-- Start Order History section -->
        <table class="adminlist">
        <thead>
            <tr>
                <th style="text-align: left;"><?php echo JText::_( "Order Comment" ); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <textarea id="order_history_comments" name="order_history_comments" style="width: 100%;" rows="5"></textarea>
                </td>
            </tr>
        </tbody>
        </table>
        <!-- End Order History section -->
	    
	</td>
</tr>
</table>

    <?php // TODO Could this go up top? Or must it be at the bottom of the form? ?>
	<script language="javascript">
		tiendaSelectDefaultAddresses();
	</script>									
	
	<input type="hidden" name="id" value="<?php echo @$row->id; ?>" />
	<input type="hidden" name="boxchecked" value="0" /> 
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="user_id" id="user_id" value="<?php echo @$row->user_id; ?>" />

</form>
