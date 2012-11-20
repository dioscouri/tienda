<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('script', 'core.js', 'media/system/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php $row = @$this->row; ?>
<?php $suffix = 'COM_TIENDA_' . strtoupper(@$this->suffix);?>

<h1 style="margin-left: 2%; margin-top: 2%;"><?php echo JText::sprintf('COM_TIENDA_SELECT_SUFFIX_PLUGINS_FOR', JText::_( $suffix ) ); ?>: <?php echo $row->geozone_name; ?></h1>


<div class="note_green" style="width: 95%; text-align: center; margin-left: auto; margin-right: auto;">
	<?php echo JText::_('COM_TIENDA_FOR_CHECKED_ITEMS'); ?>:
	<button class="btn btn-success" onclick="document.getElementById('task').value='plugin_switch'; document.adminForm.submit();"> <?php echo JText::_('COM_TIENDA_CHANGE_STATUS'); ?></button><br />	
	<button class="btn" onclick="document.adminForm.toggle.checked=true; checkAll(<?php echo count( @$items ); ?>);document.getElementById('task').value='plugin_switch'; document.adminForm.submit();"> <?php echo JText::_('COM_TIENDA_TOGGLE_ALL_STATUS'); ?></button>
	
<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

	<div style="text-align: right;">
			<input name="filter" size="40" value="<?php echo @$state->filter; ?>" />
            <button class="btn btn-primary" onclick="this.form.submit();"><?php echo JText::_('COM_TIENDA_SEARCH'); ?></button>
            <button class="btn btn-danger" onclick="tiendaFormReset(this.form);"><?php echo JText::_('COM_TIENDA_RESET'); ?></button>
	</div>
	
	<table class="table table-striped table-bordered" style="clear: both;">
		<thead>
            <tr>
                <th style="width: 5px;">
                	<?php echo JText::_('COM_TIENDA_NUM'); ?>
                </th>
                <th style="width: 20px;">
                	<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                </th>
                <th style="width: 50px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_ID', "tbl.id", @$state->direction, @$state->order ); ?>
                </th>                
                <th style="text-align: left;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_NAME', "tbl.name", @$state->direction, @$state->order ); ?>
                </th>
                <th>
	                <?php echo JText::_('COM_TIENDA_STATUS'); ?>
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
					<?php echo TiendaGrid::checkedout( $item, $i, 'id' ); ?>
				</td>
				<td style="text-align: center;">
					<?php echo $item->id; ?>
				</td>	
				<td style="text-align: left;">
					<?php echo JText::_($item->name); ?>
				</td>
				<td style="text-align: center;">
					<?php $found = in_array($row->geozone_id, $item->geozones) ? true : false;?>					
					<?php echo TiendaGrid::enable($found, $i, 'plugin_'); ?>
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
	
	<input type="hidden" name="task" id="task" value="selectplugins" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
	
	<?php echo $this->form['validate']; ?>
</form>
</div>