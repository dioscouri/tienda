<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $totals = @$this->row; ?>
<?php JLoader::import( 'com_tienda.helpers._base', JPATH_ADMINISTRATOR.DS.'components' ); ?>

<table class="adminlist">
<thead>
	<tr>
		<th colspan="4" style="text-align: left;"><?php echo JText::_( "Order Totals" ); ?></th>
	</tr>
</thead>
<tbody>
	<tr>
		<th style="width: 100px;" class="key">
			<?php echo JText::_( 'Subtotal' ); ?>:
		</th>
		<td>
            <?php echo TiendaHelperBase::currency( @$totals->order_subtotal) ?>
        </td>
	</tr>
	<tr>
		<th style="width: 100px;" class="key">
			 <?php echo JText::_( 'Tax' ); ?>:
		</th>
		<td>
            <?php echo TiendaHelperBase::currency( @$totals->order_tax) ?>
		</td>
	</tr>
	<tr>
		<th style="width: 100px;" class="key">
			 <?php echo JText::_( 'Shipping costs' ); ?>:
		</th>
		<td>
		    <?php echo TiendaHelperBase::currency( @$this->shipping_total->shipping_rate_price ); ?>
		</td>
	</tr>
    <tr>
        <th style="width: 100px;" class="key">
             <?php echo JText::_( 'Handling costs' ); ?>:
        </th>
        <td>
            <?php echo TiendaHelperBase::currency( @$this->shipping_total->shipping_rate_handling ); ?>
        </td>
    </tr>
	<tr>
		<th style="width: 100px;" class="key">
			 <?php echo JText::_( 'Shipping tax' ); ?>:
		</th>
		<td>
		    <?php echo TiendaHelperBase::currency( @$this->shipping_total->shipping_tax_total ); ?>
		</td>
	</tr>
	<tr>
		<th style="width: 100px;" class="key">
			<label for="grand_total" style="color:#1432F2;font-size:16px;">
			 <?php echo JText::_( 'Grand total' ); ?>:
			</label>
		</th>
		<td style="color:#1432F2;font-size:16px;">
            <?php echo TiendaHelperBase::currency( @$totals->order_total ); ?>
		</td>
	</tr>					
	</tbody>
</table>
<?php //echo Tienda::dump( $this->row ); ?>