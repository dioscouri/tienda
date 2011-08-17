<?php defined('_JEXEC') or die('Restricted access');?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');?>
<?php $config = TiendaConfig::getInstance();?>
<?php
switch($this->form_prefix)
{
case 'shipping_':
$address_type = '2';
break;
default:
case 'billing_':
$address_type = '1';
break;
}
?>

<fieldset>
	<table class="address_form" style="clear: both;" >
		<tbody>
			<input type="hidden" value="<?php echo JText::_('TEMPORARY');?>" name="<?php echo $this->form_prefix;?>address_name" id="<?php echo $this->form_prefix;?>address_name" />
			<?php if( $config->get('show_field_title', '3') == '3' || $config->get('show_field_title', '3') == $address_type ) :?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('TITLE');?>
				</th>
				<td>
				<input name="<?php echo $this->form_prefix;?>title" id="<?php echo $this->form_prefix;?>title"
				type="text" size="35" maxlength="250" />
				&nbsp;
				<?php if($config->get('validate_field_title', '3') == '3' || $config->get('validate_field_name', '3') == $address_type ): ?>
				*
				<?php endif;?>
				</td>
			</tr>
			<?php endif;?>
			<?php if( $config->get('show_field_name', '3') == '3' || $config->get('show_field_name', '3') == $address_type ) :?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('FIRST NAME');?>
				</th>
				<td>
				<input name="<?php echo $this->form_prefix;?>first_name" id="<?php echo $this->form_prefix;?>first_name"
				type="text" size="35" maxlength="250" />
				&nbsp;
				<?php if($config->get('validate_field_name', '3') == '3' || $config->get('validate_field_name', '3') == $address_type ): ?>
				*
				<?php endif;?>
				</td>
			</tr>
			<?php endif;?>
			<?php if( $config->get('show_field_middle', '3') == '3' || $config->get('show_field_middle', '3') == $address_type ) :?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('MIDDLE NAME');?>
				</th>
				<td>
				<input type="text" name="<?php echo $this->form_prefix;?>middle_name"
				id="<?php echo $this->form_prefix;?>middle_name" size="25" maxlength="250" />
				&nbsp;
				<?php if($config->get('validate_field_middle', '3') == '3' || $config->get('validate_field_middle', '3') == $address_type ): ?>
				*
				<?php endif;?>
				</td>
			</tr>
			<?php endif;?>
			<?php if( $config->get('show_field_last', '3') == '3' || $config->get('show_field_last', '3') == $address_type ) :?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('LAST NAME');?>
				</th>
				<td>
				<input type="text" name="<?php echo $this->form_prefix;?>last_name"
				id="<?php echo $this->form_prefix;?>last_name" size="45" maxlength="250" />
				&nbsp;
				<?php if($config->get('validate_field_last', '3') == '3' || $config->get('validate_field_last', '3') == $address_type ): ?>
				*
				<?php endif;?>
				</td>
			</tr>
			<?php endif;?>
			<?php if( $config->get('show_field_company', '3') == '3' || $config->get('show_field_company', '3') == $address_type ) :?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('COMPANY NAME');?>
				</th>
				<td>
				<input type="text" name="<?php echo $this->form_prefix;?>company" id="<?php echo $this->form_prefix;?>company"
				size="48" maxlength="250" />
				&nbsp;
				<?php if($config->get('validate_field_company', '3') == '3' || $config->get('validate_field_company', '3') == $address_type ): ?>
				*
				<?php endif;?>
				</td>
			</tr>
			<?php endif;?>
			<?php if( $config->get('show_field_address1', '3') == '3' || $config->get('show_field_address1', '3') == $address_type ) :?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('ADDRESS LINE 1');?>
				</th>
				<td>
				<input type="text" name="<?php echo $this->form_prefix;?>address_1"
				id="<?php echo $this->form_prefix;?>address_1" size="48" maxlength="250" />
				&nbsp;
				<?php if($config->get('validate_field_address1', '3') == '3' || $config->get('validate_field_address1', '3') == $address_type ): ?>
				*
				<?php endif;?>
				</td>
			</tr>
			<?php endif;?>
			<?php if( $config->get('show_field_address2', '3') == '3' || $config->get('show_field_address2', '3') == $address_type ) :?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('ADDRESS LINE 2');?>
				</th>
				<td>
				<input type="text" name="<?php echo $this->form_prefix;?>address_2"
				id="<?php echo $this->form_prefix;?>address_2" size="48" maxlength="250" />
				&nbsp;
				<?php if($config->get('validate_field_address2', '3') == '3' || $config->get('validate_field_address2', '3') == $address_type ): ?>
				*
				<?php endif;?>
				</td>
			</tr>
			<?php endif;?>
			<?php if( $config->get('show_field_city', '3') == '3' || $config->get('show_field_city', '3') == $address_type ) :?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('CITY');?>
				</th>
				<td>
				<input type="text" name="<?php echo $this->form_prefix;?>city"
				id="<?php echo $this->form_prefix;?>city" size="48" maxlength="250" />
				&nbsp;
				<?php if($config->get('validate_field_city', '3') == '3' || $config->get('validate_field_city', '3') == $address_type ): ?>
				*
				<?php endif;?>
				</td>
			</tr>
			<?php endif;?>
			<?php if( $config->get('show_field_country', '3') == '3' || $config->get('show_field_country', '3') == $address_type ) :?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('COUNTRY');?>
				</th>
				<td>
				<?php
				$url = "index.php?option=com_tienda&format=raw&controller=pos&task=getzones&prefix={$this->form_prefix}&country_id=";
				$attribs = array('class' => 'inputbox',
				'size' => '1',
				'onchange' => 'tiendaDoTask( \'' . $url . '\'+document.getElementById(\'' . $this->form_prefix . 'country_id\').value, \'' . $this->form_prefix . 'zones_wrapper\', \'\');');
				echo TiendaSelect::country($this->default_country_id, $this->form_prefix . 'country_id', $attribs, $this->form_prefix . 'country_id', false, true);
				?>&nbsp;
				<?php if($config->get('validate_field_country', '3') == '3' || $config->get('validate_field_country', '3') == $address_type ): ?>
				*
				<?php endif;?>
				</td>
			</tr>
			<?php endif;?>
			<?php if( $config->get('show_field_zone', '3') == '3' || $config->get('show_field_zone', '3') == $address_type ) :?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('Zone');?>
				</th>
				<td>
				<div id="<?php echo $this->form_prefix;?>zones_wrapper">
					<?php
					if(!empty($this->zones))
					{
						echo $this->zones;
					}
					else
					{
						echo JText::_("SELECT COUNTRY FIRST");
					}
					?>
				</div>&nbsp;
				<?php if($config->get('validate_field_zone', '3') == '3' || $config->get('validate_field_zone', '3') == $address_type ): ?>
				*
				<?php endif;?>
				</td>
			</tr>
			<?php endif;?>
			<?php if( $config->get('show_field_zip', '3') == '3' || $config->get('show_field_zip', '3') == $address_type ) :?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('POSTAL CODE');?>
				</th>
				<td>
				<input type="text" name="<?php echo $this->form_prefix;?>postal_code"
				id="<?php echo $this->form_prefix;?>postal_code" size="25" maxlength="250"
				<?php if (!empty($this->showShipping)&& $this->forShipping ) { ?>onchange="tiendaGetShippingRates( 'onCheckoutShipping_wrapper', this.form );" <?php }?> />&nbsp;
				<?php if($config->get('validate_field_zip', '3') == '3' || $config->get('validate_field_zip', '3') == $address_type ): ?>
				*
				<?php endif;?>
				</td>
			</tr>
			<?php endif;?>
			<?php if( $config->get('show_field_phone', '3') == '3' || $config->get('show_field_phone', '3') == $address_type ) :?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('PHONE');?>
				</th>
				<td>
				<input name="<?php echo $this->form_prefix;?>phone_1" id="<?php echo $this->form_prefix;?>phone_1"
				type="text" size="25" maxlength="250" />&nbsp;
				<?php if($config->get('validate_field_phone', '3') == '3' || $config->get('validate_field_phone', '3') == $address_type ): ?>
				*
				<?php endif;?>
				</td>
			</tr>
			<?php endif;?>
				<?php	$data = new JObject();
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('onAfterDisplayAddressDetails', array($data,
				$this->form_prefix));?>

</tbody>
</table>
</fieldset>
