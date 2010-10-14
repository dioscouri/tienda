<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); ?>

<fieldset>
<table class="address_form" style="clear: both;" >
    <tbody>
    <?php if(!$this->guest) { ?>
    <tr>
		<th style="width: 100px; text-align: right;" class="key">
            <?php echo JText::_( 'Address Title' ); ?>
		</th>
		<td>
            <?php echo JText::_( 'Address Title For Your Reference' ); ?>
            <br/>
	        <input name="<?php echo $this->form_prefix; ?>address_name" id="<?php echo $this->form_prefix; ?>address_name"
			type="text" size="48" maxlength="250" />
		</td>
	</tr>
	<?php } 
			else
				echo '<input value="'.JText::_( 'Temporary' ).'" name="'.$this->form_prefix.'address_name" id="'.$this->form_prefix.'address_name" type="hidden" />';
	?>
    <tr>
        <th style="width: 100px; text-align: right;" class="key">
             <?php echo JText::_( 'First name' ); ?>
        </th>
        <td>
            <input name="<?php echo $this->form_prefix; ?>first_name" id="<?php echo $this->form_prefix; ?>first_name" 
            type="text" size="35" maxlength="250" />
        </td>
    </tr>
    <tr>
        <th style="width: 100px; text-align: right;" class="key">
             <?php echo JText::_( 'Middle name' ); ?>
        </th>
        <td>
           <input type="text" name="<?php echo $this->form_prefix; ?>middle_name"
            id="<?php echo $this->form_prefix; ?>middle_name" size="25" maxlength="250" />
        </td>
    </tr>
    <tr>
        <th style="width: 100px; text-align: right;" class="key">
             <?php echo JText::_( 'Last name' ); ?>
        </th>
        <td>
           <input type="text" name="<?php echo $this->form_prefix; ?>last_name"
            id="<?php echo $this->form_prefix; ?>last_name" size="45" maxlength="250" />
        </td>
    </tr>
    <tr>
        <th style="width: 100px; text-align: right;" class="key"> 
          <?php echo JText::_( 'Company' ); ?>
        </th>
        <td>
            <input type="text" name="<?php echo $this->form_prefix; ?>company" id="<?php echo $this->form_prefix; ?>company"
            size="48" maxlength="250" />
        </td>
    </tr>
    <tr>
        <th style="width: 100px; text-align: right;" class="key">
              <?php echo JText::_( 'Address Line 1' ); ?>
        </th>
        <td>
            <input type="text" name="<?php echo $this->form_prefix; ?>address_1"
            id="<?php echo $this->form_prefix; ?>address_1" size="48" maxlength="250" />
        </td>
    </tr>
    <tr>
        <th style="width: 100px; text-align: right;" class="key">
              <?php echo JText::_( 'Address Line 2' ); ?>
        </th>
        <td>
            <input type="text" name="<?php echo $this->form_prefix; ?>address_2"
            id="<?php echo $this->form_prefix; ?>address_2" size="48" maxlength="250" />
        </td>
    </tr>
	<tr>
		<th style="width: 100px; text-align: right;" class="key">
            <?php echo JText::_( 'City' ); ?>
		</th>
		<td>
			<input type="text" name="<?php echo $this->form_prefix; ?>city" 
			id="<?php echo $this->form_prefix; ?>city" size="48" maxlength="250" />
		</td>
	</tr>
	<tr>
		<th style="width: 100px; text-align: right;" class="key">
            <?php echo JText::_( 'Country' ); ?>
		</th>
		<td>
			<?php
			$url = "index.php?option=com_tienda&format=raw&controller=checkout&task=getzones&prefix={$this->form_prefix}&country_id=";
			$attribs = array('class' => 'inputbox','size' => '1','onchange' => 'tiendaDoTask( \''.$url.'\'+document.getElementById(\''.$this->form_prefix.'country_id\').value, \''.$this->form_prefix.'zones_wrapper\', \'\');' );
			echo TiendaSelect::country( '', $this->form_prefix.'country_id', $attribs, $this->form_prefix.'country_id', true, true );
			?>
        </td>
	</tr>
	<tr>
		<th style="width: 100px; text-align: right;" class="key">
            <?php echo JText::_( 'Zone' ); ?>
		</th>
		<td>
            <div id="<?php echo $this->form_prefix; ?>zones_wrapper">
            <?php 
                echo JText::_( "Select Country First" );
            ?>
            </div>
		</td>
	</tr>
	<tr>
        <th style="width: 100px; text-align: right;" class="key">
	       <?php echo JText::_( 'Postal code' ); ?>
    	</th>
        <td>
			<input type="text" name="<?php echo $this->form_prefix; ?>postal_code"
			id="<?php echo $this->form_prefix; ?>postal_code" size="25" maxlength="250" 
			<?php if (!empty($this->showShipping)&& $this->forShipping ) { ?>onchange="tiendaGetShippingRates( 'onCheckoutShipping_wrapper', this.form );" <?php } ?> 
			/>
		</td>
	</tr>
	<tr>
		<th style="width: 100px; text-align: right;" class="key">
            <?php echo JText::_( 'Phone' ); ?>
		</th>
		<td>
			<input name="<?php echo $this->form_prefix; ?>phone_1" id="<?php echo $this->form_prefix; ?>phone_1"
			type="text" size="25" maxlength="250" />
		</td>
	</tr>
	<?php 
		$data = new JObject();
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onAfterDisplayAddressDetails', array($data, $this->form_prefix) );
	?>
	</tbody>
</table>
</fieldset>