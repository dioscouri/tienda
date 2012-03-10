<?php defined('_JEXEC') or die('Restricted access');?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');?>
<?php $row = @$this->address;?>
<?php JFilterOutput::objectHTMLSafe($row);?>
<?php $config = TiendaConfig::getInstance(); ?>

<form action="<?php echo JRoute::_('index.php?option=com_tienda&view=pos&tmpl=component');?>" method="post" class="adminForm" name="adminForm" >
	<fieldset>
		<div class="header icon-48-tienda" style="float: left;">
			<?php if($row->address_id):?>
			<?php echo JText::_('EDIT ADDRESS') . ": " . $row->address_name;?>	
			<?php else:?>
			<?php echo JText::_('NEW ADDRESS');?>	
			<?php endif;?>
		</div>
		<div class="toolbar" id="toolbar" style="float: right;">
			<table class="toolbar">
				<tr>
					<td align="center">
					<a onclick="javascript:submitbutton('addaddress'); return false;" href="#" >
					<span class="icon-32-save" title="<?php echo JText::_('COM_TIENDA_SAVE', true);?>"></span><?php echo JText::_('COM_TIENDA_SAVE');?>
					</a>
					</td>
					<td align="center">
					<a href="<?php echo JRoute::_('index.php?option=com_tienda&view=pos&task=addresses&tmpl=component');?>" >
					<span class="icon-32-cancel" title="<?php echo JText::_('COM_TIENDA_CANCEL', true);?>"></span><?php echo JText::_('COM_TIENDA_CANCEL');?>
					</a>
					</td>
				</tr>
			</table>
		</div>
	</fieldset>
	<div id="validationmessage">
	</div>
	<table>
		<tbody>
			<?php if($config->get('show_field_title', '3') != '0' ): ?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('COM_TIENDA_ADDRESS_TITLE');?>
				</th>
				<td>
				<?php echo JText::_('COM_TIENDA_ADDRESS_TITLE_FOR_YOUR_REFERENCE');?>
				<br/>
				<input name="address_name" id="address_name"
				type="text" size="48" maxlength="250"
				value="<?php echo @$row->address_name;?>" />
				</td>
			</tr>
			<?php endif;?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('SET AS DEFAULT SHIPPING');?>
				</th>
				<td>
				<?php echo JHTML::_('select.booleanlist', 'is_default_shipping', '', @$row->is_default_shipping);?>
				</td>
			</tr>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('SET AS DEFAUTL BILLING');?>
				</th>
				<td>
				<?php echo JHTML::_('select.booleanlist', 'is_default_billing', '', @$row->is_default_billing);?>
				</td>
			</tr>
			<?php if($config->get('show_field_name', '3') != '0' ): ?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('COM_TIENDA_FIRST_NAME');?>
				</th>
				<td>
				<input name="first_name" id="first_name"
				type="text" size="35" maxlength="250"
				value="<?php echo @$row->first_name;?>" />
				</td>
			</tr>
			<?php endif;?>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('COM_TIENDA_MIDDLE_NAME');?>
				</th>
				<td>
				<input type="text" name="middle_name"
				id="middle_name" size="25" maxlength="250"
				value="<?php echo @$row->middle_name;?>" />
				</td>
			</tr>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('COM_TIENDA_LAST_NAME');?>
				</th>
				<td>
				<input type="text" name="last_name"
				id="last_name" size="45" maxlength="250"
				value="<?php echo @$row->last_name;?>" />
				</td>
			</tr>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('COM_TIENDA_COMPANY');?>
				</th>
				<td>
				<input type="text" name="company" id="company"
				size="48" maxlength="250"
				value="<?php echo @$row->company;?>" />
				</td>
			</tr>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('COM_TIENDA_ADDRESS_LINE_1');?>
				</th>
				<td>
				<input type="text" name="address_1"
				id="address_1" size="48" maxlength="250"
				value="<?php echo @$row->address_1;?>" />
				</td>
			</tr>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('COM_TIENDA_ADDRESS_LINE_2');?>
				</th>
				<td>
				<input type="text" name="address_2"
				id="address_2" size="48" maxlength="250"
				value="<?php echo @$row->address_2;?>" />
				</td>
			</tr>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('COM_TIENDA_CITY');?>
				</th>
				<td>
				<input type="text" name="city" id="city"
				size="48" maxlength="250"
				value="<?php echo @$row->city;?>" />
				</td>
			</tr>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('COM_TIENDA_COUNTRY');?>
				</th>
				<td>
				<?php
				// TODO Change this to use a task within the checkout controller rather than creating a new zones controller
				
				$url = "index.php?option=com_tienda&format=raw&controller=pos&task=getzones&prefix=&country_id=";
				$attribs = array('class' => 'inputbox',
				'size' => '1',
				'onchange' => 'tiendaDoTask( \'' . $url . '\'+document.getElementById(\'country_id\').value, \'zones_wrapper\', \'\');');
				echo TiendaSelect::country(@$row->country_id, 'country_id', $attribs, 'country_id', true, true);
				?>
				</td>
			</tr>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('COM_TIENDA_ZONE');?>
				</th>
				<td>
				<div id="zones_wrapper">
					<?php
					if(empty($row->zone_id))
					{
						echo JText::_("COM_TIENDA_SELECT_COUNTRY_FIRST");
					}
					else
					{
						echo TiendaSelect::zone($row->zone_id, 'zone_id', $row->country_id);
					}
					?>
				</div>
				</td>
			</tr>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('COM_TIENDA_POSTAL_CODE');?>
				</th>
				<td>
				<input type="text" name="postal_code"
				id="postal_code" size="25" maxlength="250"
				value="<?php echo @$row->postal_code;?>" />
				</td>
			</tr>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('COM_TIENDA_PHONE');?>
				</th>
				<td>
				<input type="text" name="phone_1" id="phone_1"
				size="25" maxlength="250"
				value="<?php echo @$row->phone_1;?>" />
				</td>
			</tr>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('COM_TIENDA_CELL');?>
				</th>
				<td>
				<input type="text" name="phone_2" id="phone_2"
				size="25" maxlength="250"
				value="<?php echo @$row->phone_2;?>" />
				</td>
			</tr>
			<tr>
				<th style="width: 100px; text-align: right;" class="key">
				<?php echo JText::_('COM_TIENDA_FAX');?>
				</th>
				<td>
				<input type="text" name="fax" id="fax"
				size="25" maxlength="250"
				value="<?php echo @$row->fax;?>" />
				</td>
			</tr>
			<?php
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAfterDisplayAddressDetails', array($row,
			''));
			?>
		</tbody>
	</table>
	<input type="hidden" name="id" value="<?php echo @$row->address_id;?>" />
	<input type="hidden" name="task" id="task" value="" />
	<input type="hidden" name="task" value="addaddress" />
</form>