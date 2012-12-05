<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; 
JFilterOutput::objectHTMLSafe( $row );
?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" id="adminForm" >
	
<table class="table table-striped table-bordered">
	<tr>
		<td class="dsc-key">
			<?php echo JText::_('COM_TIENDA_ADDRESS_NAME'); ?>:
		</td>
		<td>
			<input type="text" name="address_name" value="<?php echo @$row->address_name; ?>" />
		</td>
	</tr>
	<tr>
		<td class="dsc-key">
			<?php echo JText::_('COM_TIENDA_USER_ID'); ?>:
		</td>
		<td>
			<input type="text" name="user_id" value="<?php echo @$row->user_id; ?>" />
		</td>
	</tr>
	<tr>
		<td class="dsc-key">
			<?php echo JText::_('COM_TIENDA_FIRST_NAME'); ?>:
		</td>
		<td>
			<input type="text" name="first_name" value="<?php echo @$row->first_name; ?>" />
		</td>
	</tr>
	<tr>
		<td class="dsc-key">
			<?php echo JText::_('COM_TIENDA_LAST_NAME'); ?>:
		</td>
		<td>
			<input type="text" name="last_name" value="<?php echo @$row->last_name; ?>" />
		</td>
	</tr>
	<tr>
		<td class="dsc-key">
			<?php echo JText::_('COM_TIENDA_MIDDLE_NAME'); ?>:
		</td>
		<td>
			<input type="text" name="middle_name" value="<?php echo @$row->middle_name; ?>" />
		</td>
	</tr>

	<tr>
		<td class="dsc-key">
			<?php echo JText::_('COM_TIENDA_ADDRESS_LINE_1'); ?>:
		</td>
		<td>
			<input type="text" name="address_1" value="<?php echo @$row->address_1; ?>" />
		</td>
	</tr>
	<tr>
		<td class="dsc-key">
			<?php echo JText::_('COM_TIENDA_ADDRESS_LINE_2'); ?>:
		</td>
		<td>
			<input type="text" name="address_2" value="<?php echo @$row->address_2; ?>" />
		</td>
	</tr>
	<tr>
		<td class="dsc-key">
			<?php echo JText::_('COM_TIENDA_CITY'); ?>:
		</td>
		<td>
			<input type="text" name="city" value="<?php echo @$row->city; ?>" />
		</td>
	</tr>
	<tr>
		<td class="dsc-key">
			<?php echo JText::_('COM_TIENDA_POSTAL_CODE'); ?>:
		</td>
		<td>
			<input type="text" name="postal_code" value="<?php echo @$row->postal_code; ?>" />
		</td>
	</tr>
	<tr>
		<td class="dsc-key">
			<?php echo JText::_('COM_TIENDA_COUNTRY'); ?>:
		</td>
		<td>
			<?php
			$url = "index.php?option=com_tienda&format=raw&controller=addresses&task=getzones&country_id=";
			$attribs = array('class' => 'inputbox','size' => '1','onchange' => 'Dsc.doTask( \''.$url.'\'+document.getElementById(\'country_id\').value, \'zones_wrapper\', \'\');' );
			echo TiendaSelect::country( @$row->country_id, 'country_id', $attribs, 'country_id', true, true );
			?>
		</td>
	</tr>
	<tr>
		<td class="dsc-key">
			<?php echo JText::_('COM_TIENDA_ZONE'); ?>:
		</td>
		<td>
            <div id="zones_wrapper">
            <?php 
            if (empty($row->country_id)) 
            {
            	echo JText::_('COM_TIENDA_SELECT_COUNTRY_FIRST'); 
            }
            else
            {
            	echo TiendaSelect::zone( @$row->zone_id, 'zone_id', $row->country_id );
            }
            ?>
            </div>
		</td>
	</tr>
	<tr>
		<td class="dsc-key">
			<?php echo JText::_('COM_TIENDA_IS_DEFAULT_BILLING'); ?>:
		</td>
		<td>
			<?php echo TiendaSelect::btbooleanlist( 'is_default_billing', '', @$row->is_default_billing ); ?>
		</td>
	</tr>
	<tr>
		<td class="dsc-key">
			<?php echo JText::_('COM_TIENDA_IS_DEFAULT_SHIPPING'); ?>:
		</td>
		<td>
			<?php echo TiendaSelect::btbooleanlist( 'is_default_shipping', '', @$row->is_default_shipping ); ?>
		</td>
	</tr>
	<tr>
		<td class="dsc-key">
			<?php echo JText::_('COM_TIENDA_PHONE_1'); ?>:
		</td>
		<td>
			<input type="text" name="phone_1" value="<?php echo @$row->phone_1; ?>" />
		</td>
	</tr>
	<tr>
		<td class="dsc-key">
			<?php echo JText::_('COM_TIENDA_PHONE_2'); ?>:
		</td>
		<td>
			<input type="text" name="phone_2" value="<?php echo @$row->phone_2; ?>" />
		</td>
	</tr>
	<tr>
		<td class="dsc-key">
			<?php echo JText::_('COM_TIENDA_FAX'); ?>:
		</td>
		<td>
			<input type="text" name="fax" value="<?php echo @$row->fax; ?>" />
		</td>
	</tr>
	<tr>
		<td class="dsc-key">
			<?php echo JText::_('COM_TIENDA_COMPANY'); ?>:
		</td>
		<td>
			<input type="text" name="company" value="<?php echo @$row->company; ?>" />
		</td>
	</tr>
	<tr>
		<td class="dsc-key">
			<?php echo JText::_('COM_TIENDA_TAX_NUMBER'); ?>:
		</td>
		<td>
			<input type="text" name="tax_number" value="<?php echo @$row->tax_number; ?>" />
		</td>
	</tr>
	<tr>
		<td class="dsc-key">
			<?php echo JText::_('COM_TIENDA_TITLE'); ?>:
		</td>
		<td>
			<input type="text" name="title" value="<?php echo @$row->title; ?>" />
		</td>
	</tr>
	<tr>
		<td class="dsc-key">
			<?php echo JText::_('COM_TIENDA_IS_DELETED'); ?>:
		</td>
		<td>
			<?php echo TiendaSelect::btbooleanlist( 'is_deleted', '', @$row->is_deleted ); ?>
		</td>
	</tr>
</table>

<div>
    <input type="hidden" name="id" value="<?php echo @$row->address_id; ?>" />
    <input type="hidden" name="task" value="" />
</div>


</form>