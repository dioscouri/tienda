<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); ?>
<?php $row = @$this->row; ?>
<?php $config = TiendaConfig::getInstance(); ?>

<table>
    <tbody>
    <?php if($config->get('show_field_title', '3') != '0' ): ?>
    <tr>
		<th style="width: 100px; text-align: right;" class="key">
            <?php echo JText::_('COM_TIENDA_ADDRESS_TITLE'); ?>
		</th>
		<td>
            <?php echo JText::_('COM_TIENDA_ADDRESS_TITLE_FOR_YOUR_REFERENCE'); ?>
            <br/>
	        <input name="address_name" id="address_name" type="text" size="48" maxlength="250" value="<?php echo @$row->address_name; ?>" />
		</td>
	</tr>
	<?php endif;?>
    <tr>
        <th style="width: 100px; text-align: right;" class="key">
            <?php echo JText::_('COM_TIENDA_SET_AS_DEFAULT_SHIPPING'); ?>
        </th>
        <td>
            <?php echo JHTML::_('select.booleanlist', 'is_default_shipping', '', @$row->is_default_shipping ); ?>
        </td>
    </tr>
    <tr>
        <th style="width: 100px; text-align: right;" class="key">
            <?php echo JText::_('COM_TIENDA_SET_AS_DEFAULT_BILLING'); ?>
        </th>
        <td>
            <?php echo JHTML::_('select.booleanlist', 'is_default_billing', '', @$row->is_default_billing ); ?>
        </td>
    </tr>
    <?php if($config->get('show_field_name', '3') != '0' ): ?>
    <tr>
        <th style="width: 100px; text-align: right;" class="key">
             <?php echo JText::_('COM_TIENDA_FIRST_NAME'); ?>
        </th>
        <td>
            <input name="first_name" id="first_name"  type="text" size="35" maxlength="250" value="<?php echo @$row->first_name; ?>" />
        </td>
    </tr>
    <?php endif;?>
    <?php if($config->get('show_field_middle', '3') != '0' ): ?>
    <tr>
        <th style="width: 100px; text-align: right;" class="key">
             <?php echo JText::_('COM_TIENDA_MIDDLE_NAME'); ?>
        </th>
        <td>
           <input type="text" name="middle_name" id="middle_name" size="25" maxlength="250" value="<?php echo @$row->middle_name; ?>" />
        </td>
    </tr>
    <?php endif;?>
    <?php if($config->get('show_field_last', '3') != '0' ): ?>
    <tr>
        <th style="width: 100px; text-align: right;" class="key">
             <?php echo JText::_('COM_TIENDA_LAST_NAME'); ?>
        </th>
        <td>
           <input type="text" name="last_name" id="last_name" size="45" maxlength="250" value="<?php echo @$row->last_name; ?>" />
        </td>
    </tr>
    <?php endif;?>
    <?php if($config->get('show_field_company', '3') != '0' ): ?>
    <tr>
        <th style="width: 100px; text-align: right;" class="key"> 
          <?php echo JText::_('COM_TIENDA_COMPANY'); ?>
        </th>
        <td>
          <input type="text" name="company" id="company" size="48" maxlength="250" value="<?php echo @$row->company; ?>" />
        </td>
    </tr>
    <?php endif;?>
    <?php if($config->get('show_field_tax_number', '3') != '0' ): ?>
    <tr>
        <th style="width: 100px; text-align: right;" class="key"> 
          <?php echo JText::_('COM_TIENDA_COMPANY_TAX_NUMBER'); ?>
        </th>
        <td>
          <input type="text" name="tax_number" id="tax_number" size="48" maxlength="250" value="<?php echo @$row->tax_number; ?>" />
        </td>
    </tr>
    <?php endif;?>
    <?php if($config->get('show_field_address1', '3') != '0' ): ?>
    <tr>
        <th style="width: 100px; text-align: right;" class="key">
              <?php echo JText::_('COM_TIENDA_ADDRESS_LINE_1'); ?>
        </th>
        <td>
            <input type="text" name="address_1" id="address_1" size="48" maxlength="250"  value="<?php echo @$row->address_1; ?>" />
        </td>
    </tr>
    <?php endif;?>
    <?php if($config->get('show_field_address2', '3') != '0' ): ?>
    <tr>
        <th style="width: 100px; text-align: right;" class="key">
              <?php echo JText::_('COM_TIENDA_ADDRESS_LINE_2'); ?>
        </th>
        <td>
            <input type="text" name="address_2" id="address_2" size="48" maxlength="250"  value="<?php echo @$row->address_2; ?>" />
        </td>
    </tr>
    <?php endif;?>
    <?php if($config->get('show_field_city', '3') != '0' ): ?>
	<tr>
		<th style="width: 100px; text-align: right;" class="key">
            <?php echo JText::_('COM_TIENDA_CITY'); ?>
		</th>
		<td>
			<input type="text" name="city" id="city" size="48" maxlength="250" value="<?php echo @$row->city; ?>" />
		</td>
	</tr>
  <?php endif;?>
	<tr>
		<th style="width: 100px; text-align: right;" class="key">
            <?php echo JText::_('COM_TIENDA_COUNTRY'); ?>
		</th>
		<td>
			<?php
			// TODO Change this to use a task within the checkout controller rather than creating a new zones controller 
			$url = "index.php?option=com_tienda&format=raw&controller=addresses&task=getzones&country_id=";
			$attribs = array('class' => 'inputbox','size' => '1','onchange' => 'tiendaDoTask( \''.$url.'\'+document.getElementById(\'country_id\').value, \'zones_wrapper\', \'\');' );
			echo TiendaSelect::country( @$row->country_id, 'country_id', $attribs, 'country_id', true, true );
			?>
        </td>
	</tr>
	<tr>
		<th style="width: 100px; text-align: right;" class="key">
            <?php echo JText::_('COM_TIENDA_ZONE'); ?>
		</th>
		<td>
            <div id="zones_wrapper">
            <?php 
            if (empty($row->zone_id)) 
            {
            	echo JText::_('COM_TIENDA_SELECT_COUNTRY_FIRST'); 
            }
            else
            {
            	echo TiendaSelect::zone( $row->zone_id, 'zone_id', $row->country_id );
            }
            ?>
            </div>
		</td>
	</tr>
  <?php if($config->get('show_field_zip', '3') != '0' ): ?>
	<tr>
      <th style="width: 100px; text-align: right;" class="key">
	       <?php echo JText::_('COM_TIENDA_POSTAL_CODE'); ?>
    	</th>
      <td>
			<input type="text" name="postal_code" id="postal_code" size="25" maxlength="250"  value="<?php echo @$row->postal_code; ?>" />
		</td>
	</tr>
  <?php endif;?>
  <?php if($config->get('show_field_phone', '3') != '0' ): ?>
	<tr>
		<th style="width: 100px; text-align: right;" class="key">
            <?php echo JText::_('COM_TIENDA_PHONE'); ?>
		</th>
		<td>
			<input type="text" name="phone_1" id="phone_1" size="25" maxlength="250" value="<?php echo @$row->phone_1; ?>" />
		</td>
	</tr>
  <?php endif;?>
	<tr>
		<th style="width: 100px; text-align: right;" class="key">
            <?php echo JText::_('COM_TIENDA_CELL'); ?>
		</th>
		<td>
			<input type="text" name="phone_2" id="phone_2" size="25" maxlength="250" value="<?php echo @$row->phone_2; ?>" />
		</td>
	</tr>
	<tr>
		<th style="width: 100px; text-align: right;" class="key">
			<?php echo JText::_('COM_TIENDA_FAX'); ?>
		</th>
		<td>
			<input type="text" name="fax" id="fax" size="25" maxlength="250" value="<?php echo @$row->fax; ?>" />
		</td>
	</tr>
	<?php 
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onAfterDisplayAddressDetails', array($row, '') );
	?>
	</tbody>
</table>