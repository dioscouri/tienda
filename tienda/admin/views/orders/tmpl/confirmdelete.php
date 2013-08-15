<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php Tienda::load( 'TiendaHelperBase', 'helpers._base' ); ?>

<div class="note_pink">
    <span class="alert"><?php echo JText::_('COM_TIENDA_WARNING'); ?></span>
    <?php echo JText::_('COM_TIENDA_DELETING_ORDERS_CANNOT_BE_UNDONE'); ?>
</div>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

	<table class="table table-striped table-bordered" style="clear: both;">
		<thead>
            <tr>
                <th style="width: 20px;">
                	<?php echo JText::_('COM_TIENDA_ID'); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo JText::_('COM_TIENDA_DATE'); ?>
                </th>
                <th style="text-align: left;">
                	<?php echo JText::_('COM_TIENDA_ORDER'); ?>
                </th>
				<th>
                	<?php echo JText::_('COM_TIENDA_CUSTOMER'); ?>
                </th>
                <th>
                    <?php echo JText::_('COM_TIENDA_STATUS'); ?>
                </th>
            </tr>
		</thead>
        <tbody>
		<?php $i=0; $k=0; ?>
        <?php foreach (@$items as $item) : ?>
            <tr class='row<?php echo $k; ?>'>
				<td style="text-align: center;">
                    <?php echo $item->order_id; ?>
					<input type="hidden" name="cid[]" value="<?php echo $item->order_id; ?>"/>
				</td>
				<td>
				    <?php echo JHTML::_('date', $item->created_date, Tienda::getInstance()->get('date_format')); ?>
				</td>
				<td style="text-align: left;">
                    <?php 
                    echo "<b>".JText::_('COM_TIENDA_ORDER_ID')."</b>: ".$item->order_id."<br>";
                    echo "<b>".JText::_('COM_TIENDA_ORDER_AMOUNT')."</b>: ".TiendaHelperBase::currency( $item->order_total )."<br>";
                    ?>
				</td>
                <td>
                    <?php echo $item->user_name .' [ '.$item->user_id.' ]'; ?>
                    &nbsp;&nbsp;&bull;&nbsp;&nbsp;<?php echo $item->email .' [ '.$item->user_username.' ]'; ?>
                    <br/>
                    <b><?php echo JText::_('COM_TIENDA_SHIP_TO'); ?></b>:
                    <?php 
                    if (empty($item->shipping_address_1)) 
                    {
                       echo JText::_('COM_TIENDA_UNDEFINED_SHIPPING_ADDRESS'); 
                    }
                       else
                    {
                        echo $item->shipping_address_1.", ";
                        echo $item->shipping_address_2 ? $item->shipping_address_2.", " : "";
                        echo $item->shipping_city.", ";
                        echo $item->shipping_zone_name." ";
                        echo $item->shipping_postal_code." ";
                        echo $item->shipping_country_name;
                    }
                    ?>
                </td>								
				<td style="text-align: center;">
                    <?php echo $item->order_state_name; ?>
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
					&nbsp;
				</td>
			</tr>
		</tfoot>
	</table>

	<input type="hidden" name="task" id="task" value="" />
	<input type="hidden" name="boxchecked" value="1" />
	<input type="hidden" name="confirmdelete" value="1" />
	<?php echo $this->form['validate']; ?>
</form>