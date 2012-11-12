<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php Tienda::load( 'TiendaHelperCategory', 'helpers.category' ); ?>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">

	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>
	
   <?php echo TiendaGrid::searchform(@$state->filter,JText::_('COM_TIENDA_SEARCH'), JText::_('COM_TIENDA_RESET') ) ?>
	

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
                <th style="width: 50px;">
                </th>
                <th style="text-align: left;">
                	<?php echo TiendaGrid::sort( 'COM_TIENDA_NAME', "tbl.category_name", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                </th>
                <th style="width: 100px;">
    	            <?php echo TiendaGrid::sort( 'COM_TIENDA_ORDER', "tbl.lft", @$state->direction, @$state->order ); ?>
    	            <?php echo JHTML::_('grid.order', @$items ); ?>
                </th>
                <th style="width: 100px;">
    	            <?php echo TiendaGrid::sort( 'COM_TIENDA_ENABLED', "tbl.category_enabled", @$state->direction, @$state->order ); ?>
                </th>
            </tr>
            <tr class="filterline">
                <th colspan="3">
	                <?php $attribs = array('class' => 'inputbox', 'size' => '1', 'onchange' => 'document.adminForm.submit();'); ?>
                	<div class="range">
                        <div class="rangeline">
                            <input type="text" placeholder="<?php echo JText::_('COM_TIENDA_FROM'); ?>" id="filter_id_from" name="filter_id_from" value="<?php echo @$state->filter_id_from; ?>" size="5" class="input input-tiny" />
                        </div>
                        <div class="rangeline">
                            <input type="text" placeholder="<?php echo JText::_('COM_TIENDA_TO'); ?>" id="filter_id_to" name="filter_id_to" value="<?php echo @$state->filter_id_to; ?>" size="5" class="input input-tiny" />
                        </div>
                    </div>
                </th>
                <th>
                </th>
                <th style="text-align: left;">
                	<input type="text" id="filter_name" name="filter_name" value="<?php echo @$state->filter_name; ?>" size="25"/>
                	<?php echo TiendaSelect::category( @$state->filter_parentid, 'filter_parentid', $attribs, 'parentid', true ); ?>
                </th>
                <th>
                </th>
                <th>
                </th>
                <th>
    	            <?php echo TiendaSelect::booleans( @$state->filter_enabled, 'filter_enabled', $attribs, 'enabled', true ); ?>
                </th>
            </tr>
			<tr>
				<th colspan="20" style="font-weight: normal;">
					<div style="float: right; padding: 5px;"><?php echo @$this->pagination->getResultsCounter(); ?></div>
					<div style="float: left;"><?php echo @$this->pagination->getListFooter(); ?></div>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="20">
					<div style="float: right; padding: 5px;"><?php echo @$this->pagination->getResultsCounter(); ?></div>
					<?php echo @$this->pagination->getPagesLinks(); ?>
				</td>
			</tr>
		</tfoot>
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
					<a href="<?php echo $item->link; ?>">
						<?php echo $item->category_id; ?>
					</a>
				</td>
                <td style="text-align: center;">
                    <?php echo TiendaHelperCategory::getImage($item->category_full_image, '', JText::_($item->category_name) ); ?>
                </td>
				<td style="text-align: left;">
					<a href="<?php echo $item->link; ?>">
						<?php echo str_repeat( '.&nbsp;', $item->level-1 ).JText::_($item->name); ?>
					</a>
					<br/>
					<?php 
					$layout = Tienda::getClass( 'TiendaHelperCategory', 'helpers.category' )->getLayout( $item->category_id );
					if ($layout != 'default') 
					{
					    echo "<b>".JText::_('COM_TIENDA_LAYOUT_OVERRIDE')."</b>: ".$layout; 
					}
					?>
				</td>
                <td style="text-align: center;">
                    <?php Tienda::load( 'TiendaUrl', 'library.url' ); ?>
                    <?php echo $item->products_count." ".JText::_('COM_TIENDA_PRODUCTS'); ?>
                    <br/>
                    <?php $select_url = "index.php?option=com_tienda&controller=categories&task=selectproducts&id=".$item->category_id."&tmpl=component"; ?>
                    [<?php echo TiendaUrl::popup( $select_url, JText::_('COM_TIENDA_SELECT_PRODUCTS'), array('update' => true) ); ?>]
                </td>
				<td style="text-align: center;">
					<?php echo TiendaGrid::order($item->category_id); ?>
					<?php echo TiendaGrid::ordering($item->category_id, $item->ordering ); ?>
				</td>
				<td style="text-align: center;">
					<?php echo TiendaGrid::enable($item->category_enabled, $i, 'category_enabled.' ); ?>
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
	</table>

	<input type="hidden" name="order_change" value="0" />
	<input type="hidden" name="id" value="" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
	
	<?php echo $this->form['validate']; ?>
</form>