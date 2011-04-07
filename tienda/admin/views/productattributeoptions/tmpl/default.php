<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php $row = @$this->row; ?>
                            
<h1 style="margin-left: 2%; margin-top: 2%;"><?php echo JText::_( "Set Options for" ); ?>: <?php echo $row->productattribute_name; ?></h1>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>

	<div class="note" style="width: 96%; margin-left: auto; margin-right: auto;">
	
	    <div style="float: left; font-size: 1.3em; font-weight: bold; height: 30px;"><?php echo JText::_('Add a New Attribute Option'); ?></div>

	    <div class="reset"></div>
	    
                <table class="adminlist">
                <thead>
                <tr>
                    <th></th>
                    <th><?php echo JText::_( "Name" ); ?></th>
                    <th style="width: 15px;"><?php echo JText::_( "Prefix" ); ?></th>
                    <th><?php echo JText::_( "Price" ); ?></th>
                    <th><?php echo JText::_( "Code" ); ?></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <?php echo JText::_( "Complete this form to add a new option" ); ?>:
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
                        <input id="createproductattributeoption_code" name="createproductattributeoption_code" value="" />
                    </td>
                    <td>
                        <button onclick="document.getElementById('task').value='createattributeoption'; document.adminForm.submit();"><?php echo JText::_('Create Option'); ?></button>
                    </td>
                </tr>
                </tbody>
                </table>
                
	</div>

<div class="note_green" style="width: 96%; margin-left: auto; margin-right: auto;">
    <div style="float: left; font-size: 1.3em; font-weight: bold; height: 30px;"><?php echo JText::_('Current Attribute Options'); ?></div>
    <div style="float: right;">
        <button onclick="document.getElementById('task').value='saveattributeoptions'; document.adminForm.toggle.checked=true; checkAll(<?php echo count( @$items ); ?>); document.adminForm.submit();"><?php echo JText::_('Save All Changes'); ?></button>
    </div>
    <div class="reset"></div>
        
	<table class="adminlist" style="clear: both;">
		<thead>
            <tr>
                <th style="width: 20px;">
                	<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                </th>
                <th style="text-align: left;">
                	<?php echo TiendaGrid::sort( 'Option', "tbl.productattributeoption_name", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'Prefix', "tbl.productattributeoption_prefix", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: center;">
                    <?php echo TiendaGrid::sort( 'Price', "tbl.productattributeoption_price", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: center;">
                    <?php echo TiendaGrid::sort( 'Code', "tbl.productattributeoption_code", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: left;">
                	<?php echo TiendaGrid::sort( 'Parent Option', "tbl.parent_productattributeoption_id", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                	<?php echo TiendaGrid::sort( 'Order', "tbl.ordering", @$state->direction, @$state->order ); ?>
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
					<input type="text" name="name[<?php echo $item->productattributeoption_id; ?>]" value="<?php echo $item->productattributeoption_name; ?>" />
				</td>
                <td style="text-align: center;">
                    <?php echo TiendaSelect::productattributeoptionprefix( $item->productattributeoption_prefix, "prefix[{$item->productattributeoption_id}]" ); ?>
                </td>
                <td style="text-align: center;">
                    <input type="text" name="price[<?php echo $item->productattributeoption_id; ?>]" value="<?php echo $item->productattributeoption_price; ?>" size="10" />
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
					
					
					echo TiendaSelect::productattributes($attribute_id, $row->product_id, $item->productattributeoption_id, array('class' => 'inputbox', 'size' => '1'), null, $allowAny = true, $title = 'No Parent');
					
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
					<input type="text" name="ordering[<?php echo $item->productattributeoption_id; ?>]" value="<?php echo $item->ordering; ?>" size="10" />
				</td>
				<td style="text-align: center;">
					[<?php echo TiendaUrl::popup( "index.php?option=com_tienda&controller=products&task=setattributeoptionvalues&id=".$item->productattributeoption_id."&tmpl=component", JText::_( "Set Values" ) ); ?>]
				</td>
				<td style="text-align: center;">
					[<a href="index.php?option=com_tienda&controller=productattributeoptions&task=delete&cid[]=<?php echo $item->productattributeoption_id; ?>&return=<?php echo base64_encode("index.php?option=com_tienda&controller=products&task=setattributeoptions&id={$row->productattribute_id}&tmpl=component"); ?>">
						<?php echo JText::_( "Delete Option" ); ?>	
					</a>
					]
				</td>
			</tr>
			<?php $i=$i+1; $k = (1 - $k); ?>
			<?php endforeach; ?>
			
			<?php if (!count(@$items)) : ?>
			<tr>
				<td colspan="10" align="center">
					<?php echo JText::_('No items found'); ?>
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