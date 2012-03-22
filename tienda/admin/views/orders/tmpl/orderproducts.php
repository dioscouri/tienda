<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $items = (!empty($this->row->orderitems)) ? $this->row->orderitems : array(); ?>
<?php Tienda::load( 'TiendaHelperBase', 'helpers._base' ); ?>

        <table class="adminlist">
            <thead>
                <?php if (count(@$items)) : ?>
                <tr>
                    <th style="width: 20px;">
                        <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                    </th>
                    <th style="text-align: left;"><?php echo JText::_('Product'); ?></th>
                    <th style="width: 50px;"><?php echo JText::_('Quantity'); ?></th>
                    <th style="width: 50px;"><?php echo JText::_('Total'); ?></th>
                </tr>
                <?php endif; ?>
            </thead>
            <tbody>
            <?php $i=0; $k=0; ?>
            <?php foreach (@$items as $item) : ?>
                <tr class='row<?php echo $k; ?>'>
                    <td style="text-align: center;">
                        <input type="checkbox" id="cb<?php echo $i; ?>" name="products[]" value="<?php echo $item->product_id; ?>" onclick="isChecked(this.checked);" />
                    </td>
                    <td style="text-align: left;">
	                    <?php echo $item->orderitem_name; ?>
	                    <br />
	                    <b><?php echo JText::_('Price'); ?>:</b>
	                    <?php echo TiendaHelperBase::currency( $item->orderitem_price ); ?>
	                     
	                    <?php if (!empty($item->orderitem_sku)) : ?>
		                    <br />
		                    <b><?php echo JText::_('SKU'); ?>:</b>
		                    <?php echo $item->orderitem_sku; ?>
		                <?php endif; ?>
	                </td>
                    <td style="text-align: center; vertical-valign: top;">
                        <input name="quantity[<?php echo $item->product_id; ?>]" value="<?php echo $item->orderitem_quantity; ?>" style="width: 30px;" type="text" />
                    </td>
                    <td style="text-align: right; vertical-valign: top;">
                        <?php echo TiendaHelperBase::currency( $item->orderitem_final_price ); ?>
                    </td>
                </tr>
            <?php $i=$i+1; $k = (1 - $k); ?>
            <?php endforeach; ?>
            <?php 
            if (empty($items)) { ?>
	            <tr>
	            <td colspan="5" align="center">
	            <?php echo JText::_('No items in order'); ?>
	            </td>
	            </tr>
            <?php } ?>
            
            <?php if (count($items)) : ?>
                <tr>
                    <td colspan="2" style="text-align: left;">
                        <input onclick="tiendaRemoveProducts('<?php echo JText::_('Please Select an Item to Remove'); ?>');" value="<?php echo JText::_('Remove Selected'); ?>" class="button" type="button" />
                    </td>
                    <td colspan="2" style="text-align: right;">
                        <input onclick="tiendaUpdateProductQuantities();" value="<?php echo JText::_('Update Quantities'); ?>" class="button" style="float: right;" type="button" />
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>