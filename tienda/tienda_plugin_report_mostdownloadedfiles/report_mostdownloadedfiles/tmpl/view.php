<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');?>
<?php $state = @$vars->state; ?>
<?php $items = @$vars->items; ?>

    <table class="adminlist" style="clear: both;">
        <thead>
            <tr>
                <th style="width: 10px;">
                    <?php echo JText::_("Num"); ?>
                </th>
                <th style="width: 50px;">
                    <?php echo JText::_("ID"); ?>
                </th>
                <th style="text-align: left;">
                    <?php echo JText::_("FILE NAME"); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo JText::_("FILE EXTENSION"); ?>
                </th>
                <th style="width: 150px;">
                    <?php echo JText::_("ASSOCIATED PRODUCT"); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo JText::_("PURCHASE REQUIRED"); ?>
                </th>
                <th style="width: 50px;">
                    <?php echo JText::_("MAX DOWNLOAD"); ?>
                </th>
                <th style="width: 50px;">
                    <?php echo JText::_("DOWNLOADS"); ?>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="20">

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
                        <?php echo $item->productfile_name; ?>                     
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
                    <img border="0" alt="Enabled" src="/dioscouri/media/com_tienda/images/tick.png">
                    <?php }else{ ?>
                    <img border="0" alt="Disabled" src="/dioscouri/media/com_tienda/images/publish_x.png">
                    <?php } ?>
                </td>
                <td style="text-align: center;">
                    <?php echo $item->max_download == "-1" ? JText::_("UNLIMITED"): $item->max_download; ?>
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
                    <?php echo JText::_('No items found'); ?>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
