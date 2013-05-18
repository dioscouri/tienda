<?php
	defined('_JEXEC') or die('Restricted access');
	JHTML::_('stylesheet', 'menu.css', 'media/com_tienda/css/');
	$state = @$this->state;
	$form = @$this->form;
	$items = @$this->items;
	$menu = TiendaMenu::getInstance();
?>

<div class='componentheading'>
	<span><?php echo JText::_('COM_TIENDA_MY_DOWNLOADS'); ?></span>
</div>

	<?php if ( $menu ) { $menu->display(); } ?>
		
<form action="<?php echo JRoute::_( @$form['action']."&limitstart=".@$state->limitstart )?>" method="post" name="adminForm" enctype="multipart/form-data">
    
    <table>
        <tr>
            <td align="left" width="100%">
                <?php echo JText::_('COM_TIENDA_SEARCH_BY_APPLYING_FILTERS'); ?>
            </td>
            <td nowrap="nowrap" style="text-align: right;">
                <input name="filter" value="<?php echo @$state->filter; ?>" />
                <button onclick="this.form.submit();"><?php echo JText::_('COM_TIENDA_SEARCH'); ?></button>
                <button onclick="tiendaFormReset(this.form);"><?php echo JText::_('COM_TIENDA_RESET'); ?></button>
            </td>
        </tr>
    </table>

    <table class="adminlist" style="clear: both;" >
        <thead>
            <tr class="filterline">
                <th>
                    <?php $attribs = array('class' => 'inputbox', 'size' => '1', 'onchange' => 'document.adminForm.submit();'); ?>
                </th>
                <th>
                </th>
                <th>
                    <?php echo TiendaSelect::downloadableproduct( JFactory::getUser()->id, @$state->filter_product_id, 'filter_product_id', $attribs ); ?>
                </th>
                <th>
                </th>                
            </tr>
            <tr>
                <th style="width: 50px;">
                </th>
                <th style="width: 200px; text-align: left;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_FILE', "filename", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 200px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_PRODUCT', "product_name", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 50px;">
                    <?php echo JText::_('COM_TIENDA_MAX_DOWNLOADS'); ?>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="20">
                    <div style="float: right; padding: 5px;"><?php echo @$this->pagination->getResultsCounter(); ?></div>
                    <?php echo @$this->pagination->getListFooter(); ?>
                </td>
            </tr>
        </tfoot>
        <tbody>
        <?php 
        	$k = 0;
        	foreach($this->items as $item) :
        	$downloadable = 1; // 1 -> limited download, 0 -> out of limit, -1 ->  unlimited
        	switch($item->productdownload_max)
        	{
        		case -1 :
        			$downloadable = JText::_('COM_TIENDA_UNLIMITED');
        			break;
        		case 0 : 
        			$downloadable = JText::_('COM_TIENDA_NOT_AVAILABLE');
        			break;
        		default:
        			$downloadable = $item->productdownload_max;
        	}
        ?>
        <tr class="row<?php echo $k;?>">
        	<td style="text-align: center;">
                <span class="productfile_image">
                    <a href="<?php echo JRoute::_( 'index.php?option=com_tienda&view=products&task=downloadfile&format=raw&id='.$item->productfile_id."&product_id=".$item->product_id); ?>">
                        <img src="<?php echo Tienda::getURL( 'images' )."download.png"; ?>" alt="<?php echo JText::_('COM_TIENDA_DOWNLOAD') ?>" style="height: 24px; padding: 5px; vertical-align: middle;" />
                    </a>
                </span>
        	</td>
        	<td style="text-align: left;">
        		<?php if($item->productdownload_max != 0 ) : ?>
			        <div class="productfile">
			            <span class="productfile_link" style="vertical-align: middle;" >
			                <a href="<?php echo JRoute::_( 'index.php?option=com_tienda&view=products&task=downloadfile&format=raw&id='.$item->productfile_id."&product_id=".$item->product_id); ?>"><?php echo $item->filename; ?></a>
			            </span>
			        </div>
		        <?php else: ?>
			        <?php echo $item->filename; ?>
		        <?php endif;?>
		      </td>
		      <td style="text-align:center;">
		      	<a href="<?php echo JRoute::_('index.php?option=com_tienda&view=productdownloads&filter_product_id='.$item->product_id);?>"><?php echo $item->product_name; ?></a>
		      </td>
        	<td style="text-align:center;"><?php echo $downloadable;?></td>
        </tr>
        <?php 
        	$k = 1 - $k;
        	endforeach;?>        
        </tbody>
    </table>

    <input type="hidden" name="id" value="" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="" />
    <input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
    <input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
    
    <?php echo $this->form['validate']; ?>
</form>