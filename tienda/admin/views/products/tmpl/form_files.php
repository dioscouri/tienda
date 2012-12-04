<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
$form = @$this->form;
$row = @$this->row;
$helper_product = new TiendaHelperProduct();
?>
<table class="table table-striped table-bordered">

    <?php 
    if (empty($row->product_id))
    {
        // doing a new product, so display a notice
        ?>
    <tr>
        <td class="dsc-key"><?php echo JText::_('COM_TIENDA_PRODUCT_FILES'); ?>:</td>
        <td>
            <div class="note well">
                <?php echo JText::_('COM_TIENDA_CLICK_APPLY_TO_BE_ABLE_TO_ADD_FILES_TO_THE_PRODUCT'); ?>
            </div>
        </td>
    </tr>
    <?php
    }
    else
    {
        // display lightbox link to manage files
        ?>
    <tr>
        <td class="dsc-key"><?php echo JText::_('COM_TIENDA_PRODUCT_FILES'); ?>:</td>
        <td><?php
        Tienda::load( 'TiendaUrl', 'library.url' );
        ?> [<?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=products&task=setfiles&id=".$row->product_id."&tmpl=component", JText::_('COM_TIENDA_MANAGE_FILES') ); ?>] <?php $files = $helper_product->getFiles( $row->product_id ); ?>
            <div id="current_files">
                <?php foreach (@$files as $file) : ?>
                [<a href="<?php echo "index.php?option=com_tienda&view=productfiles&task=delete&cid[]=".$file->productfile_id."&return=".base64_encode("index.php?option=com_tienda&view=products&task=edit&id=".$row->product_id); ?>"> <?php echo JText::_('COM_TIENDA_REMOVE'); ?>
                </a>] [<a href="<?php echo "index.php?option=com_tienda&view=productfiles&task=downloadfile&id=".$file->productfile_id."&product_id=".$row->product_id; ?>"> <?php echo JText::_('COM_TIENDA_DOWNLOAD');?>
                </a>]
                <?php echo $file->productfile_name; ?>
                <br />
                <?php endforeach; ?>
            </div>
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top; width: 100px; text-align: right;" class="dsc-key"><?php echo JText::_('COM_TIENDA_PRODUCT_FILES_PATH_OVERRIDE'); ?>:</td>
        <td><input name="product_files_path" id="product_files_path" value="<?php echo @$row->product_files_path; ?>" size="75" maxlength="255" type="text" />
            <div class="note well">
                <?php echo JText::_('COM_TIENDA_IF_NO_FILE_PATH_OVERRIDE_IS_SPECIFIED_MESSAGE'); ?>
                <ul>
                    <li>/images/com_tienda/files/[SKU]</li>
                    <li>/images/com_tienda/files/[ID]</li>
                </ul>
                <?php echo JText::_('COM_TIENDA_CHANGING_FILE_PATH_NOTE'); ?>
            </div>
        </td>
    </tr>

    <?php
    }
    ?>
</table>
