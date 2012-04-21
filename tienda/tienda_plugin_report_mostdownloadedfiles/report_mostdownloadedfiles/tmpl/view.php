<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');?>
<?php $state = @$vars->state; ?>
<?php $items = @$vars->items; ?>
<?php $pagination = @$vars->pagination; ?>
    <table class="adminlist" style="clear: both;">
        <thead>
            <tr>
                <th style="width: 10px;">
                    <?php echo JText::_('COM_TIENDA_NUM'); ?>
                </th>
                <th style="width: 50px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_ID', "tbl.productfile_id", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: left;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_FILE_NAME_AND_PATH', "tbl.productfile_name", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 75px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_FILE_EXTENSION', "tbl.productfile_extension", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 150px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_ASSOCIATED_PRODUCT', "tbl_products.product_name", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 75px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_PURCHASE_REQUIRED', "tbl.purchase_required", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 50px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_MAX_DOWNLOAD', "tbl.max_download", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 50px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_DOWNLOADS', "file_downloads", @$state->direction, @$state->order ); ?>
                </th>
            </tr>           
        </thead>
        <tfoot>
			<tr>
				<td colspan="20">
					<div style="float: right; padding: 5px;"><?php echo @$pagination->getResultsCounter(); ?></div>					
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
                        <?php echo $item->productfile_id; ?>
                </td>
                <td style="text-align: left;">                	
                         <span style="font-weight:bold;"><?php echo $item->productfile_name; ?></span><br/>
                        <span style="font-style:italic;"><?php echo JText::_('COM_TIENDA_FILE_PATH')?>: </span><?php echo $item->productfile_path; ?>                    
                </td>
                <td style="text-align: center;">
                    <?php echo $item->productfile_extension; ?>
                </td>
                <td style="text-align: center;">
                	<a href="index.php?option=com_tienda&view=products&task=edit&id=<?php echo $item->product_id; ?>">
                         <?php echo $item->product_name; ?>                     
                    </a>                   
                </td>
                <td style="text-align: center;">
                	<?php if($item->purchase_required) { ?>
                    <img border="0" alt="Enabled" src="../media/com_tienda/images/tick.png">
                    <?php }else{ ?>
                    <img border="0" alt="Disabled" src="../media/com_tienda/images/publish_x.png">
                    <?php } ?>
                </td>
                <td style="text-align: center;">
                    <?php echo $item->max_download == "-1" ? JText::_('COM_TIENDA_UNLIMITED'): $item->max_download; ?>
                </td>
                 <td style="text-align: center;">
                    <?php echo $item->file_downloads; ?>
                </td>
            </tr>
            <?php ++$i; $k = (1 - $k); ?>
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
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
    