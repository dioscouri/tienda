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
                <?php echo JText::_('COM_TIENDA_PRIMARY_IMAGE'); ?>:
            </th>
            <td class="dsc-value">
                <input name="product_full_image" id="product_full_image" value="<?php echo @$row->product_full_image; ?>" type="text" class="input-xxlarge" />
            </td>
        </tr>    
        <tr>
            <th class="dsc-key">
                <?php echo JText::_('COM_TIENDA_UPLOAD_NEW_IMAGES'); ?>
            </th>
            <td>
                <div class="help-block muted">
                    <?php echo JText::_('COM_TIENDA_UPLOAD_ZIP_IMAGES_MESSAGE'); ?>
                </div>
                
                <div class="control-group">
                    <input id="new_product_full_images" name="product_full_image_new[]" type="file" multiple="multiple" <?php if (empty($row->product_id) || !in_array($multiupload_script, array('0', 'uploadify'))) { ?> onchange="tiendaMakeFileList();" size="40" <?php } ?> />
                </div>
                
                <div class="help-block dsc-clear">
                    <h4 id="fileList-title" style="display: none;"><?php echo JText::_( "COM_TIENDA_FILES_SELECTED" ); ?></h4>
                    <ul id="fileList"></ul>
                    <div id="queue"></div>
                </div>
                
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

<div id="form-gallery">
<?php 
if (!empty($row->product_id)) {
    $this->setLayout( 'form_gallery' ); echo $this->loadTemplate();
}
?>
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {
        Tienda.bindProductGalleryLinks();
    });

    <?php if (!empty($row->product_id) && in_array($multiupload_script, array('0', 'uploadify'))) { ?>
	jQuery(document).ready(function() {
		jQuery('#new_product_full_images').uploadifive({
			'auto' : true,
			'removeCompleted': true,
			'method' : 'post',
			'formData' : {
			   'option':'com_tienda',
			   'view':'products',
			   'task':'uploadifyImage',
			   'format':'raw',
			   'product_id': '<?php echo $row->product_id ?>',
			   '<?php echo JSession::getFormToken(); ?>': '1'
			},
			'queueID'          : 'queue',
			'uploadScript'     : 'index.php',
			'onQueueComplete' : function() { Tienda.refreshProductGallery(<?php echo $row->product_id; ?>); jQuery('#new_product_full_images').uploadifive('clearQueue'); },
			'onFallback'   : function() { tiendaJQ('#new_product_full_images').on('change', function(){ tiendaMakeFileList(); }); },
			'onInit': function() { }
		});
	});
	<?php } ?>

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
