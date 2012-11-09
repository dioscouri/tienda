<?php defined('_JEXEC') or die('Restricted access');

	JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
	JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
	Tienda::load( 'TiendaGrid', 'library.grid' );
	Tienda::load( 'TiendaHelperAddresses', 'helpers.addresses' );
	$config = Tienda::getInstance();
	$one_page =$config->get('one_page_checkout', 0);
	$guest_enabled = $config->get('guest_checkout_enabled', 0);
	
	switch($this->form_prefix)
	{
		case 'shipping_input_':
			$address_type = '2';
			break;
		default:
		case 'billing_input_':
			$address_type = '1';
			break;
	}
	$elements  = TiendaHelperAddresses::getAddressElementsData( $address_type );
	$js_strings = array( 'COM_TIENDA_UPDATING_SHIPPING_RATES', 'COM_TIENDA_UPDATING_CART', 'COM_TIENDA_UPDATING_ADDRESS',  'COM_TIENDA_UPDATING_PAYMENT_METHODS' );
	TiendaHelperAddresses::addJsTranslationStrings( $js_strings );
?>

<div id="<?php echo $this->form_prefix; ?>addressForm" class="address_form">
	<?php
		if( $elements['address_name'][0] ) 
		{
			if( $this->guest && !$one_page )
			{
				echo '<input value="'.JText::_('COM_TIENDA_TEMPORARY').'" name="'.$this->form_prefix.'address_name" id="'.$this->form_prefix.'address_name" type="hidden" />';
			}
			else
			{
				?>
	<label class="key" for="<?php echo $this->form_prefix; ?>address_name"><?php echo JText::_('COM_TIENDA_ADDRESS_TITLE'); ?>
		<?php if( $elements['address_name'][1] ): ?>
			<?php echo TiendaGrid::required(); ?>
		<?php endif;?>
		<span class="block"><?php echo JText::_('COM_TIENDA_ADDRESS_TITLE_FOR_YOUR_REFERENCE'); ?>
		
		</span>
	</label>
	<input name="<?php echo $this->form_prefix; ?>address_name" id="<?php echo $this->form_prefix; ?>address_name" class="inputbox" type="text" maxlength="250" data-required="<?php echo $elements['address_name'][1] ? 'true' : false; ?>" />&nbsp;
				<?php
			}
		}
		?>

	<div class="floatbox">
	<?php if( $elements['title'][0] ) :?>
		<div>
			<label class="key" for="<?php echo $this->form_prefix; ?>title">
				<?php 
					echo JText::_('COM_TIENDA_TITLE');
					if( $elements['title'][1] ):
						echo TiendaGrid::required();
					endif;
				?>
			</label>
			<input name="<?php echo $this->form_prefix; ?>title"	id="<?php echo $this->form_prefix; ?>title" class="inputbox"	type="text" maxlength="250" data-required="<?php echo $elements['title'][1] ? 'true' : false; ?>" />
		</div>
		<?php endif; ?>


	<?php if( $elements['name'][0] ) :?>
		<div>
			<label class="key" for="<?php echo $this->form_prefix; ?>first_name">
				<?php echo JText::_('COM_TIENDA_FIRST_NAME'); ?>
				<?php if( $elements['name'][1] ): ?>
					<?php echo TiendaGrid::required(); ?>
				<?php endif;?>			
			</label>
			<input name="<?php echo $this->form_prefix; ?>first_name"	id="<?php echo $this->form_prefix; ?>first_name" class="inputbox"	type="text" maxlength="250" data-required="<?php echo $elements['name'][1] ? 'true' : false; ?>" />
		</div>
		<?php endif; ?>

		<?php if( $elements['middle'][0] ) :?>
		<div>
			<label class="key" for="<?php echo $this->form_prefix; ?>middle_name">
				<?php echo JText::_('COM_TIENDA_MIDDLE_NAME'); ?> 
				<?php if( $elements['middle'][1] ): ?>
					<?php echo TiendaGrid::required(); ?>
				<?php endif;?>
			</label>
			<input type="text" name="<?php echo $this->form_prefix; ?>middle_name" id="<?php echo $this->form_prefix; ?>middle_name" class="inputbox"	maxlength="250" data-required="<?php echo $elements['middle'][1] ? 'true' : false; ?>" />
		</div>
		<?php endif; ?>
	</div>

	<?php if( $elements['last'][0] ) :?>
	<div>
		<label class="key" for="<?php echo $this->form_prefix; ?>last_name">
			<?php echo JText::_('COM_TIENDA_LAST_NAME'); ?>
			<?php if( $elements['last'][1] ): ?>
				<?php echo TiendaGrid::required(); ?>
			<?php endif;?>
		</label>
		<input type="text" name="<?php echo $this->form_prefix; ?>last_name"	id="<?php echo $this->form_prefix; ?>last_name" class="inputbox" size="45" maxlength="250" data-required="<?php echo $elements['last'][1] ? 'true' : false; ?>" />
	</div>
	<?php endif; ?>
	
