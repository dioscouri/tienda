<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php //echo @$vars->token; ?>

<?php $form = @$vars->form; ?>
<?php $items = @$vars->list; ?>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>

    <table>
        <tr>
            <td align="left" width="100%">
            </td>
            <td nowrap="nowrap">
                <input name="filter" value="<?php echo @$state->filter; ?>" />
                <button onclick="this.form.submit();"><?php echo JText::_('Search'); ?></button>
                <button onclick="tiendaFormReset(this.form);"><?php echo JText::_('Reset'); ?></button>
            </td>
        </tr>
    </table>

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
                	<?php echo TiendaGrid::sort( 'ID', "tbl.shipping_method_id", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: left;">
                	<?php echo TiendaGrid::sort( 'Name', "tbl.shipping_method_name", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'Tax Class', "tbl.tax_class_id", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
    	            <?php echo TiendaGrid::sort( 'Enabled', "tbl.shipping_method_enabled", @$state->direction, @$state->order ); ?>
                </th>
            </tr>
            <tr class="filterline">
                <th colspan="3">
                	<?php $attribs = array('class' => 'inputbox', 'size' => '1', 'onchange' => 'document.adminForm.submit();'); ?>
                	<div class="range">
	                	<div class="rangeline">
	                		<span class="label"><?php echo JText::_("From"); ?>:</span> <input id="filter_id_from" name="filter_id_from" value="<?php echo @$state->filter_id_from; ?>" size="5" class="input" />
	                	</div>
	                	<div class="rangeline">
	                		<span class="label"><?php echo JText::_("To"); ?>:</span> <input id="filter_id_to" name="filter_id_to" value="<?php echo @$state->filter_id_to; ?>" size="5" class="input" />
	                	</div>
                	</div>
                </th>
                <th style="text-align: left;">
                	<input id="filter_name" name="filter_name" value="<?php echo @$state->filter_name; ?>" size="15"/>
                	<?php echo TiendaSelect::shippingtype( @$state->filter_shippingtype, 'filter_shippingtype', $attribs, 'shippingtype', true ); ?>
                </th>
                <th>
                    <?php echo TiendaSelect::taxclass( @$state->filter_taxclass, 'filter_taxclass', $attribs, 'taxclass', true, false ); ?>
                </th>
                <th>
    	            <?php echo TiendaSelect::booleans( @$state->filter_enabled, 'filter_enabled', $attribs, 'enabled', true, 'Enabled State' ); ?>
                </th>
            </tr>
			<tr>
				<th colspan="20" style="font-weight: normal;">
					<div style="float: right; padding: 5px;"><?php //echo @$this->pagination->getResultsCounter(); ?></div>
					<div style="float: left;"><?php //echo @$this->pagination->getListFooter(); ?></div>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="20">
					<div style="float: right; padding: 5px;"><?php //echo @$this->pagination->getResultsCounter(); ?></div>
					<?php //echo @$this->pagination->getPagesLinks(); ?>
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
                        <?php JLoader::import( 'com_tienda.library.url', JPATH_ADMINISTRATOR.DS.'components' ); ?>
                        <?php JLoader::import( 'com_tienda.helpers.shipping', JPATH_ADMINISTRATOR.DS.'components' ); 
                        $id = JRequest::getInt('id', '0');
                        ?>
                        <span style="float: right;">[<?php 
                        echo TiendaUrl::popup( "index.php?option=com_tienda&view=shipping&task=view&id={$id}&shippingTask=setRates&format=raw&sid={$item->shipping_method_id}", "Set Rates" ); ?>]</span>
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
					<?php echo TiendaGrid::enable($item->shipping_method_enabled, $i, 'shipping_method_enabled.' ); ?>
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
	<input type="hidden" name="id" value="" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />

	<?php echo $form['validate']; ?>
</form>