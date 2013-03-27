<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('script', 'core.js', 'media/system/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php $row = @$this->row; ?>

<h1 style="margin-left: 2%; margin-top: 2%;"><?php echo JText::_('COM_TIENDA_SELECT_CATEGORIES_FOR'); ?>: <?php echo $row->product_name; ?></h1>

<div class="note_green" style="width: 95%; text-align: center; margin-left: auto; margin-right: auto;">
	<?php echo JText::_('COM_TIENDA_FOR_CHECKED_ITEMS'); ?>:
	<button class="btn btn-success" onclick="document.getElementById('task').value='selected_switch'; document.adminForm.submit();"> <?php echo JText::_('COM_TIENDA_CHANGE_STATUS'); ?></button>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

    <table>
        <tr>
            <td align="left" width="100%">
                <input name="filter" type="text" value="<?php echo @$state->filter; ?>" />
                <button class="btn btn-primary" onclick="this.form.submit();"><?php echo JText::_('COM_TIENDA_SEARCH'); ?></button>
                <button class="btn btn-danger" onclick="tiendaFormReset(this.form);"><?php echo JText::_('COM_TIENDA_RESET'); ?></button>
            </td>
            <td nowrap="nowrap">
                <?php $attribs = array('class' => 'inputbox', 'size' => '1', 'onchange' => 'document.adminForm.submit();'); ?>
                <?php echo TiendaSelect::category( @$state->filter_parentid, 'filter_parentid', $attribs, 'parentid', true, true ); ?>
            </td>
        </tr>
    </table>
	
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
                	<?php echo TiendaGrid::sort( 'COM_TIENDA_ID', "tbl.category_id", @$state->direction, @$state->order ); ?>
                </th>                
                <th style="text-align: left;">
                	<?php echo TiendaGrid::sort( 'COM_TIENDA_NAME', "tbl.category_name", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
    	            <?php echo TiendaGrid::sort( 'COM_TIENDA_ORDER', "tbl.lft", @$state->direction, @$state->order ); ?>
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
					<?php echo TiendaGrid::checkedout( $item, $i, 'category_id' ); ?>
				</td>
				<td style="text-align: center;">
					<?php echo $item->category_id; ?>
				</td>	
				<td style="text-align: left;">
					<?php echo str_repeat( '.&nbsp;', $item->level-1 ).JText::_($item->name); ?>
				</td>
				<td style="text-align: center;">
					<?php // echo $item->ordering; ?>
				</td>
				<td style="text-align: center;">
					<?php $table = DSCTable::getInstance('ProductCategories', 'TiendaTable'); ?>
					<?php
                    $keynames = array();
                    $keynames['product_id'] = $row->product_id;
                    $keynames['category_id'] = $item->category_id;
					?>
					<?php $table->load( $keynames ); ?>
					<?php echo TiendaGrid::enable(isset($table->product_id), $i, 'selected_'); ?>
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

	<input type="hidden" name="task" id="task" value="selectcategories" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
	
	<?php echo $this->form['validate']; ?>
</form>
</div>