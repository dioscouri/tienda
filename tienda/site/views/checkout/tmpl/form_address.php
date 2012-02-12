<?php defined('_JEXEC') or die('Restricted access');

	JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
	JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
	Tienda::load( 'TiendaGrid', 'library.grid' );
	Tienda::load( 'TiendaHelperAddresses', 'helpers.addresses' );
	$config = TiendaConfig::getInstance();
	$one_page =$config->get('one_page_checkout', 0);
	
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
	$js_strings = array( 'Updating Shipping Rates', 'Updating Cart', 'Updating Address',  'Updating Payment Methods' );
	TiendaHelperImage::addJsTranslationStrings( $js_strings );
?>

<div id="<?php echo $this->form_prefix; ?>addressForm" class="address_form">
	<?php if( !$this->guest && $elements['address_name'][0] ) { ?>

	<label class="key" for="<?php echo $this->form_prefix; ?>address_name"><?php echo JText::_( 'Address Title' ); ?>
		<span class="block"><?php echo JText::_( 'Address Title For Your Reference' ); ?>
		<?php if( !$this->guest && $elements['address_name'][1] ): ?>
			<?php echo TiendaGrid::required(); ?>
		<?php endif;?>
		</span>
	</label>
	<input name="<?php echo $this->form_prefix; ?>address_name" id="<?php echo $this->form_prefix; ?>address_name" class="inputbox" type="text" maxlength="250" />&nbsp;

	<?php }
	else
		echo '<input value="'.JText::_( 'Temporary' ).'" name="'.$this->form_prefix.'address_name" id="'.$this->form_prefix.'address_name" type="hidden" />';
	?>
	<div class="floatbox">
	<?php if( $elements['first_name'][0] ) :?>
		<div>
			<label class="key" for="<?php echo $this->form_prefix; ?>first_name">
				<?php echo JText::_( 'First name' ); ?>
				<?php if( $elements['first_name'][1] ): ?>
					<?php echo TiendaGrid::required(); ?>
				<?php endif;?>			
			</label>
			<input name="<?php echo $this->form_prefix; ?>first_name"	id="<?php echo $this->form_prefix; ?>first_name" class="inputbox"	type="text" maxlength="250" />
		</div>
		<?php endif; ?>

		<?php if( $elements['middle_name'][0] ) :?>
		<div>
			<label class="key" for="<?php echo $this->form_prefix; ?>middle_name">
				<?php echo JText::_( 'Middle name' ); ?> 
				<?php if( $elements['middle_name'][1] ): ?>
					<?php echo TiendaGrid::required(); ?>
				<?php endif;?>
			</label>
			<input type="text" name="<?php echo $this->form_prefix; ?>middle_name" id="<?php echo $this->form_prefix; ?>middle_name" class="inputbox"	maxlength="250" />
		</div>
		<?php endif; ?>
	</div>

	<?php if( $elements['last_name'][0] ) :?>
	<div>
		<label class="key" for="<?php echo $this->form_prefix; ?>last_name">
			<?php echo JText::_( 'Last name' ); ?>
			<?php if( $elements['last_name'][1] ): ?>
				<?php echo TiendaGrid::required(); ?>
			<?php endif;?>
		</label>
		<input type="text" name="<?php echo $this->form_prefix; ?>last_name"	id="<?php echo $this->form_prefix; ?>last_name" class="inputbox" size="45" maxlength="250" />
	</div>
	<?php endif; ?>

	<?php if( $elements['address1'][0] ) :?>
	<div>
		<label class="key" for="<?php echo $this->form_prefix; ?>address_1">
			<?php echo JText::_( 'Address Line 1' ); ?>
			<?php if( $elements['address1'][1] ): ?>
				<?php echo TiendaGrid::required(); ?>
			<?php endif;?>
		</label>
		<input type="text"	name="<?php echo $this->form_prefix; ?>address_1" id="<?php echo $this->form_prefix; ?>address_1" class="inputbox" size="48" maxlength="250" />
	</div>
	<?php endif; ?>
	
	<?php if( $elements['address2'][0] ) :?>
	<div>
		<label class="key" for="<?php echo $this->form_prefix; ?>address_2">
			<?php echo JText::_( 'Address Line 2' ); ?>
			<?php if( $elements['address2'][1] ): ?>
				<?php echo TiendaGrid::required(); ?>
			<?php endif;?>
		</label>
		<input type="text" name="<?php echo $this->form_prefix; ?>address_2" id="<?php echo $this->form_prefix; ?>address_2" class="inputbox" size="48" maxlength="250" />
	</div>
	<?php endif; ?>

	<?php if( $elements['country'][0] ) :?>
	<div>
		<label class="key">
			<?php echo JText::_( 'Country' ); ?>
			<?php if( $elements['country'][1] ): ?>
				<?php echo TiendaGrid::required(); ?>
			<?php endif;?>
		</label>
		<?php
		$url = "index.php?option=com_tienda&format=raw&controller=checkout&task=getzones&prefix={$this->form_prefix}&country_id=";

		$onchange = 'tiendaPutAjaxLoader( \''.$this->form_prefix.'zones_wrapper\' );tiendaDoTask( \''.$url.'\'+document.getElementById(\''.$this->form_prefix.'country_id\').value, \''.$this->form_prefix.'zones_wrapper\', \'\', \'\', false, function() {tiendaCheckoutAutomaticShippingRatesUpdate( \''.$this->form_prefix.'country_id\' );}  );';
		if( $one_page )
		{
			$onchange = 'tiendaPutAjaxLoader( \''.$this->form_prefix.'zones_wrapper\' );'.
									'tiendaDoTask( \''.$url.'\'+document.getElementById(\''.$this->form_prefix.'country_id\').value, \''.$this->form_prefix.'zones_wrapper\', \'\', \'\', false, '.
									'function() {tiendaCheckoutAutomaticShippingRatesUpdate( \''.$this->form_prefix.'country_id\' ); '.
									'	});';
		}

		$attribs = array('class' => 'inputbox','size' => '1','onchange' => $onchange );
		echo TiendaSelect::country( $this->default_country_id, $this->form_prefix.'country_id', $attribs, $this->form_prefix.'country_id', false, true );
		?>
	</div>
	<?php endif; ?>

	<?php if( $elements['city'][0] ) :?>
	<div>
		<label class="key" for="<?php echo $this->form_prefix; ?>city">
			<?php echo JText::_( 'City' ); ?>
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
				<?php echo JText::_( 'Zone' ); ?>
				<?php if( $elements['zone'][1] ): ?>
					<?php echo TiendaGrid::required(); ?>
				<?php endif;?>
			</label>
			<div id="<?php echo $this->form_prefix; ?>zones_wrapper">
				<?php
				if (!empty($this->zones)) {
					echo $this->zones;
				} else {
					echo JText::_( "Select Country First" );
				}
				?>
			</div>
		</div>
		<?php endif; ?>
	</div>

	<?php if( $elements['zip'][0] ) :?>
		<div>
			<label class="key" for="<?php echo $this->form_prefix; ?>postal_code">
				<?php echo JText::_( 'Postal code' ); ?>
				<?php if( $elements['zip'][1] ): ?>
					<?php echo TiendaGrid::required(); ?>
				<?php endif;?>
			</label>
			<?php
			$onchange = '';
			if( $one_page )
				$onchange = 'tiendaCheckoutAutomaticShippingRatesUpdate( \''.$this->form_prefix.'postal_code\' )';
			else
				if( !empty($this->showShipping)&& $this->forShipping )
					$onchange = 'tiendaGrayOutAddressDiv( \''.JText::_( 'Updating Address' ).'\' ); tiendaGetShippingRates( \'onCheckoutShipping_wrapper\', document.adminForm, tiendaDeleteAddressGrayDiv );';
			?>
			<input type="text" name="<?php echo $this->form_prefix; ?>postal_code" id="<?php echo $this->form_prefix; ?>postal_code" class="inputbox" size="25" maxlength="250" <?php if ( strlen( $onchange ) ) { ?> onchange="<?php echo $onchange; ?>" <?php } ?> />
		</div>
		<?php endif; ?>

	<?php if( $elements['phone'][0] ) :?>
	<div>
		<label class="key" name="<?php echo $this->form_prefix; ?>phone_1">
			<?php echo JText::_( 'Phone' ); ?>
			<?php if( $elements['phone'][1] ): ?>
				<?php echo TiendaGrid::required(); ?>
			<?php endif;?>
		</label>
		<input name="<?php echo $this->form_prefix; ?>phone_1" id="<?php echo $this->form_prefix; ?>phone_1" class="inputbox" type="text" size="25" maxlength="250" />
	</div>
	<?php endif; ?>

	<div class="floatbox">
	<?php if( $elements['company'][0] ) :?>
		<div>
			<label class="key" for="<?php echo $this->form_prefix; ?>company">
				<?php echo JText::_( 'Company' ); ?>
				<?php if($elements['company'][1] ): ?>
					<?php echo TiendaGrid::required(); ?>
				<?php endif;?>
			</label>
			<input type="text" name="<?php echo $this->form_prefix; ?>company" id="<?php echo $this->form_prefix; ?>company" class="inputbox" size="48" maxlength="250" />
		</div>
		<?php endif; ?>
		<?php if( $elements['tax_number'][0] ) :?>
		<div>
			<label class="key" for="<?php echo $this->form_prefix; ?>tax_number">
				<?php echo JText::_( 'Co. Tax Number' ); ?>
				<?php if( $elements['tax_number'][1] ): ?>
					<?php echo TiendaGrid::required(); ?>
				<?php endif;?>
			</label>
			<input type="text" name="<?php echo $this->form_prefix; ?>tax_number" id="<?php echo $this->form_prefix; ?>tax_number" class="inputbox" size="48" maxlength="250" />
		</div>
		<?php endif; ?>
	</div>

	<?php
	$data = new JObject();
	$dispatcher = JDispatcher::getInstance();
	$dispatcher->trigger('onAfterDisplayAddressDetails', array($data, $this->form_prefix) );
	?>

</div>
