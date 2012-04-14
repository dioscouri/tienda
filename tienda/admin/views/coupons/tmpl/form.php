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
		<legend><?php echo JText::_('COM_TIENDA_FORM'); ?></legend>
			<table class="admintable">
				<tr>
					<td style="width: 125px; text-align: right;" class="key">
						<?php echo JText::_('COM_TIENDA_NAME'); ?>:
					</td>
					<td>
						<input type="text" name="coupon_name" id="coupon_name" value="<?php echo @$row->coupon_name; ?>" size="48" maxlength="250" />
					</td>
				</tr>
                <tr>
                    <td class="key hasTip" title="<?php echo JText::_('COM_TIENDA_COUPON_CODE').'::'.JText::_('COM_TIENDA_COUPON_CODE_TIP'); ?>" style="width: 125px; text-align: right;">
                        <?php echo JText::_('COM_TIENDA_CODE'); ?>:
                    </td>
                    <td>
                        <input type="text" name="coupon_code" value="<?php echo @$row->coupon_code; ?>" size="48" maxlength="250" />
                    </td>
                </tr>
				<tr>
					<td style="width: 125px; text-align: right;" class="key">
						<?php echo JText::_('COM_TIENDA_ENABLED'); ?>:
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', 'coupon_enabled', '', @$row->coupon_enabled ); ?>
					</td>
				</tr>
                <tr>
                    <td class="key hasTip" title="<?php echo JText::_('COM_TIENDA_COUPON_VALUE').'::'.JText::_('COM_TIENDA_COUPON_VALUE_TIP'); ?>" style="width: 125px; text-align: right;">
                        <?php echo JText::_('COM_TIENDA_VALUE'); ?>:
                    </td>
                    <td>
                        <input type="text" name="coupon_value" value="<?php echo @$row->coupon_value; ?>" size="10" maxlength="250" />
                    </td>
                </tr>
                <tr>
                    <td class="key hasTip" title="<?php echo JText::_('COM_TIENDA_COUPON_CURRENCY').'::'.JText::_('COM_TIENDA_COUPON_CURRENCY_TIP'); ?>" style="width: 125px; text-align: right;">
                        <?php echo JText::_('COM_TIENDA_CURRENCY'); ?>:
                    </td>
                    <td>
                        <?php echo TiendaSelect::currency( @$row->currency_id, 'currency_id' ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_VALUE_TYPE'); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::_('select.booleanlist', 'coupon_value_type', '', @$row->coupon_value_type, 'Percentage', 'Flat Rate' ); ?>
                    </td>
                </tr>
                <tr>
                    <td class="key hasTip" title="<?php echo JText::_('COM_TIENDA_MAX_USES').'::'.JText::_('COM_TIENDA_MAX_USES_TIP'); ?>" style="width: 125px; text-align: right;">
                        <?php echo JText::_('COM_TIENDA_MAX_USES'); ?>:
                    </td>
                    <td>
                        <input type="text" name="coupon_max_uses" value="<?php echo @$row->coupon_max_uses; ?>" size="10" maxlength="250" />
                    </td>
                </tr>
                <tr>
                    <td class="key hasTip" title="<?php echo JText::_('COM_TIENDA_MAX_USES_PER_USER').'::'.JText::_('COM_TIENDA_MAX_USES_PER_USER_TIP'); ?>" style="width: 125px; text-align: right;">
                        <?php echo JText::_('COM_TIENDA_MAX_USES_PER_USER'); ?>:
                    </td>
                    <td>
                        <input type="text" name="coupon_max_uses_per_user" value="<?php echo @$row->coupon_max_uses_per_user; ?>" size="10" maxlength="250" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_VALID_FROM'); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::calendar( @$row->start_date, "start_date", "start_date", '%Y-%m-%d %H:%M:%S' ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_EXPIRES_ON'); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::calendar( @$row->expiration_date, "expiration_date", "expiration_date", '%Y-%m-%d %H:%M:%S' ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_DISCOUNT_APPLIED'); ?>:
                    </td>
                    <td>                    
                        <?php $attribs = array(); ?>
                        <?php $attribs['onclick'] = 'showProductList();'; ?>
                        <?php echo TiendaSelect::coupontype(@$row->coupon_type, 'coupon_type', $attribs, 'coupon_type'); ?>
                    </td>
                </tr>
                <tr <?php if (empty($row->coupon_type)) { echo 'style="display: none;"'; } ?> id="per_product">
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_SELECT_PRODUCTS'); ?>:
                    </td>
                    <td>
                    	<?php if(@$row->coupon_id){?>
                        <?php $select_url = "index.php?option=com_tienda&controller=coupons&task=selectproducts&id=".$row->coupon_id."&tmpl=component&hidemainmenu=1"; ?>
                    	<?php echo TiendaUrl::popup( $select_url, JText::_('COM_TIENDA_SELECT_PRODUCTS') ); ?>
                    	<?php } else
                    	{
                    		?>
                    		<div class="note">
                    		<?php echo JText::_('COM_TIENDA_CLICK_THE_APPLY_BUTTON_TO_ADD_PRODUCTS_TO_THIS_COUPON'); ?>
                			</div>
                    		<?php 
                    	}?>
                    </td>
                </tr>                                
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_DISCOUNT_APPLIES_TO'); ?>:
                    </td>
                    <td>
                    	<?php $attribs = array(); ?>
                        <?php $attribs['onchange'] = 'shippingPerOrder();'; ?>
                        <?php echo TiendaSelect::coupongroup( @$row->coupon_group, 'coupon_group', $attribs ); ?>                                               
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_TYPE'); ?>:
                    </td>
                    <td>
                        <?php echo TiendaSelect::booleanlist( 'coupon_automatic', '', @$row->coupon_automatic, 'Automatic', 'User Submitted' );?>
                    </td>
                </tr>                
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_DESCRIPTION'); ?>:
                    </td>
                    <td>
                        <textarea name="coupon_description" rows="10" cols="55"><?php echo @$row->coupon_description; ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_PARAMETERS'); ?>:
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