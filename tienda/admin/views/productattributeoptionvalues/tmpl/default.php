<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php $row = @$this->row; ?>
                            
<h1 style="margin-left: 2%; margin-top: 2%;"><?php echo JText::_('Set Values for'); ?>: <?php echo $row->productattributeoption_name; ?></h1>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>

	<div class="note" style="width: 96%; margin-left: auto; margin-right: auto;">
	
	    <div style="float: left; font-size: 1.3em; font-weight: bold; height: 30px;"><?php echo JText::_('Add a New Attribute Option Value'); ?></div>

	    <div class="reset"></div>
	    
                <table class="adminlist">
                <thead>
                <tr>
                    <th></th>
                    <th style="width: 15px;"><?php echo JText::_('Field'); ?></th>
                     <th><?php echo JText::_('Operator'); ?></th>
                    <th><?php echo JText::_('COM_TIENDA_VALUE'); ?></th>
                    <th></th>
                    
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <?php echo JText::_('Complete this form to add a new option value'); ?>:
                    </td>
                    <td>
                        <?php echo TiendaSelect::productattributeoptionvaluefield( "product_full_image", 'createproductattributeoptionvalue_field' ); ?>
                    </td>
                    <td>
                        <?php echo TiendaSelect::productattributeoptionvalueoperator( "replace", 'createproductattributeoptionvalue_operator' ); ?>
                    </td>
                    <td>
                        <input id="createproductattributeoptionvalue_value" name="createproductattributeoptionvalue_value" value="" />
                    </td>
                    <td>
                        <button onclick="document.getElementById('task').value='createattributeoptionvalue'; document.adminForm.submit();"><?php echo JText::_('Create Value'); ?></button>
                    </td>
                </tr>
                </tbody>
                </table>
                
	</div>

<div class="note_green" style="width: 96%; margin-left: auto; margin-right: auto;">
    <div style="float: left; font-size: 1.3em; font-weight: bold; height: 30px;"><?php echo JText::_('Current Attribute Option Values'); ?></div>
    <div style="float: right;">
        <button onclick="document.getElementById('task').value='saveattributeoptionvalues'; document.adminForm.toggle.checked=true; checkAll(<?php echo count( @$items ); ?>); document.adminForm.submit();"><?php echo JText::_('Save All Changes'); ?></button>
    </div>
    <div class="reset"></div>
        
	<table class="adminlist" style="clear: both;">
		<thead>
            <tr>
                <th style="width: 20px;">
                	<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                </th>
                <th style="text-align: left;">
                	<?php echo TiendaGrid::sort( 'Field', "tbl.productattributeoptionvalue_field", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'Operator', "tbl.productattributeoptionvalue_operator", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: center;">
                    <?php echo TiendaGrid::sort( 'Value', "tbl.productattributeoptionvalue_value", @$state->direction, @$state->order ); ?>
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
					<?php echo TiendaGrid::checkedout( $item, $i, 'productattributeoptionvalue_id' ); ?>
				</td>
				<td style="text-align: left;">
					<?php echo TiendaSelect::productattributeoptionvaluefield( $item->productattributeoptionvalue_field, "field[{$item->productattributeoptionvalue_id}]" ); ?>
				</td>
                <td style="text-align: center;">
                    <?php echo TiendaSelect::productattributeoptionvalueoperator( $item->productattributeoptionvalue_operator, "operator[{$item->productattributeoptionvalue_id}]" ); ?>
                </td>
                <td style="text-align: center;">
                    <input type="text" name="value[<?php echo $item->productattributeoptionvalue_id; ?>]" value="<?php echo $item->productattributeoptionvalue_value; ?>" size="10" />
                </td>
				<td style="text-align: center;">
					[<a href="index.php?option=com_tienda&controller=productattributeoptionvalues&task=delete&cid[]=<?php echo $item->productattributeoptionvalue_id; ?>&return=<?php echo base64_encode("index.php?option=com_tienda&controller=products&task=setattributeoptionvalues&id={$row->productattributeoption_id}&tmpl=component"); ?>">
						<?php echo JText::_('Delete Value'); ?>	
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
	<input type="hidden" name="id" value="<?php echo $row->productattributeoption_id; ?>" />
	<input type="hidden" name="task" id="task" value="setattributeoptionvalues" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
	
	<?php echo $this->form['validate']; ?>
	
</form>