<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $items = (!empty($this->row->orderitems)) ? $this->row->orderitems : array(); ?>
<?php Tienda::load( 'TiendaHelperBase', 'helpers._base' ); ?>

        <table class="table table-striped table-bordered">
            <thead>
                <?php if (count(@$items)) : ?>
                <tr>
                    <th style="width: 20px;">
                        <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                    </th>
                    <th style="text-align: left;"><?php echo JText::_('COM_TIENDA_PRODUCT'); ?></th>
                    <th style="width: 50px;"><?php echo JText::_('COM_TIENDA_QUANTITY'); ?></th>
                    <th style="width: 50px;"><?php echo JText::_('COM_TIENDA_TOTAL'); ?></th>
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
	                    <b><?php echo JText::_('COM_TIENDA_PRICE'); ?>:</b>
	                    <?php echo TiendaHelperBase::currency( $item->orderitem_price ); ?>
	                     
	                    <?php if (!empty($item->orderitem_sku)) : ?>
		                    <br />
		                    <b><?php echo JText::_('COM_TIENDA_SKU'); ?>:</b>
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
	            <?php echo JText::_('COM_TIENDA_NO_ITEMS_IN_ORDER'); ?>
	            </td>
	            </tr>
            <?php } ?>
            
            <?php if (count($items)) : ?>
                <tr>
                    <td colspan="2" style="text-align: left;">
                        <input class="btn btn-danger" onclick="tiendaRemoveProducts('<?php echo JText::_('COM_TIENDA_PLEASE_SELECT_AN_ITEM_TO_REMOVE'); ?>');" value="<?php echo JText::_('COM_TIENDA_REMOVE SELECTED'); ?>" class="button" type="button" />
                    </td>
                    <td colspan="2" style="text-align: right;">
                        <input class="btn btn-primary"  onclick="tiendaUpdateProductQuantities();" value="<?php echo JText::_('COM_TIENDA_UPDATE_QUANTITIES'); ?>" class="button" style="float: right;" type="button" />
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>