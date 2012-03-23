<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php Tienda::load( 'TiendaUrl', 'library.url' ); ?>
<?php $helper_category =& TiendaHelperBase::getInstance( 'Category' ); ?>
<?php $helper_product =& TiendaHelperBase::getInstance( 'Product' ); ?>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>

    <table>
        <tr>
            <td align="left" width="100%">
            </td>
            <td nowrap="nowrap">
                <input name="filter" value="<?php echo @$state->filter; ?>" />
                <button onclick="this.form.submit();"><?php echo JText::_('COM_TIENDA_SEARCH'); ?></button>
                <button onclick="tiendaFormReset(this.form);"><?php echo JText::_('COM_TIENDA_RESET'); ?></button>
            </td>
        </tr>
    </table>

	<table class="adminlist" style="clear: both;">
		<thead>
            <tr>
                <th style="width: 5px;">
                	<?php echo JText::_('Num'); ?>
                </th>
                <th style="width: 20px;">
                	<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                </th>
                <th style="width: 50px;">
                	<?php echo TiendaGrid::sort( 'ID', "tbl.product_id", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: left;" colspan="2">
                	<?php echo TiendaGrid::sort( 'Name', "tbl.product_name", @$state->direction, @$state->order ); ?>
                	+
                	<?php echo TiendaGrid::sort( 'Rating', "tbl.product_rating", @$state->direction, @$state->order ); ?>
                	+
                	<?php echo TiendaGrid::sort( 'Reviews', "tbl.product_comments", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 70px;">
                	<?php echo TiendaGrid::sort( 'SKU', "tbl.product_sku", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 50px;">
                	<?php echo TiendaGrid::sort( 'Price', "price", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                	<?php echo TiendaGrid::sort( 'Quantity', "product_quantity", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'Order', "tbl.ordering", @$state->direction, @$state->order ); ?>
                    <?php echo JHTML::_('grid.order', @$items ); ?>
                </th>
                <th style="width: 100px;">
    	            <?php echo TiendaGrid::sort( 'Enabled', "tbl.product_enabled", @$state->direction, @$state->order ); ?>
                </th>
            </tr>
            <tr class="filterline">
                <th colspan="3">
                	<?php $attribs = array('class' => 'inputbox', 'size' => '1', 'onchange' => 'document.adminForm.submit();'); ?>
                	<div class="range">
	                	<div class="rangeline">
	                		<span class="label"><?php echo JText::_('COM_TIENDA_FROM'); ?>:</span> <input id="filter_id_from" name="filter_id_from" value="<?php echo @$state->filter_id_from; ?>" size="5" class="input" />
	                	</div>
	                	<div class="rangeline">
	                		<span class="label"><?php echo JText::_('COM_TIENDA_TO'); ?>:</span> <input id="filter_id_to" name="filter_id_to" value="<?php echo @$state->filter_id_to; ?>" size="5" class="input" />
	                	</div>
                	</div>
                </th>
                <th style="text-align: left;" colspan="2">
                	<input id="filter_name" name="filter_name" value="<?php echo @$state->filter_name; ?>" size="25"/>
                	<?php echo TiendaSelect::category( @$state->filter_category, 'filter_category', $attribs, 'category', true ); ?>
                	<?php echo TiendaSelect::booleans( @$state->filter_ships, 'filter_ships', $attribs, 'ships', true, 'Requires Shipping', 'Yes', 'No' ); ?>
                	<?php echo TiendaSelect::taxclass( @$state->filter_taxclass, 'filter_taxclass', $attribs, 'taxclass', true, false ); ?>
                </th>
                <th>
                	<input id="filter_sku" name="filter_sku" value="<?php echo @$state->filter_sku; ?>" size="15"/>
                </th>
                <th>
                	<div class="range">
	                	<div class="rangeline">
	                		<span class="label"><?php echo JText::_('COM_TIENDA_FROM'); ?>:</span> <input id="filter_price_from" name="filter_price_from" value="<?php echo @$state->filter_price_from; ?>" size="5" class="input" />
	                	</div>
	                	<div class="rangeline">
	                		<span class="label"><?php echo JText::_('COM_TIENDA_TO'); ?>:</span> <input id="filter_price_to" name="filter_price_to" value="<?php echo @$state->filter_price_to; ?>" size="5" class="input" />
	                	</div>
                	</div>
                </th>
                <th>
                	<div class="range">
	                	<div class="rangeline">
	                		<span class="label"><?php echo JText::_('COM_TIENDA_FROM'); ?>:</span> <input id="filter_quantity_from" name="filter_quantity_from" value="<?php echo @$state->filter_quantity_from; ?>" size="5" class="input" />
	                	</div>
	                	<div class="rangeline">
	                		<span class="label"><?php echo JText::_('COM_TIENDA_TO'); ?>:</span> <input id="filter_quantity_to" name="filter_quantity_to" value="<?php echo @$state->filter_quantity_to; ?>" size="5" class="input" />
	                	</div>
                	</div>
                </th>
                <th>
                </th>
                <th>
    	            <?php echo TiendaSelect::booleans( @$state->filter_enabled, 'filter_enabled', $attribs, 'enabled', true, 'Enabled State' ); ?>
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
					<?php echo TiendaGrid::checkedout( $item, $i, 'product_id' ); ?>
				</td>
				<td style="text-align: center;">
					<a href="<?php echo $item->link_edit; ?>">
						<?php echo $item->product_id; ?>
					</a>
				</td>
				<td style="text-align: center; width: 50px;">
                    <?php echo $helper_product->getImage($item->product_id, 'id', $item->product_name, 'full', false, false, array( 'width'=>48 ) ); ?>
				</td>
				<td style="text-align: left;">
					<a href="<?php echo $item->link_edit; ?>">
						<?php echo JText::_($item->product_name); ?>
					</a>
					
					<div class="product_rating">
					   <?php echo $helper_product->getRatingImage( $this, $item->product_rating ); ?>
					   <?php if (!empty($item->product_comments)) : ?>
					   <span class="product_comments_count">(<?php echo $item->product_comments; ?>)</span>
					   <?php endif; ?>
					</div>
					
					<div class="product_categories">
						<span style="float: right;">[<?php echo TiendaUrl::popup( "index.php?option=com_tienda&controller=products&task=selectcategories&id=".$item->product_id."&tmpl=component", JText::_('Select Categories'), array('update' => true) ); ?>]</span>
						<?php $categories = $helper_product->getCategories( $item->product_id ); ?>
						<?php for ($n='0'; $n<count($categories) && $n<'1'; $n++) : ?>
							<?php $category = $categories[$n]; ?>
							<?php echo $helper_category->getPathName( $category ); ?>
							<br/>
						<?php endfor; ?>
						<?php if (count($categories) > $n) { echo sprintf( JText::_('And x More'), count($categories) - $n ); } ?>
					</div>

                    <div class="product_images_path">
                        <b><?php echo JText::_('Image Gallery Path'); ?>:</b> <?php echo str_replace( JPATH_SITE, '', $helper_product->getGalleryPath( $item->product_id ) ); ?>
                    </div>

                    <?php 
                    $layout = $helper_product->getLayout( $item->product_id );
                    if ($layout != 'view') 
                    {
                        echo "<b>".JText::_('Layout Override')."</b>: ".$layout; 
                    }
                    ?>
                </td>
                
				<td style="text-align: center;">
					<?php echo $item->product_sku; ?>
				</td>
				<td style="text-align: right;">
					<?php echo TiendaHelperBase::currency($item->price); ?>
					<br/>
					[<?php echo TiendaUrl::popup( "index.php?option=com_tienda&controller=products&task=setprices&id=".$item->product_id."&tmpl=component", JText::_('Set Prices'), array('update' => true) ); ?>]
				</td>
				<td style="text-align: center;">
					
					<?php 
					if(!isset($item->product_check_inventory)){
						echo JText::_('Check Product Inventory Disabled');
					} else {
						echo (int) $item->product_quantity; ?>
                    <br/>
                    [<?php echo TiendaUrl::popup( "index.php?option=com_tienda&controller=products&task=setquantities&id=".$item->product_id."&tmpl=component", JText::_('Set Quantities'), array('update' => true) ); ?>]
                    
                    <?php } ?>
				</td>
                <td style="text-align: center;">
                    <?php echo TiendaGrid::order($item->product_id); ?>
                    <?php echo TiendaGrid::ordering($item->product_id, $item->ordering ); ?>
                </td>
				<td style="text-align: center;">
					<?php echo TiendaGrid::enable($item->product_enabled, $i, 'product_enabled.' ); ?>
				</td>
			</tr>
			<?php ++$i; $k = (1 - $k); ?>
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