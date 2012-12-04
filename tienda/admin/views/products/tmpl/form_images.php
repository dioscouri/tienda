<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
$form = @$this->form;
$row = @$this->row;
$helper_product = new TiendaHelperProduct();
$multiupload_script = $this->defines->get( 'multiupload_script', 0 );
?>

<fieldset>
    <table class="table">
        <tr>
            <th class="dsc-key">
                <?php echo JText::_('COM_TIENDA_UPLOAD_NEW_IMAGES'); ?>
            </th>
            <td>
                <div class="help-block muted">
                    <?php echo JText::_('COM_TIENDA_UPLOAD_ZIP_IMAGES_MESSAGE'); ?>
                </div>
                
                <?php if (!empty($row->product_id) && in_array($multiupload_script, array('0', 'uploadify'))) { ?>
                    
                    <div id="uploadifyImage control">
                        <div id="queue"></div>
                        <input type="file" multiple="true" name="uploadify_image" id="uploadify_file_upload">
                    </div>
                    
                <?php } else { ?>
                    
                    <div class="control-group">
                        <input id="new_product_full_images" name="product_full_image_new[]" type="file" multiple="multiple" onchange="tiendaMakeFileList();" size="40" />
                    </div>
                    
                    <div class="help-block dsc-clear">
                        <h4 id="fileList-title" style="display: none;"><?php echo JText::_( "COM_TIENDA_FILES_SELECTED" ); ?></h4>
                        <ul id="fileList"></ul>
                    </div>
                    
                    <script type="text/javascript">
                    function tiendaMakeFileList() {
                    	var input = document.getElementById("new_product_full_images");
                    	var ul = document.getElementById("fileList");
                    	var title = document.getElementById("fileList-title");
                    	while (ul.hasChildNodes()) {
                    		ul.removeChild(ul.firstChild);
                    	}
                    	for (var i = 0; i < input.files.length; i++) {
                    		var li = document.createElement("li");
                    		li.innerHTML = input.files[i].name;
                    		ul.appendChild(li);
                    	}
                    	if(!ul.hasChildNodes()) {
                    		var li = document.createElement("li");
                    		li.innerHTML = 'No Files Selected';
                    		ul.appendChild(li);
                    		title.setStyle('display', 'block');
                    	} else {
                    	    title.setStyle('display', 'block');
                    	}
                    }
                    </script> 
	            <?php } ?>
            </td>
        </tr>
        <tr>
            <th class="dsc-key">
                <?php echo JText::_('COM_TIENDA_IMAGES_GALLERY_PATH_OVERRIDE'); ?>
            </th>
            <td>
                <textarea name="product_images_path" id="product_images_path"><?php echo @$row->product_images_path; ?></textarea>
                <div class="help-block muted">
                    <?php echo JText::_('COM_TIENDA_IF_NO_IMAGE_PATH_OVERRIDE_IS_SPECIFIED_MESSAGE'); ?>
                    <ul>
                        <li>/images/com_tienda/products/[SKU]</li>
                        <li>/images/com_tienda/products/[ID]</li>
                    </ul>
                </div>
            </td>
        </tr>
    </table>
</fieldset>

<?php 
if (!empty($row->product_id)) { ?>
    <div id="form-gallery">
    <?php $this->setLayout( 'form_gallery' ); echo $this->loadTemplate(); ?>
    </div>
    <?php
}
?>

<script type="text/javascript">
    jQuery(document).ready(function() {
        Tienda.bindProductGalleryLinks();
    });

    <?php $timestamp = time();?>

    <?php if (!empty($row->product_id) && in_array($multiupload_script, array('0', 'uploadify'))) { ?>
	jQuery(document).ready(function() {
		jQuery('#uploadify_file_upload').uploadifive({
			'auto' : true,
			'method' : 'post',
			'formData' : {
			   'option':'com_tienda',
			   'view':'products',
			   'task':'uploadifyImage',
			   'format':'raw',
			   'product_id': '<?php echo $row->product_id ?>'
			},
			'queueID'          : 'queue',
			'uploadScript'     : '<?php echo JURI::getInstance()->root(true); ?>/index.php',
			'onQueueComplete' : function() { Tienda.refreshProductGallery(<?php echo $row->product_id; ?>); jQuery('#uploadify_file_upload').uploadifive('clearQueue'); }
		});
	});
	<?php } ?>
</script>
	
<?php /* ?>
<table class="table">
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
        <td style="vertical-align: top; width: 100px; text-align: right;" class="dsc-key"><?php echo JText::_('COM_TIENDA_IMAGES_GALLERY_PATH_OVERRIDE'); ?>:</td>
        <td><input name="product_images_path" id="product_images_path" value="<?php echo @$row->product_images_path; ?>" size="75" maxlength="255" type="text" />
            <div class="dsc-tip">
                <?php echo JText::_('COM_TIENDA_IF_NO_IMAGE_PATH_OVERRIDE_IS_SPECIFIED_MESSAGE'); ?>
                <ul>
                    <li>/images/com_tienda/products/[SKU]</li>
                    <li>/images/com_tienda/products/[ID]</li>
                </ul>
            </div>
        </td>
    </tr>
</table>
*/ ?>
