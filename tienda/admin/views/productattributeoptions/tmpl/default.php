<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php $row = @$this->row; ?>
                            
<h1 style="margin-left: 2%; margin-top: 2%;"><?php echo JText::_('COM_TIENDA_SET_OPTIONS_FOR'); ?>: <?php echo $row->productattribute_name; ?></h1>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>

	<div class="note" style="width: 96%; margin-left: auto; margin-right: auto;">
	
	    <div style="float: left; font-size: 1.3em; font-weight: bold; height: 30px;"><?php echo JText::_('COM_TIENDA_ADD_A_NEW_ATTRIBUTE_OPTION'); ?></div>

	    <div class="reset"></div>
	    
                <table class="adminlist">
                <thead>
                <tr>
                    <th></th>
                    <th><?php echo JText::_('COM_TIENDA_NAME'); ?></th>
                    <th style="width: 15px;"><?php echo JText::_('COM_TIENDA_PRICE_PREFIX'); ?></th>
                    <th><?php echo JText::_('COM_TIENDA_PRICE'); ?></th>
                    <th style="width: 15px;"><?php echo JText::_('COM_TIENDA_WEIGHT_PREFIX'); ?></th>
                    <th><?php echo JText::_('COM_TIENDA_WEIGHT'); ?></th>
                    <th><?php echo JText::_('COM_TIENDA_CODE'); ?></th>
                    <th><?php echo JText::_('COM_TIENDA_IS_BLANK'); ?></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <?php echo JText::_('COM_TIENDA_COMPLETE_THIS_FORM_TO_ADD_A_NEW_OPTION'); ?>:
                    </td>
                    <td>
                        <input id="createproductattributeoption_name" name="createproductattributeoption_name" value="" />
                    </td>
                    <td>
                        <?php echo TiendaSelect::productattributeoptionprefix( "+", 'createproductattributeoption_prefix' ); ?>
                    </td>
                    <td>
                        <input id="createproductattributeoption_price" name="createproductattributeoption_price" value="" size="10" />
                    </td>
                    <td>
                        <?php echo TiendaSelect::productattributeoptionprefix( "+", 'createproductattributeoption_prefix_weight' ); ?>
                    </td>
                    <td>
                        <input id="createproductattributeoption_weight" name="createproductattributeoption_weight" value="" size="10" />
                    </td>
                    <td>
                        <input id="createproductattributeoption_code" name="createproductattributeoption_code" value="" />
                    </td>
                    <td>
	                    <?php echo TiendaSelect::booleans( 0, 'createproductattributeoption_blank', array('class' => 'inputbox', 'size' => '1'), null, false, 'Select State', 'Yes', 'No' );?>
                    </td>
                    <td>
                        <button onclick="document.getElementById('task').value='createattributeoption'; document.adminForm.submit();"><?php echo JText::_('COM_TIENDA_CREATE_OPTION'); ?></button>
                    </td>
                </tr>
                </tbody>
                </table>
                
	</div>

