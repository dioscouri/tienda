<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $row = @$this -> row; ?>

<table class="table table-striped table-bordered">
    <tbody>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DISPLAY_PRODUCT_SORT_BY'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('display_sort_by', 'class="inputbox"', $this -> row -> get('display_sort_by', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_PRODUCT_SORTINGS'); ?>
            </th>
            <td>
            <textarea rows="5" name="display_sortings"><?php echo $this -> row -> get('display_sortings', 'Name|product_name,Price|price,Rating|product_rating'); ?></textarea>
            </td>
            <td><?php echo JText::_('COM_TIENDA_PRODUCT_SORTINGS_DESC')?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DISPLAY_WORKING_IMAGE_PRODUCT'); ?>
            </th>
            <td><?php echo TiendaSelect::btbooleanlist('dispay_working_image_product', 'class="inputbox"', $this -> row -> get('dispay_working_image_product', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_WIDTH_OF_UI_LIGHTBOXES'); ?>
            </th>
            <td><input type="text" name="lightbox_width" value="<?php echo $this -> row -> get('lightbox_width', '800'); ?>" class="inputbox" size="10" />
            </td>
            <td><?php echo JText::_('COM_TIENDA_WIDTH_OF_UI_LIGHTBOXES_DESC'); ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_HEIGHT_OF_UI_LIGHTBOXES'); ?>
            </th>
            <td><input type="text" name="lightbox_height" value="<?php echo $this -> row -> get('lightbox_height', '480'); ?>" class="inputbox" size="10" />
            </td>
            <td><?php echo JText::_('COM_TIENDA_HEIGHT_OF_UI_LIGHTBOXES_DESC'); ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_CONFIG_PROCESS_CONTENT_PLUGIN_PRODUCT_DESC'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('content_plugins_product_desc', 'class="inputbox"', $this -> row -> get('content_plugins_product_desc', '0')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_SHOW_DIOSCOURI_LINK_IN_FOOTER'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist( 'show_linkback', 'class="inputbox"', $this -> row -> get('show_linkback', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_YOUR_DIOSCOURI_AFFILIATE_ID'); ?>
            </th>
            <td><input type="text" name="amigosid" value="<?php echo $this -> row -> get('amigosid', ''); ?>" class="inputbox" />
            </td>
            <td><a href='http://www.dioscouri.com/' target='_blank'> <?php echo JText::_('COM_TIENDA_NO_AMIGOSID'); ?>
            </a>
            </td>
        </tr>
    </tbody>
</table>