<div class="floatbox">
	<?php if( $elements['company'][0] ) :?>
		<div>
			<label class="key" for="<?php echo $this->form_prefix; ?>company">
				<?php echo JText::_('COM_TIENDA_COMPANY'); ?>
				<?php if($elements['company'][1] ): ?>
					<?php echo TiendaGrid::required(); ?>
				<?php endif;?>
			</label>
			<input type="text" name="<?php echo $this->form_prefix; ?>company" id="<?php echo $this->form_prefix; ?>company" class="inputbox" size="48" maxlength="250" data-required="<?php echo $elements['company'][1] ? 'true' : false; ?>" />
		</div>
		<?php endif; ?>
		<?php if( $elements['tax_number'][0] ) :?>
		<div>
			<label class="key" for="<?php echo $this->form_prefix; ?>tax_number">
				<?php echo JText::_('COM_TIENDA_CO_TAX_NUMBER'); ?>
				<?php if( $elements['tax_number'][1] ): ?>
					<?php echo TiendaGrid::required(); ?>
				<?php endif;?>
			</label>
			<input type="text" name="<?php echo $this->form_prefix; ?>tax_number" id="<?php echo $this->form_prefix; ?>tax_number" class="inputbox" size="48" maxlength="250" data-required="<?php echo $elements['tax_number'][1] ? 'true' : false; ?>" />
		</div>
		<?php endif; ?>
	</div>

	<?php if( $elements['address1'][0] ) :?>
	<div>
		<label class="key" for="<?php echo $this->form_prefix; ?>address_1">
			<?php echo JText::_('COM_TIENDA_ADDRESS_LINE_1'); ?>
			<?php if( $elements['address1'][1] ): ?>
				<?php echo TiendaGrid::required(); ?>
			<?php endif;?>
		</label>
		<input type="text"	name="<?php echo $this->form_prefix; ?>address_1" id="<?php echo $this->form_prefix; ?>address_1" class="inputbox" size="48" maxlength="250" data-required="<?php echo $elements['address1'][1] ? 'true' : false; ?>" />
	</div>
	<?php endif; ?>
	
	<?php if( $elements['address2'][0] ) :?>
	<div>
		<label class="key" for="<?php echo $this->form_prefix; ?>address_2">
			<?php echo JText::_('COM_TIENDA_ADDRESS_LINE_2'); ?>
			<?php if( $elements['address2'][1] ): ?>
				<?php echo TiendaGrid::required(); ?>
			<?php endif;?>
		</label>
		<input type="text" name="<?php echo $this->form_prefix; ?>address_2" id="<?php echo $this->form_prefix; ?>address_2" class="inputbox" size="48" maxlength="250" data-required="<?php echo $elements['address2'][1] ? 'true' : false; ?>" />
	</div>
	<?php endif; ?>

	<?php if( $elements['country'][0] ) :?>
	<div>
		<label class="key">
			<?php echo JText::_('COM_TIENDA_COUNTRY'); ?>
			<?php if( $elements['country'][1] ): ?>
				<?php echo TiendaGrid::required(); ?>
			<?php endif;?>
		</label>
		<?php
		$url = "index.php?option=com_tienda&format=raw&controller=checkout&task=getzones&prefix={$this->form_prefix}&country_id=";

		$onchange = 'tiendaPutAjaxLoader( \''.$this->form_prefix.'zones_wrapper\' );tiendaDoTask( \''.$url.'\'+document.getElementById(\''.$this->form_prefix.'country_id\').value, \''.$this->form_prefix.'zones_wrapper\', \'\', \'\', false, function() {tiendaCheckoutAutomaticShippingRatesUpdate( \''.$this->form_prefix.'country_id\' ); });';
		if( $one_page )
		{
			$onchange = 'tiendaPutAjaxLoader( \''.$this->form_prefix.'zones_wrapper\' );'.
									'tiendaDoTask( \''.$url.'\'+document.getElementById(\''.$this->form_prefix.'country_id\').value, \''.$this->form_prefix.'zones_wrapper\', \'\', \'\', false, '.
									'function() {tiendaCheckoutAutomaticShippingRatesUpdate( \''.$this->form_prefix.'country_id\' ); '.
									'
			});';
		}

		$attribs = array('class' => 'inputbox','size' => '1','onchange' => $onchange );
		echo TiendaSelect::country( $this->default_country_id, $this->form_prefix.'country_id', $attribs, $this->form_prefix.'country_id', false, true );
		?>
	</div>
	<?php endif; ?>

	<?php if( $elements['city'][0] ) :?>
	<div>
		<label class="key" for="<?php echo $this->form_prefix; ?>city">
			<?php echo JText::_('COM_TIENDA_CITY'); ?>
			<?php if( $elements['city'][1] ): ?>
				<?php echo TiendaGrid::required(); ?>
			<?php endif;?>		
		</label>
		<input type="text" name="<?php echo $this->form_prefix; ?>city" id="<?php echo $this->form_prefix; ?>city" class="inputbox" size="48" maxlength="250" />
	</div>
	<?php endif; ?>

	<div class="floatbox">

		<?php if( $elements['zone'][0] ) :?>
		<div>
			<label class="key">
				<?php echo JText::_('COM_TIENDA_ZONE'); ?>
				<?php if( $elements['zone'][1] ): ?>
					<?php echo TiendaGrid::required(); ?>
				<?php endif;?>
			</label>
			<div id="<?php echo $this->form_prefix; ?>zones_wrapper">
				<?php
				if (!empty($this->zones)) {
					echo $this->zones;
				} else {
					echo JText::_('COM_TIENDA_SELECT_COUNTRY_FIRST');
				}
				?>
			</div>
		</div>
		<?php endif; ?>
	</div>

	<?php if( $elements['zip'][0] ) :?>
		<div>
			<label class="key" for="<?php echo $this->form_prefix; ?>postal_code">
				<?php echo JText::_('COM_TIENDA_POSTAL_CODE'); ?>
				<?php if( $elements['zip'][1] ): ?>
					<?php echo TiendaGrid::required(); ?>
				<?php endif;?>
			</label>
			<?php
			$onchange = '';
			if( !empty( $this->showShipping ) )
      {
        if( $one_page )
		  		$onchange = 'tiendaCheckoutAutomaticShippingRatesUpdate( \''.$this->form_prefix.'postal_code\' )';
        else
			    $onchange = 'tiendaGrayOutAddressDiv( \'Updating Address\' ); tiendaGetShippingRates( \'onCheckoutShipping_wrapper\', document.adminForm, tiendaDeleteAddressGrayDiv );';
			}
      ?>
			<input type="text" name="<?php echo $this->form_prefix; ?>postal_code" id="<?php echo $this->form_prefix; ?>postal_code" class="inputbox" size="25" maxlength="250" <?php if ( strlen( $onchange ) ) { ?> onchange="<?php echo $onchange; ?>" <?php } ?> />
		</div>
		<?php endif; ?>

	<?php if( $elements['phone'][0] ) :?>
	<div>
		<label class="key" name="<?php echo $this->form_prefix; ?>phone_1">
			<?php echo JText::_('COM_TIENDA_PHONE'); ?>
			<?php if( $elements['phone'][1] ): ?>
				<?php echo TiendaGrid::required(); ?>
			<?php endif;?>
		</label>
		<input name="<?php echo $this->form_prefix; ?>phone_1" id="<?php echo $this->form_prefix; ?>phone_1" class="inputbox" type="text" size="25" maxlength="250" data-required="<?php echo $elements['phone'][1] ? 'true' : false; ?>" />
	</div>
	<?php endif; ?>



	<?php
	$data = new JObject();
	$dispatcher = JDispatcher::getInstance();
	$dispatcher->trigger('onAfterDisplayAddressDetails', array($data, $this->form_prefix) );
	?>

</div>