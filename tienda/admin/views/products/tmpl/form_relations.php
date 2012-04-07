<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>

<div>
	<table class="adminlist" style="clear: both;">
		<thead>
            <tr>
                <th style="width: 5px;">
                	<?php echo JText::_('COM_TIENDA_NUM'); ?>
                </th>
                <th style="width: 50px;">
                	<?php echo JText::_('COM_TIENDA_ID'); ?>
                </th>                
                <th style="text-align: center; width: 200px;">
                	<?php echo JText::_('Relationship'); ?>
                </th>
                <th style="text-align: left;">
    	            <?php echo JText::_('COM_TIENDA_PRODUCT'); ?>
                </th>
                <th style="text-align: center;">
                    <?php echo JText::_('COM_TIENDA_PRICE'); ?>
                </th>
                <th style="width: 50px;">
                </th>
            </tr>
		</thead>
        <tbody>
		<?php $i=0; $k=0; ?>
        <?php foreach (@$items as $item) : ?>
            
            <?php
            if ($item->relation_type == 'requires' && $item->product_id_to == $this->product_id)
            {
                $item->relation_type = 'required_by';
            }

            if ($item->relation_type == 'parent' && $item->product_id_to == $this->product_id)
            {
                $item->relation_type = 'child';
            }

            if ($item->relation_type == 'requires_past' && $item->product_id_to == $this->product_id)
            {
                $item->relation_type = 'requires_past_by';
            }

            if ($item->relation_type == 'requires_current' && $item->product_id_to == $this->product_id)
            {
                $item->relation_type = 'requires_current_by';
            }
            ?>
        
            <tr class='row<?php echo $k; ?>'>
				<td align="center">
					<?php echo $i + 1; ?>
				</td>
				<td style="text-align: center;">
					<?php echo $item->productrelation_id; ?>
				</td>	
				<td style="text-align: center;">
				    <span class="relationship_<?php echo $item->relation_type; ?>">
					<?php echo JText::_( "Relationship ". $item->relation_type ); ?>
					</span>
				</td>
				<td style="text-align: left;">
				    <?php 
				    if ($item->product_id_from == $this->product_id)
				    {
				        // display the _product_to
                        $product_id = $item->product_id_to;
                        $product_name = $item->product_name_to;
                        $product_model = $item->product_model_to;
                        $product_sku = $item->product_sku_to;
                        $product_price = $item->product_price_to;
				    } 
				        else 
				    { 
                        // display the _product_from
                        $product_id = $item->product_id_from;
                        $product_name = $item->product_name_from;
                        $product_model = $item->product_model_from;
                        $product_sku = $item->product_sku_from;
                        $product_price = $item->product_price_from;
				    } 
                    ?>
                    
                    #<?php echo $product_id; ?>: <?php echo $product_name; ?><br/>
                    <?php if (!empty($product_sku)) : ?>
                        <?php echo JText::_('COM_TIENDA_SKU').": ".$product_sku; ?><br/>
                    <?php endif; ?>
                    <?php if (!empty($product_model)) : ?>
                        <?php echo JText::_('COM_TIENDA_MODEL').": ".$product_model; ?><br/>
                    <?php endif; ?>
                    
				</td>
                <td style="text-align: center;">
                    <?php echo TiendaHelperBase::currency($product_price); ?>
                </td>
				<td style="text-align: center;">
				    <input type="button" onclick="tiendaRemoveRelationship(<?php echo $item->productrelation_id; ?>, 'existing_relationships', '<?php echo JText::_('Deleting'); ?>');" value="<?php echo JText::_('COM_TIENDA_DELETE'); ?>" />
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
</div>