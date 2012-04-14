<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php $row = @$this->row; ?>

<h1 style="margin-left: 2%; margin-top: 2%;"><?php echo JText::_('COM_TIENDA_COM_TIENDA_SELECT_ZONES_FOR'); ?>: <?php echo $row->geozone_name; ?></h1>

<div class="note_green" style="width: 95%; text-align: center; margin-left: auto; margin-right: auto;">
	<?php echo JText::_('COM_TIENDA_FOR_CHECKED_ITEMS'); ?>:
	<button onclick="document.getElementById('task').value='selected_switch'; document.adminForm.submit();"> <?php echo JText::_('COM_TIENDA_CHANGE_STATUS'); ?></button><br />
	<button onclick="document.adminForm.toggle.checked=true; checkAll(<?php echo count( @$items ); ?>);document.getElementById('task').value='savezipranges'; document.adminForm.submit();"> <?php echo JText::_('COM_TIENDA_SAVE_ALL_CHANGES_TO_ZIP_RANGES'); ?></button>


<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

    <table>
        <tr>
            <td align="left" width="100%">
                <input name="filter" value="<?php echo @$state->filter; ?>" />
                <button onclick="this.form.submit();"><?php echo JText::_('COM_TIENDA_SEARCH'); ?></button>
                <button onclick="tiendaFormReset(this.form);"><?php echo JText::_('COM_TIENDA_RESET'); ?></button>
            </td>
            <td>
                <?php $attribs = array('class' => 'inputbox', 'size' => '1', 'onchange' => 'document.adminForm.submit();'); ?>
                <?php
                echo TiendaSelect::booleans( @$state->filter_associated, 'filter_associated', $attribs, $idtag = null, false, '', 'Associated Zones Only', 'All Zones' );
                ?>
            </td>
            <td nowrap="nowrap">
                <?php echo TiendaSelect::country( @$state->filter_countryid, 'filter_countryid', $attribs, 'country_id', true ); ?>
            </td>
        </tr>
    </table>

	<table class="adminlist" style="clear: both;">
		<thead>
            <tr>
                <th style="width: 5px;">
                	<?php echo JText::_('COM_TIENDA_NUM'); ?>
                </th>
                <th style="width: 20px;">
                	<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                </th>
                <th style="width: 50px;">
                    <?php echo TiendaGrid::sort( 'ID', "tbl.zone_id", @$state->direction, @$state->order ); ?>
                </th>                
                <th style="text-align: left;">
                    <?php echo TiendaGrid::sort( 'Name', "tbl.zone_name", @$state->direction, @$state->order ); ?>
                </th>
                <th>
	                <?php echo JText::_('COM_TIENDA_STATUS'); ?>
                </th>
                <th style="width: 150px;">
	                <?php echo JText::_('COM_TIENDA_POSTAL_CODE_RANGE'); ?>
                </th>
            </tr>
		</thead>
        <tbody>
		<?php $i=0; $k=0; ?>
        <?php foreach (@$items as $item) : ?>
            <tr class='row<?php echo $k; ?>'>
				<td align="center">
					<?php echo $i + 1; ?>
				</td>
				<td style="text-align: center;">
					<?php echo TiendaGrid::checkedout( $item, $i, 'zone_id' ); ?>
				</td>
				<td style="text-align: center;">
					<?php echo $item->zone_id; ?>
				</td>	
				<td style="text-align: left;">
					<?php echo JText::_($item->zone_name); ?>
				</td>
				<td style="text-align: center;">
					<?php $table = JTable::getInstance('ZoneRelations', 'TiendaTable'); ?>
					<?php
                    $keynames = array();
                    $keynames['geozone_id'] = $row->geozone_id;
                    $keynames['zone_id'] = $item->zone_id;
					?>
					<?php $table->load( $keynames ); ?>
					<?php echo TiendaGrid::enable(isset($table->geozone_id), $i, 'selected_'); ?>
				</td>
				<td style="text-align: center;">
					<?php if(isset($table->geozone_id)): ?>
					<input type="text" name="zip_range[<?php echo $table->zone_id;?>]" value="<?php echo @$table->zip_range;?>" />
					<?php endif; ?>
				</td>
			</tr>
			<?php $i=$i+1; $k = (1 - $k); ?>
			<?php endforeach; ?>
			
			<?php if (!count(@$items)) : ?>
			<tr>
				<td colspan="10" align="center">
					<?php echo JText::_('COM_TIENDA_NO_ITEMS_FOUND'); ?>
				</td>
			</tr>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="20">
					<?php echo @$this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
	</table>

	<input type="hidden" name="task" id="task" value="selectzones" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
	
	<?php echo $this->form['validate']; ?>
</form>
</div>