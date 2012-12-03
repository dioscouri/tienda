<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
$form = @$this->form;
$row = @$this->row;
$helper_product = new TiendaHelperProduct();
?>

<div class="well options">
    <legend>
        <?php echo JText::_('COM_TIENDA_IMAGES'); ?>
    </legend>
    <table class="table table-striped table-bordered" style="width: 100%;">
        <tr>
            <td style="width: 100px; text-align: right;" class="dsc-key"><label for="product_full_image"> <?php echo JText::_('COM_TIENDA_CURRENT_DEFAULT_IMAGE'); ?>:
            </label>
            </td>
            <td>
                <div id='default_image_name'>
                    <?php
                    echo $row->product_full_image;
                    ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="width: 100px; text-align: right;" class="dsc-key"><label for="product_image_gallery"> <?php echo JText::_('COM_TIENDA_CURRENT_IMAGES'); ?>:
            </label>
            </td>
            <td>[ <?php
            echo TiendaUrl::popup( "index.php?option=com_tienda&view=products&task=viewGallery&id=".@$row->product_id."&tmpl=component", JText::_('COM_TIENDA_VIEW_GALLERY') );
            ?> ] <br /> <?php $images = $helper_product->getGalleryImages( $helper_product->getGalleryPath( @$row->product_id ) ); ?> <?php foreach (@$images as $image) : ?> [<a href="<?php echo "index.php?option=com_tienda&view=products&task=deleteImage&product_id=".@$row->product_id."&image=".$image."&return=".base64_encode("index.php?option=com_tienda&view=products&task=edit&id=".@$row->product_id); ?>"> <?php echo JText::_('COM_TIENDA_REMOVE'); ?>
            </a>] <?php echo $image; ?> <br /> <?php endforeach; ?>
            </td>
        </tr>
        <tr>
            <td style="width: 100px; text-align: right;" class="dsc-key"><label for="product_full_image_new"> <?php echo JText::_('COM_TIENDA_UPLOAD_NEW_IMAGE'); ?>:
            </label>
            </td>
            <td>

                <div id="uploadifyImage">
                    <?php echo  DSCImage::uploadifyElement('uploadify_file_upload','uploadify_image')?>

                </div>
                <div class="note well" style="clear: both">
                    <?php echo JText::_('COM_TIENDA_UPLOAD_ZIP_IMAGES_MESSAGE'); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top; width: 100px; text-align: right;" class="dsc-key"><?php echo JText::_('COM_TIENDA_IMAGES_GALLERY_PATH_OVERRIDE'); ?>:</td>
            <td><input name="product_images_path" id="product_images_path" value="<?php echo @$row->product_images_path; ?>" size="75" maxlength="255" type="text" />
                <div class="note well">
                    <?php echo JText::_('COM_TIENDA_IF_NO_IMAGE_PATH_OVERRIDE_IS_SPECIFIED_MESSAGE'); ?>
                    <ul>
                        <li>/images/com_tienda/products/[SKU]</li>
                        <li>/images/com_tienda/products/[ID]</li>
                    </ul>
                </div>
            </td>
        </tr>
    </table>
</div>
