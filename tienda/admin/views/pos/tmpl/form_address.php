<?php 
	defined('_JEXEC') or die('Restricted access');
	JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
	JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
	
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
	Tienda::load( 'TiendaHelperAddresses', 'helpers.addresses' );
	$elements  = TiendaHelperAddresses::getAddressElementsData( $address_type );
	
	$session = JFactory::getSession();
	$user_type = $session->get( 'user_type', '', 'tienda_pos' );
	$guest = true;
	if( $user_type == "existing" || $user_type == "new" )
		$guest = false;
?>

<fieldset>
	<table class="address_form" style="clear: both;" >
		<tbody>
			<?php if( $elements['address_name'][0] ) :
				if( $guest ) : ?>
			<input type="hidden" value="<?php echo JText::_('COM_TIENDA_TEMPORARY');?>" name="<?php echo $this->form_prefix;?>address_name" id="<?php echo $this->form_prefix;?>address_name" />
				<?php else: ?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
					<label class="key" for="<?php echo $this->form_prefix; ?>address_name"><?php echo JText::_( 'COM_TIENDA_ADDRESS_TITLE' ); ?>
					<?php if( !$this->guest && $elements['address_name'][1] ): ?>
						<?php echo TiendaGrid::required(); ?>
					<?php endif;?>
				</label>
				</th>
				<td>
					<input name="<?php echo $this->form_prefix; ?>address_name" id="<?php echo $this->form_prefix; ?>address_name" class="inputbox" type="text" maxlength="250" />
				</td>
			</tr>
			<?php 
					endif;
				endif;
				if( $elements['title'][0] ) :
			?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php
					echo JText::_('COM_TIENDA_TITLE');
					if( $elements['title'][1] ):
						echo TiendaGrid::required();
					endif;
				?>
				</th>
				<td>
				<input name="<?php echo $this->form_prefix;?>title" id="<?php echo $this->form_prefix;?>title" type="text" size="35" maxlength="250" />
				</td>
			</tr>
			<?php
				endif;
				if( $elements['name'][0] ) :
			?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php 
					echo JText::_('COM_TIENDA_FIRST_NAME');
					if( $elements['name'][1] ):
						echo TiendaGrid::required();
					endif;
				?>
				</th>
				<td>
				<input name="<?php echo $this->form_prefix;?>first_name" id="<?php echo $this->form_prefix;?>first_name" type="text" size="35" maxlength="250" />
				</td>
			</tr>
			<?php
				endif;
				if( $elements['middle'][0] ) :
			?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php 
					echo JText::_('COM_TIENDA_MIDDLE_NAME');
					if( $elements['middle'][1] ):
						echo TiendaGrid::required();
					endif;
				?>
				</th>
				<td>
				<input type="text" name="<?php echo $this->form_prefix;?>middle_name" id="<?php echo $this->form_prefix;?>middle_name" size="25" maxlength="250" />
				</td>
			</tr>
			<?php
				endif;
				if( $elements['last'][0] ) :
			?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php 
					echo JText::_('COM_TIENDA_LAST_NAME');
					if( $elements['last'][1] ):
						echo TiendaGrid::required();
					endif;
				?>
				</th>
				<td>
				<input type="text" name="<?php echo $this->form_prefix;?>last_name" id="<?php echo $this->form_prefix;?>last_name" size="45" maxlength="250" />
				</td>
			</tr>
			<?php
				endif;
				if( $elements['company'][0] ) :
			?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php 
					echo JText::_('COM_TIENDA_COMPANY');
					if( $elements['company'][1] ):
						echo TiendaGrid::required();
					endif;
				?>
				</th>
				<td>
				<input type="text" name="<?php echo $this->form_prefix;?>company" id="<?php echo $this->form_prefix;?>company" size="48" maxlength="250" />
				</td>
			</tr>
			<?php
				endif;
				if( $elements['address1'][0] ) :
			?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php 
					echo JText::_('COM_TIENDA_ADDRESS_LINE_1');
					if( $elements['address1'][1] ):
						echo TiendaGrid::required();
					endif;
				?>
				</th>
				<td>
				<input type="text" name="<?php echo $this->form_prefix;?>address_1" id="<?php echo $this->form_prefix;?>address_1" size="48" maxlength="250" />
				</td>
			</tr>
			<?php
				endif;
				if( $elements['address2'][0] ) :
			?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php
					echo JText::_('COM_TIENDA_ADDRESS_LINE_2');
					if( $elements['address2'][1] ):
						echo TiendaGrid::required();
					endif;
				?>
				</th>
				<td>
				<input type="text" name="<?php echo $this->form_prefix;?>address_2" id="<?php echo $this->form_prefix;?>address_2" size="48" maxlength="250" />
				</td>
			</tr>
			<?php
				endif;
				if( $elements['city'][0] ) :
			?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php
					echo JText::_('COM_TIENDA_CITY');
					if( $elements['city'][1] ):
						echo TiendaGrid::required();
					endif;
				?>
				</th>
				<td>
				<input type="text" name="<?php echo $this->form_prefix;?>city" id="<?php echo $this->form_prefix;?>city" size="48" maxlength="250" />
				</td>
			</tr>
			<?php
				endif;
				if( $elements['country'][0] ) :
			?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php
					echo JText::_('COM_TIENDA_COUNTRY');
					if( $elements['country'][1] ):
						echo TiendaGrid::required();
					endif;
				?>
				</th>
				<td>
				<?php
				$url = "index.php?option=com_tienda&format=raw&controller=pos&task=getzones&prefix={$this->form_prefix}&country_id=";
				$attribs = array('class' => 'inputbox',
				'size' => '1',
				'onchange' => 'tiendaDoTask( \'' . $url . '\'+document.getElementById(\'' . $this->form_prefix . 'country_id\').value, \'' . $this->form_prefix . 'zones_wrapper\', \'\');');
				echo TiendaSelect::country($this->default_country_id, $this->form_prefix . 'country_id', $attribs, $this->form_prefix . 'country_id', false, true);
				?>
				</td>
			</tr>
			<?php
				endif;
				if( $elements['zone'][0] ) :
			?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php
					echo JText::_('COM_TIENDA_ZONE');
					if( $elements['zone'][1] ):
						echo TiendaGrid::required();
					endif;
				?>
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
						echo JText::_("COM_TIENDA_SELECT_COUNTRY_FIRST");
					}
					?>
				</div>
				</td>
			</tr>
			<?php
				endif;
				if( $elements['zip'][0] ) :
			?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php
					echo JText::_('COM_TIENDA_POSTAL_CODE');
					if( $elements['zip'][1] ):
						echo TiendaGrid::required();
					endif;
				?>
				</th>
				<td>
				<input type="text" name="<?php echo $this->form_prefix;?>postal_code" id="<?php echo $this->form_prefix;?>postal_code" size="25" maxlength="250"
				<?php if (!empty($this->showShipping)&& $this->forShipping ) { ?>onchange="tiendaGetShippingRates( 'onCheckoutShipping_wrapper', this.form );" <?php }?> />
				</td>
			</tr>
			<?php
				endif;
				if( $elements['phone'][0] ) :
			?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php
					echo JText::_('COM_TIENDA_PHONE');
					if( $elements['phone'][1] ):
						echo TiendaGrid::required();
					endif;
				?>
				</th>
				<td>
				<input name="<?php echo $this->form_prefix;?>phone_1" id="<?php echo $this->form_prefix;?>phone_1" type="text" size="25" maxlength="250" />
				</td>
			</tr>
			<?php
				endif;
				if( $elements['tax_number'][0] ) :
			?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php
					echo JText::_('COM_TIENDA_CO_TAX_NUMBER');
					if( $elements['tax_number'][1] ):
						echo TiendaGrid::required();
					endif;
				?>
				</th>
				<td>
				<input name="<?php echo $this->form_prefix;?>tax_number" id="<?php echo $this->form_prefix;?>tax_number" type="text" size="25" maxlength="250" />
				</td>
			</tr>
			<?php
				endif;
				
				$data = new JObject();
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('onAfterDisplayAddressDetails', array($data, $this->form_prefix));
		?>
</tbody>
</table>
</fieldset>
