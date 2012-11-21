<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $row = @$this -> row; ?>

<table class="table table-striped table-bordered">
    <tbody>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DISPLAY_DEFAULT_CATEGORY_IMAGE'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('use_default_category_image', '' , $this -> row -> get('use_default_category_image', '1')) ; ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DEFAULT_PRODUCT_IMAGE_HEIGHT'); ?>
            </th>
            <td><input type="text" name="product_img_height" value="<?php echo $this -> row -> get('product_img_height', ''); ?>" />
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DEFAULT_PRODUCT_IMAGE_WIDTH'); ?>
            </th>
            <td><input type="text" name="product_img_width" value="<?php echo $this -> row -> get('product_img_width', ''); ?>" />
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_RECREATE_PRODUCT_THUMBNAILS'); ?>
            </th>
            <td><a href="index.php?option=com_tienda&view=products&task=recreateThumbs" onClick="return confirm('<?php echo JText::_('Are you sure? Remember to save your new Configuration Values before doing this!'); ?>');"><?php echo JText::_('COM_TIENDA_CLICK_HERE_TO_RECREATE_THE_PRODUCT_THUMBNAILS'); ?> </a>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DEFAULT_CATEGORY_IMAGE_HEIGHT'); ?>
            </th>
            <td><input type="text" name="category_img_height" value="<?php echo $this -> row -> get('category_img_height', ''); ?>" />
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DEFAULT_CATEGORY_IMAGE_WIDTH'); ?>
            </th>
            <td><input type="text" name="category_img_width" value="<?php echo $this -> row -> get('category_img_width', ''); ?>" />
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_RECREATE_CATEGORY_THUMBNAILS'); ?>
            </th>
            <td><a href="index.php?option=com_tienda&view=categories&task=recreateThumbs" onClick="return confirm('<?php echo JText::_('Are you sure? Remember to save your new Configuration Values before doing this!'); ?>');"><?php echo JText::_('COM_TIENDA_CLICK_HERE_TO_RECREATE_THE_CATEGORY_THUMBNAILS'); ?> </a>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DEFAULT_MANUFACTURER_IMAGE_HEIGHT'); ?>
            </th>
            <td><input type="text" name="manufacturer_img_height" value="<?php echo $this -> row -> get('manufacturer_img_height', ''); ?>" />
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DEFAULT_MANUFACTURER_IMAGE_WIDTH'); ?>
            </th>
            <td><input type="text" name="manufacturer_img_width" value="<?php echo $this -> row -> get('manufacturer_img_width', ''); ?>" />
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_RECREATE_MANUFACTURER_THUMBNAILS'); ?>
            </th>
            <td><a href="index.php?option=com_tienda&view=manufacturers&task=recreateThumbs" onClick="return confirm('<?php echo JText::_('COM_TIENDA_ARE_YOU_SURE_REMEMBER_TO_SAVE_YOUR_NEW_CONFIGURATION_VALUES'); ?>');"><?php echo JText::_('COM_TIENDA_CLICK_HERE_TO_RECREATE_THE_MANUFACTURER_THUMBNAILS'); ?> </a>
            </td>
        </tr>
    </tbody>
</table>
