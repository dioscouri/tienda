<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $totals = @$this->row; ?>
<?php Tienda::load( 'TiendaHelperBase', 'helpers._base' ); ?>

<table class="adminlist">
<thead>
	<tr>
		<th colspan="4" style="text-align: left;"><?php echo JText::_('COM_TIENDA_ORDER_TOTALS'); ?></th>
	</tr>
</thead>
<tbody>
	<tr>
		<th style="width: 100px;" class="key">
			<?php echo JText::_('COM_TIENDA_SUBTOTAL'); ?>:
		</th>
		<td>
            <?php echo TiendaHelperBase::currency( @$totals->order_subtotal) ?>
        </td>
	</tr>
	<tr>
		<th style="width: 100px;" class="key">
			 <?php echo JText::_('COM_TIENDA_TAX'); ?>:
		</th>
		<td>
            <?php echo TiendaHelperBase::currency( @$totals->order_tax) ?>
		</td>
	</tr>
	<tr>
		<th style="width: 100px;" class="key">
			 <?php echo JText::_('COM_TIENDA_SHIPPING_COSTS'); ?>:
		</th>
		<td>
		    <?php echo TiendaHelperBase::currency( @$this->shipping_total->shipping_rate_price ); ?>
		</td>
	</tr>
    <tr>
        <th style="width: 100px;" class="key">
             <?php echo JText::_('COM_TIENDA_HANDLING_COSTS'); ?>:
        </th>
        <td>
            <?php echo TiendaHelperBase::currency( @$this->shipping_total->shipping_rate_handling ); ?>
        </td>
    </tr>
	<tr>
		<th style="width: 100px;" class="key">
			 <?php echo JText::_('COM_TIENDA_SHIPPING_TAX'); ?>:
		</th>
		<td>
		    <?php echo TiendaHelperBase::currency( @$this->shipping_total->shipping_tax_total ); ?>
		</td>
	</tr>
	<tr>
		<th style="width: 100px;" class="key">
			<label for="grand_total" style="color:#1432F2;font-size:16px;">
			 <?php echo JText::_('COM_TIENDA_GRAND_TOTAL'); ?>:
			</label>
		</th>
		<td style="color:#1432F2;font-size:16px;">
            <?php echo TiendaHelperBase::currency( @$totals->order_total ); ?>
		</td>
	</tr>					
	</tbody>
</table>
