<?php defined('_JEXEC') or die('Restricted access');?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');?>
<?php $items = @$this -> items;?>
<div class="cartitems">

	<table class="adminlist" style="clear: both;">
		<thead>
			<tr>				
				<th style="width: 20px;">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
				</th>				
				<th colspan="2" style="text-align: left;">
				<?php echo JText::_('COM_TIENDA_PRODUCT');?>
				</th>
				<th style="width: 50px;">
				<?php echo JText::_('COM_TIENDA_QUANTITY');?>
				</th>
				<th style="width: 50px;">
				<?php echo JText::_('COM_TIENDA_TOTAL');?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php $i = 0;
			$k = 0;
			$subtotal = 0;
			?>
			<?php foreach ($items as $item) : ?>

			<tr class="row<?php echo $k;?>">				
				<td style="border-bottom: 1px solid #E5E5E5; width: 20px; text-align: center;">			
				<input type="checkbox" id="cb<?php echo $i;?>" name="cid[<?php echo $item -> cart_id;?>]" value="<?php echo $item -> product_id;?>" onclick="isChecked(this.checked);" />
				</td>				
				<td style="border-bottom: 1px solid #E5E5E5; text-align: center; width: 50px;">
				<?php echo TiendaHelperProduct::getImage($item -> product_id, 'id', $item -> product_name, 'full', false, false, array('width' => 48));?>
				</td>
				<td style="border-bottom: 1px solid #E5E5E5;">
					<a href="index.php?option=com_tienda&view=products&task=edit&id=<?php echo $item->product_id;?>">
					<?php echo $item -> product_name;?>
					</a>
					<br/>
					<?php if (!empty($item->attributes_names)) : ?>
	                	<?php echo $item->attributes_names; ?>
	                	<br/>
	                <?php endif; ?>
	                    <input name="product_attributes[<?php echo $item->cart_id; ?>]" value="<?php echo $item->product_attributes; ?>" type="hidden" />   
				</td>
				<td style="border-bottom: 1px solid #E5E5E5; width: 50px; text-align: center;">
				<?php $type = 'text';
					if($item -> product_parameters -> get('hide_quantity_cart') == '1') {
						$type = 'hidden';
						echo $item -> product_qty;
					}
				?>
				
				<input name="quantities[<?php echo $item -> cart_id;?>]" type="<?php echo $type;?>" size="3" maxlength="3" value="<?php echo $item -> product_qty;?>" />

				<!-- Keep Original quantity to check any update to it when going to checkout -->
				<input name="original_quantities[<?php echo $item -> cart_id;?>]" type="hidden" value="<?php echo $item -> product_qty;?>" />
				</td>
				<td style="border-bottom: 1px solid #E5E5E5; text-align: right;">
				<?php $product_total = ($item -> product_price) * ($item -> product_qty);?>
				<?php echo TiendaHelperBase::currency($product_total);?>
				</td>
			</tr>
			<?php $subtotal = $subtotal + $product_total;?>
			<?php endforeach;?>			
			<tr>
				<td colspan="3" style="border-bottom: 1px solid #E5E5E5; text-align: left;">
				<input type="submit" class="button" value="<?php echo JText::_('COM_TIENDA_REMOVE SELECTED');?>" onclick="tiendaSubmitForm('update')" name="remove" />
				</td>
				<td colspan="2" style="border-bottom: 1px solid #E5E5E5; ">
				<input style="float: right;" type="submit" class="button" value="<?php echo JText::_('COM_TIENDA_UPDATE_QUANTITIES');?>" onclick="tiendaSubmitForm('update')" name="update" />
				</td>
			</tr>
			<tr>
				<td colspan="4" style="border-bottom: 1px solid #E5E5E5;  text-align: right; font-weight: bold;">
				<?php echo JText::_('COM_TIENDA_SUBTOTAL');?>
				</td>
				<td style="border-bottom: 1px solid #E5E5E5; text-align: right;">
				<?php echo TiendaHelperBase::currency($subtotal);?>
				</td>
			</tr>
			
		</tbody>
	</table>
</div>
<input type="hidden" name="boxchecked" value="" />