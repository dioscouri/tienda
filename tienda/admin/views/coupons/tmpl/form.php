<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row;
JFilterOutput::objectHTMLSafe( $row );
Tienda::load( 'TiendaHelperCoupon', 'helpers.coupon' );
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
JHTML::_('behavior.tooltip'); 
?>
<script type="text/javascript">
function showProductList()
{
	var value = getSelectedRadio('adminForm', 'coupon_type');
	if(value == '1')
	{
		$('per_product').style.display = '';
	}
	else
	{
		$('per_product').style.display = 'none';
	}
}

function shippingPerOrder()
{
	var value = getSelectedValue('adminForm', 'coupon_group');
	if(value == 'shipping')
	{
		$('coupon_type1').style.display = 'none';
		$('couponForm').getElement('label[for=coupon_type1]').style.display = 'none';
	}
	else
	{
		$('coupon_type1').style.display = '';
		$('couponForm').getElement('label[for=coupon_type1]').style.display = '';
	}
}
</script>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" id="couponForm" name="adminForm" enctype="multipart/form-data" >

	<fieldset>
		<legend><?php echo JText::_('Form'); ?></legend>
			<table class="admintable">
				<tr>
					<td style="width: 125px; text-align: right;" class="key">
						<?php echo JText::_( 'Name' ); ?>:
					</td>
					<td>
						<input type="text" name="coupon_name" id="coupon_name" value="<?php echo @$row->coupon_name; ?>" size="48" maxlength="250" />
					</td>
				</tr>
                <tr>
                    <td class="key hasTip" title="<?php echo JText::_("Coupon Code").'::'.JText::_( "Coupon Code Tip" ); ?>" style="width: 125px; text-align: right;">
                        <?php echo JText::_( 'Code' ); ?>:
                    </td>
                    <td>
                        <input type="text" name="coupon_code" value="<?php echo @$row->coupon_code; ?>" size="48" maxlength="250" />
                    </td>
                </tr>
				<tr>
					<td style="width: 125px; text-align: right;" class="key">
						<?php echo JText::_( 'Enabled' ); ?>:
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', 'coupon_enabled', '', @$row->coupon_enabled ); ?>
					</td>
				</tr>
                <tr>
                    <td class="key hasTip" title="<?php echo JText::_("Coupon Value").'::'.JText::_( "Coupon Value Tip" ); ?>" style="width: 125px; text-align: right;">
                        <?php echo JText::_( 'Value' ); ?>:
                    </td>
                    <td>
                        <input type="text" name="coupon_value" value="<?php echo @$row->coupon_value; ?>" size="10" maxlength="250" />
                    </td>
                </tr>
                <tr>
                    <td class="key hasTip" title="<?php echo JText::_("Coupon Currency").'::'.JText::_( "Coupon Currency Tip" ); ?>" style="width: 125px; text-align: right;">
                        <?php echo JText::_( 'Currency' ); ?>:
                    </td>
                    <td>
                        <?php echo TiendaSelect::currency( @$row->currency_id, 'currency_id' ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_( 'Value Type' ); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::_('select.booleanlist', 'coupon_value_type', '', @$row->coupon_value_type, 'Percentage', 'Flat Rate' ); ?>
                    </td>
                </tr>
                <tr>
                    <td class="key hasTip" title="<?php echo JText::_("Max Uses").'::'.JText::_( "Max Uses Tip" ); ?>" style="width: 125px; text-align: right;">
                        <?php echo JText::_( 'Max Uses' ); ?>:
                    </td>
                    <td>
                        <input type="text" name="coupon_max_uses" value="<?php echo @$row->coupon_max_uses; ?>" size="10" maxlength="250" />
                    </td>
                </tr>
                <tr>
                    <td class="key hasTip" title="<?php echo JText::_("Max Uses Per User").'::'.JText::_( "Max Uses Per User Tip" ); ?>" style="width: 125px; text-align: right;">
                        <?php echo JText::_( 'Max Uses Per User' ); ?>:
                    </td>
                    <td>
                        <input type="text" name="coupon_max_uses_per_user" value="<?php echo @$row->coupon_max_uses_per_user; ?>" size="10" maxlength="250" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_( 'Valid From' ); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::calendar( @$row->start_date, "start_date", "start_date", '%Y-%m-%d %H:%M:%S' ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_( 'Expires On' ); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::calendar( @$row->expiration_date, "expiration_date", "expiration_date", '%Y-%m-%d %H:%M:%S' ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_( 'Discount Applied' ); ?>:
                    </td>
                    <td>                    
                        <?php $attribs = array(); ?>
                        <?php $attribs['onclick'] = 'showProductList();'; ?>
                        <?php echo TiendaSelect::coupontype(@$row->coupon_type, 'coupon_type', $attribs, 'coupon_type'); ?>
                    </td>
                </tr>
                <tr <?php if (empty($row->coupon_type)) { echo 'style="display: none;"'; } ?> id="per_product">
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_( 'Select Products' ); ?>:
                    </td>
                    <td>
                    	<?php if(@$row->coupon_id){?>
                        <?php $select_url = "index.php?option=com_tienda&controller=coupons&task=selectproducts&id=".$row->coupon_id."&tmpl=component&hidemainmenu=1"; ?>
                    	<?php echo TiendaUrl::popup( $select_url, JText::_( "Select Products") ); ?>
                    	<?php } else
                    	{
                    		?>
                    		<div class="note">
                    		<?php echo JText::_( "Click the Apply button to add products to this coupon" ); ?>
                			</div>
                    		<?php 
                    	}?>
                    </td>
                </tr>                                
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_( 'Discount Applies To' ); ?>:
                    </td>
                    <td>
                    	<?php $attribs = array(); ?>
                        <?php $attribs['onchange'] = 'shippingPerOrder();'; ?>
                        <?php echo TiendaSelect::coupongroup( @$row->coupon_group, 'coupon_group', $attribs ); ?>                                               
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_( 'Type' ); ?>:
                    </td>
                    <td>
                        <?php echo TiendaSelect::booleanlist( 'coupon_automatic', '', @$row->coupon_automatic, 'Automatic', 'User Submitted' );?>
                    </td>
                </tr>                
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_( 'Description' ); ?>:
                    </td>
                    <td>
                        <textarea name="coupon_description" rows="10" cols="55"><?php echo @$row->coupon_description; ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_( 'Parameters' ); ?>:
                    </td>
                    <td>
                        <textarea name="coupon_params" rows="5" cols="55"><?php echo @$row->coupon_params; ?></textarea>
                    </td>
                </tr>

			</table>
			<input type="hidden" name="id" value="<?php echo @$row->coupon_id; ?>" />
			<input type="hidden" name="task" value="" />
	</fieldset>
</form>
<script type="text/javascript">
	window.addEvent('domready', function(){
	  showProductList();
	  shippingPerOrder();
	});
</script>