<div class="note_green" style="width: 96%; margin-left: auto; margin-right: auto;">
    <div style="float: left; font-size: 1.3em; font-weight: bold; height: 30px;"><?php echo JText::_('COM_TIENDA_CURRENT_ATTRIBUTE_OPTIONS'); ?></div>
    <div style="float: right;">
        <button onclick="document.getElementById('task').value='saveattributeoptions'; document.adminForm.toggle.checked=true; checkAll(<?php echo count( @$items ); ?>); document.adminForm.submit();"><?php echo JText::_('COM_TIENDA_SAVE_ALL_CHANGES'); ?></button>
    </div>
    <div class="reset"></div>
        
	<table class="adminlist" style="clear: both;">
		<thead>
            <tr>
                <th style="width: 20px;">
                	<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                </th>
                <th style="text-align: left;">
                	<?php echo TiendaGrid::sort( 'COM_TIENDA_OPTION', "tbl.productattributeoption_name", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_PRICE_PREFIX', "tbl.productattributeoption_prefix", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: center;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_PRICE', "tbl.productattributeoption_price", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_WEIGHT_PREFIX', "tbl.productattributeoption_prefix_weight", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: center;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_WEIGHT', "tbl.productattributeoption_weight", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: center;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_CODE', "tbl.productattributeoption_code", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: left;">
                	<?php echo TiendaGrid::sort( 'COM_TIENDA_PARENT_OPTION', "tbl.parent_productattributeoption_id", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: center;">
                	<?php echo JText::_('COM_TIENDA_IS_BLANK'); ?>
                </th>
                <th style="width: 100px;">
                	<?php echo TiendaGrid::sort( 'COM_TIENDA_ORDER', "tbl.ordering", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
				</th>
				<th style="width: 100px;">
				</th>
            </tr>
		</thead>
        <tbody>
		<?php $i=0; $k=0; ?>
        <?php foreach (@$items as $item) : ?>
            <tr class='row<?php echo $k; ?>'>
				<td style="text-align: center;">
					<?php echo TiendaGrid::checkedout( $item, $i, 'productattributeoption_id' ); ?>
				</td>
				<td style="text-align: left;">
					<input type="text" size="" name="name[<?php echo $item->productattributeoption_id; ?>]" value="<?php echo $item->productattributeoption_name; ?>" />
				</td>
                <td style="text-align: center;">
                    <?php echo TiendaSelect::productattributeoptionprefix( $item->productattributeoption_prefix, "prefix[{$item->productattributeoption_id}]" ); ?>
                </td>
                <td style="text-align: center;">
                    <input type="text" name="price[<?php echo $item->productattributeoption_id; ?>]" value="<?php echo $item->productattributeoption_price; ?>" size="10" />
                </td>
                <td style="text-align: center;">
                    <?php echo TiendaSelect::productattributeoptionprefix( $item->productattributeoption_prefix_weight, "prefix_weight[{$item->productattributeoption_id}]" ); ?>
                </td>
                <td style="text-align: center;">
                    <input type="text" name="weight[<?php echo $item->productattributeoption_id; ?>]" value="<?php echo $item->productattributeoption_weight; ?>" size="10" />
                </td>
                <td style="text-align: center;">
                    <input type="text" name="code[<?php echo $item->productattributeoption_id; ?>]" value="<?php echo $item->productattributeoption_code; ?>" size="10" />
                </td>
                <td style="text-align: left;">
					<?php
					if($item->parent_productattributeoption_id)
					{
						Tienda::load('TiendaTableProductAttributeOptions', 'tables.productattributeoptions');
						$opt = JTable::getInstance('ProductAttributeOptions', 'TiendaTable');
						$opt->load($item->parent_productattributeoption_id);
						$attribute_id = $opt->productattribute_id;
					}
					else
					{
						$attribute_id = 0;
					}
					
					
					echo TiendaSelect::productattributes($attribute_id, $row->product_id, $item->productattributeoption_id, array('class' => 'inputbox', 'size' => '1'), null, $allowAny = true, $title = 'COM_TIENDA_NO_PARENT');
					
					?>
					
					<div id="parent_option_select_<?php echo $item->productattributeoption_id; ?>">
					
					<?php
					
					if($item->parent_productattributeoption_id)
					{
						echo TiendaSelect::productattributeoptions($attribute_id, $item->parent_productattributeoption_id, 'parent['.$item->productattributeoption_id.']');	
					}
					
					?>
					
					</div>
				</td>
        <td style="text-align: center;">
	      	<?php echo TiendaSelect::booleans( $item->is_blank, 'blank['.$item->productattributeoption_id.']', array('class' => 'inputbox', 'size' => '1'), null, false, 'Select State', 'Yes', 'No' );?>
				</td>
				<td style="text-align: center;">
					<input type="text" name="ordering[<?php echo $item->productattributeoption_id; ?>]" value="<?php echo $item->ordering; ?>" size="10" />
				</td>
				<td style="text-align: center;">
					[<?php echo TiendaUrl::popup( "index.php?option=com_tienda&controller=products&task=setattributeoptionvalues&id=".$item->productattributeoption_id."&tmpl=component", JText::_('COM_TIENDA_SET_VALUES') ); ?>]
				</td>
				<td style="text-align: center;">
					[<a href="index.php?option=com_tienda&controller=productattributeoptions&task=delete&cid[]=<?php echo $item->productattributeoption_id; ?>&return=<?php echo base64_encode("index.php?option=com_tienda&controller=products&task=setattributeoptions&id={$row->productattribute_id}&tmpl=component"); ?>">
						<?php echo JText::_('COM_TIENDA_DELETE_OPTION'); ?>	
					</a>
					]
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
</div>

	<input type="hidden" name="order_change" value="0" />
	<input type="hidden" name="id" value="<?php echo $row->productattribute_id; ?>" />
	<input type="hidden" name="task" id="task" value="setattributeoptions" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
	
	<?php echo $this->form['validate']; ?>
	
</form>