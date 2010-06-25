<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php $form = @$vars->form; ?>
<?php $items = @$vars->list; ?>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>

	<table class="adminlist" style="clear: both;">
		<thead>
            <tr>
                <th style="width: 5px;">
                	<?php echo JText::_("Num"); ?>
                </th>
                <th style="width: 20px;">
                	<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                </th>
                <th style="width: 50px;">
                	<?php echo JText::_('ID'); ?>
                </th>
                <th style="text-align: left;">
                	<?php echo JText::_( 'Name' ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo JText::_( 'Tax Class' ); ?>
                </th>
                <th style="width: 100px;">
    	            <?php echo JText::_( 'Enabled' ); ?>
                </th>
            </tr>
		</thead>
        <tfoot>
            <tr>
                <td colspan="20">
                    &nbsp;
                </td>
            </tr>
        </tfoot>
        <tbody>
		<?php $i=0; $k=0; ?>
        <?php foreach (@$items as $item) : ?>
            <tr class='row<?php echo $k; ?>'>
				<td align="center">
					<?php echo $i + 1; ?>
				</td>
				<td style="text-align: center;">
					<?php echo TiendaGrid::checkedout( $item, $i, 'shipping_method_id' ); ?>
				</td>
				<td style="text-align: center;">
					<a href="<?php echo $item->link; ?>">
						<?php echo $item->shipping_method_id; ?>
					</a>
				</td>
				<td style="text-align: left;">
                    <a href="<?php echo $item->link; ?>">
                        <?php echo $item->shipping_method_name; ?>
                    </a>
                    <div class="shipping_rates">
                        <?php Tienda::load( 'TiendaUrl', 'library.url' ); ?>
                        <?php Tienda::load( 'TiendaHelperShipping', 'helpers.shipping' ); 
                        $id = JRequest::getInt('id', '0');
                        ?>
                        <span style="float: right;">[<?php 
                        echo TiendaUrl::popup( "index.php?option=com_tienda&view=shipping&task=view&id={$id}&shippingTask=setRates&tmpl=component&sid={$item->shipping_method_id}", "Set Rates" ); ?>]</span>
                        <?php 
                        if ($shipping_method_type = TiendaHelperShipping::getType($item->shipping_method_type))
                        {
                        	echo "<b>".JText::_( "Type" )."</b>: ".$shipping_method_type->title; 
                        }
                        if ($item->subtotal_minimum > '0')
                        {
                        	?>
                        	<br/>
                        	<?php echo "<b>".JText::_( "Minimum Order Required" )."</b> "; ?>:
                            <?php echo TiendaHelperBase::currency( $item->subtotal_minimum ); ?>
                            <?php	
                        }
                        ?>
                    </div>
				</td>
				<td style="text-align: center;">
				    <?php echo $item->tax_class_name; ?>
				</td>
				<td style="text-align: center;">
					<?php echo TiendaGrid::boolean( $item->shipping_method_enabled ); ?>
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
	</table>

	<input type="hidden" name="order_change" value="0" />
	<input type="hidden" name="sid" value=" <?php echo $vars->sid; ?>" />
	<input type="hidden" name="shippingTask" value="_default" />
	<input type="hidden" name="task" value="view" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />

</form>