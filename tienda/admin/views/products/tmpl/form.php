<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('stylesheet', 'uploadify.css', 'media/com_tienda/css/'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('script', 'tienda_admin.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('script', 'Stickman.MultiUpload.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('script', 'swfobject.js', 'media/com_tienda/js/uploadify/'); ?>
<?php // JHTML::_('script', 'jquery-1.5.1.min.js', 'media/com_tienda/js/uploadify/'); ?>
<?php JHTML::_('script', 'jquery.uploadify.v2.1.4.min.js', 'media/com_tienda/js/uploadify/'); ?>
<?php JHTML::_('behavior.tooltip'); ?>

<?php $form = @$this->form; ?>
<?php
 // in joomla 2.5 this is causing admin forms to have encoded entities in the html forms
 // $row = @$this->row; JFilterOutput::objectHTMLSafe( $row ); ?>

<?php 
 $row = @$this->row;  ?>
<?php

Tienda::load( 'TiendaUrl', 'library.url' );
Tienda::load( "TiendaHelperProduct", 'helpers.product' );
$helper_product = TiendaHelperBase::getInstance( 'product' );
?>
<form id="adminForm" action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >

    <fieldset>
    <legend><?php echo JText::_('COM_TIENDA_BASIC_INFORMATION'); ?></legend>
        <div style="float: left; margin:5px;">
        <table class="table table-striped table-bordered">
            <tr>
                <td style="width: 100px; text-align: right;" class="dsc-key">
                    <?php echo JText::_('COM_TIENDA_NAME'); ?>:
                </td>
                <td class="dsc-value">
                    <input type="text" name="product_name" id="product_name" value="<?php echo @$row->product_name; ?>" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="dsc-key">
                    <?php echo JText::_('COM_TIENDA_ALIAS'); ?>:
                </td>
                <td class="dsc-value">
                    <input name="product_alias" id="product_alias" value="<?php echo @$row->product_alias; ?>" type="text" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="dsc-key">
                    <?php echo JText::_('COM_TIENDA_ID'); ?>:
                </td>
                <td class="dsc-value">
                    <?php 
                    if (empty($row->product_id)) 
                    {
                        ?>
                        <div style="color: grey;"><?php echo JText::_('COM_TIENDA_AUTOMATICALLY_GENERATED'); ?></div>
                        <?php
                    }
                    else
                    {
                        echo @$row->product_id;
                    }
                    ?>
                </td>
            </tr>
        </table>
        </div>
        <div style="float: left; margin:5px;">
        <table class="table table-striped table-bordered">
            <tr>
                <td style="width: 100px; text-align: right;" class="dsc-key">
                    <?php echo JText::_('COM_TIENDA_MODEL'); ?>:
                </td>
                <td class="dsc-value">
                    <input type="text" name="product_model" id="product_model" value="<?php echo @$row->product_model; ?>" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="dsc-key">
                    <?php echo JText::_('COM_TIENDA_SKU'); ?>:
                </td>
                <td class="dsc-value">
                    <input type="text" name="product_sku" id="product_sku" value="<?php echo @$row->product_sku; ?>" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="dsc-key">
                    <?php echo JText::_('COM_TIENDA_ENABLED'); ?>:
                </td>
                <td class="dsc-value">
                    <?php  echo TiendaSelect::btbooleanlist( 'product_enabled', '', @$row->product_enabled ); ?>
                </td>
            </tr>
        </table>
        </div>
        <div style="float: left; margin:5px;">
        <table class="table table-striped table-bordered">
            <tr>
                <td style="width: 100px; text-align: right;" class="dsc-key">
                    <?php echo JText::_('COM_TIENDA_OVERALL_RATING'); ?>:
                </td>
                <td class="dsc-value">
                    <?php echo $helper_product->getRatingImage( $this, @$row->product_rating ); ?>
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="dsc-key">
                    <?php echo JText::_('COM_TIENDA_COMMENTS'); ?>:
                </td>
                <td class="dsc-value">
                    <?php echo @$row->product_comments; ?>
                </td>
            </tr>
        </table>
        </div>
        <div id="default_image" style="float: right; padding: 0px 5px 5px 0px;">
            <?php
            jimport('joomla.filesystem.file');
            if (!empty($row->product_full_image))
            {
                echo TiendaUrl::popup( $helper_product->getImage($row->product_id, '', '', 'full', true, false ), $helper_product->getImage($row->product_id, 'id', $row->product_name, 'full', false, false, array( 'height'=>80 )), array('update' => false, 'img' => true));
            }
            ?>
        </div>
    </fieldset>

    <div class="reset"></div>
  
 

    
    
    <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onBeforeDisplayProductForm', array( $row ) );                    
    ?>

<?php $tabs = array(); #TODO add the tabs to an array or object and than make a display method so plugins can edit the tabs and the ordering ?>

		<ul class="nav nav-tabs" id="TiendaProductTabs">
            <li class="active"><a href="#panel_product_properties" data-toggle="tab"><?php echo  JText::_('COM_TIENDA_PRODUCT_PROPERTIES'); ?></a></li>
            <li class=""><a href="#panel_pricing" data-toggle="tab"><?php echo JText::_('COM_TIENDA_PRICING_AND_INVENTORY'); ?></a></li>
          	 <li class=""><a href="#subscriptions" data-toggle="tab"><?php echo  JText::_('COM_TIENDA_SUBSCRIPTIONS'); ?></a></li>
           <li class=""><a href="#panel_relations" data-toggle="tab"><?php echo JText::_('COM_TIENDA_RELATED_ITEMS'); ?></a></li>
           <li class=""><a href="#panel_display" data-toggle="tab"><?php echo JText::_('COM_TIENDA_DISPLAY'); ?></a></li>
          <li class=""><a href="#panel_integrations" data-toggle="tab"><?php echo JText::_('COM_TIENDA_INTEGRATIONS'); ?></a></li>
          <li class=""><a href="#panel_advanced" data-toggle="tab"><?php echo JText::_('COM_TIENDA_ADVANCED'); ?></a></li>
          
         <?php JDispatcher::getInstance()->trigger('onDisplayProductFormTabs', array( $tabs, $row ) );?>
      
          </ul>

 
 
 <div class="tab-content">
  <div class="tab-pane active" id="panel_product_properties" data-target="panel_product_properties">
  <table style="width: 100%" class="table">
	<tr>
		<td style="vertical-align: top; width: 65%;">
		
            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_ADDITIONAL_INFORMATION'); ?></legend>
            
            <div style='float: left; width: 100%;'>
            <table class="table table-striped table-bordered" style="width: 100%;">
                <tr>
                    <td  class="dsc-key">
                       
                        <?php echo JText::_('COM_TIENDA_MANUFACTURER'); ?>:
                        
                    </td>
                    <td class="dsc-value">
                        <?php echo TiendaSelect::manufacturer( @$row->manufacturer_id, 'manufacturer_id', '', 'manufacturer_id', false, true ); ?>
                    </td>
                </tr>
                <?php 
                if (empty($row->product_id)) 
                {
                    // doing a new product, so display a notice
                    ?>
                    <tr>
                        <td width="100" align="right" class="dsc-key" style="vertical-align: top;">
                            <?php echo JText::_('COM_TIENDA_PRODUCT_ATTRIBUTES'); ?>:
                        </td>
                        <td class="dsc-value">
                            <div class="note"><?php echo JText::_('COM_TIENDA_CLICK_APPLY_TO_BE_ABLE_TO_CREATE_PRODUCT_ATTRIBUTES'); ?></div>
                        </td>
                    </tr>
                    <?php
                } 
                    else
                {
                    // display lightbox link to manage attributes
                    ?>
                    <tr>
                        <td  class="dsc-key">
                            <?php echo JText::_('COM_TIENDA_PRODUCT_ATTRIBUTES'); ?>:
                        </td>
                        <td class="dsc-value">
                            [<?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=products&task=setattributes&id=".$row->product_id."&tmpl=component", JText::_('COM_TIENDA_SET_ATTRIBUTES'), array('onclose' => '\function(){tiendaNewModal(\''.JText::_('COM_TIENDA_SAVING_THE_PRODUCT').'\'); submitbutton(\'apply\');}') ); ?>]
                            <?php $attributes = $helper_product->getAttributes( $row->product_id ); ?>
                            <div id="current_attributes">
                                <?php foreach (@$attributes as $attribute) : ?>
                                    [<a href="<?php echo "index.php?option=com_tienda&view=productattributes&task=delete&cid[]=".$attribute->productattribute_id."&return=".base64_encode("index.php?option=com_tienda&view=products&task=edit&id=".$row->product_id); ?>">
                                        <?php echo JText::_('COM_TIENDA_REMOVE'); ?>
                                    </a>]
                                    [<?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=products&task=setattributeoptions&id=".$attribute->productattribute_id."&tmpl=component", JText::_('Set Attribute Options'), array('onclose' => '\function(){tiendaNewModal(\''.JText::_('COM_TIENDA_SAVING_THE_PRODUCT').'\'); submitbutton(\'apply\');}') ); ?>]
                                    <?php echo $attribute->productattribute_name; ?>
                                    <?php echo "(".$attribute->option_names_csv.")"; ?>
                                    <br/>
                                <?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <td class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_REQUIRES_SHIPPING'); ?>:
                    </td>
                    <td class="dsc-value">
                        <?php // Make the shipping options div only display if yes ?>
                        <div class="control-group"><div class="controls"><fieldset id="shipoptions" class="radio btn-group">
	  <input class="input"  type="radio" <?php if (empty($row->product_ships)) { echo "checked='checked'"; } ?> value="0" name="product_ships" id="product_ships0"/>
	  <label onclick="tiendaShowHideDiv('shipping_options');" for="product_ships0"><?php echo JText::_('COM_TIENDA_NO'); ?></label>
     <input class="input"  type="radio" <?php if (!empty($row->product_ships)) { echo "checked='checked'"; } ?> value="1" name="product_ships" id="product_ships1"/><label onclick="tiendaShowHideDiv('shipping_options');" for="product_ships1"><?php echo JText::_('COM_TIENDA_YES'); ?></label>
</fieldset></div></div>
                        
                                 </td>
                </tr>
                </table>
                </div>
                
                <?php // Only display if product ships ?>
                <div id="shipping_options" style='width: 100%; <?php if (empty($row->product_ships)) { echo "display: none;"; } ?>' >                
                <table class="table table-striped table-bordered" style="width: 100%;">
                <tr>
                    <td style="width: 100px; text-align: right;" class="dsc-key">
                        <label for="product_weight">
                        <?php echo JText::_('COM_TIENDA_WEIGHT'); ?>:
                        </label>
                    </td>
                    <td>
                        <input type="text" name="product_weight" id="product_weight" value="<?php echo @$row->product_weight; ?>" size="30" maxlength="250" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="dsc-key">
                        <label for="product_length">
                        <?php echo JText::_('COM_TIENDA_LENGTH'); ?>:
                        </label>
                    </td>
                    <td>
                        <input type="text" name="product_length" id="product_length" value="<?php echo @$row->product_length; ?>" size="30" maxlength="250" />
                    </td>
                </tr>

                <tr>
                    <td style="width: 100px; text-align: right;" class="dsc-key">
                        <label for="product_width">
                        <?php echo JText::_('COM_TIENDA_WIDTH'); ?>:
                        </label>
                    </td>
                    <td>
                        <input type="text" name="product_width" id="product_width" value="<?php echo @$row->product_width; ?>" size="30" maxlength="250" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="dsc-key">
                        <label for="product_height">
                        <?php echo JText::_('COM_TIENDA_HEIGHT'); ?>:
                        </label>
                    </td>
                    <td>
                        <input type="text" name="product_height" id="product_height" value="<?php echo @$row->product_height; ?>" size="30" maxlength="250" />
                    </td>
                </tr>
            </table>
            </div>
            
            <div class="reset"></div>
            
            </fieldset>
		
            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_DESCRIPTION'); ?></legend>
            
            <table class="table table-striped table-bordered" style="width: 100%;">
				<tr>
					<td style="width: 100px; text-align: right; vertical-align:top;" class="dsc-key">
						<?php echo JText::_('COM_TIENDA_FULL_DESCRIPTION'); ?>:
					</td>
					<td>
						<?php $editor = JFactory::getEditor(); ?>
						<?php echo $editor->display( 'product_description',  @$row->product_description, '100%', '300', '75', '20' ) ; ?>
					</td>
				</tr>
                <tr>
                    <td style="width: 100px; text-align: right; vertical-align:top;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_SHORT_DESCRIPTION'); ?>:
                    </td>
                    <td>
                        <?php $editor = JFactory::getEditor(); ?>
                        <?php echo $editor->display( 'product_description_short',  @$row->product_description_short, '100%', '300', '75', '10' ) ; ?>
                    </td>
                </tr>
                <?php if (Tienda::getClass('TiendaHelperTags', 'helpers.tags')->isInstalled()) : ?>
                <tr>
                	<td style="width: 100px; text-align: right; vertical-align:top;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_TAGS'); ?>:
                    </td>
                	<td>                	
                	<?php
                        // triggering custom event for plugins
				        //JPluginHelper::importPlugin('tienda');
						$dispatcher = &JDispatcher::getInstance();
						$dispatcher->trigger('onDisplayProductTagsForm', array( $row ) );
					?>
					</td>
                </tr>
                <?php endif; ?>
            </table>
            </fieldset>
		    
            <?php
                // fire plugin event here to enable extending the form
                JDispatcher::getInstance()->trigger('onAfterDisplayProductFormMainColumn', array( $row ) );                    
            ?>
		    
		</td>
		<td style="max-width: 35%; min-width: 35%; width: 35%; vertical-align: top;">

            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_PUBLICATION_DATES'); ?></legend>
            <table class="table table-striped table-bordered" style="width: 100%;">
                <tr>
                    <td style="width: 100px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_PUBLISH_UP'); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::calendar( @$row->publish_date, "publish_date", "publish_date", '%Y-%m-%d %H:%M:%S', array('size'=>25) ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_PUBLISH_DOWN'); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::calendar( @$row->unpublish_date, "unpublish_date", "unpublish_date", '%Y-%m-%d %H:%M:%S', array('size'=>25) ); ?>
                    </td>
                </tr>
            </table>
            </fieldset>

            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_CATEGORIES'); ?></legend>
            <table class="table table-striped table-bordered" style="width: 100%;">
                <?php 
                if (empty($row->product_id)) 
                {
                    // doing a new product, so collect default info
                    ?>
                    <tr>
                        <td width="100" align="right" class="dsc-key" style="vertical-align: top;">
                            <label for="category_id">
                            <?php echo JText::_('COM_TIENDA_PRODUCT_CATEGORY'); ?>:
                            </label>
                        </td>
                        <td>
                            <?php echo TiendaSelect::category( '', 'category_id', '', 'category_id' ); ?>
                            <div class="note"><?php echo JText::_('COM_TIENDA_SET_INITIAL_CATEGORY_NOW_ADDITIONAL_ONES_LATER'); ?></div>
                        </td>
                    </tr>
                    <?php
                } 
                    else
                {
                    // display lightbox link to manage categories
                    ?>
                    <tr>
                        <td style="width: 100px; text-align: right;" class="dsc-key">
                            <label for="product_categories">
                            <?php echo JText::_('COM_TIENDA_CATEGORIES'); ?>:
                            </label>
                        </td>
                        <td>
                            <?php Tienda::load( 'TiendaHelperCategory', 'helpers.category' ); ?>
                            <?php Tienda::load( 'TiendaUrl', 'library.url' ); ?>
                            [<?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=products&task=selectcategories&id=".$row->product_id."&tmpl=component", JText::_('COM_TIENDA_SELECT_CATEGORIES')); ?>]
                            <?php $categories = $helper_product->getCategories( $row->product_id ); ?>
                            <div id="current_categories">
                                <?php foreach (@$categories as $category) : ?>
                                    [<a href="<?php echo "index.php?option=com_tienda&view=products&task=selected_disable&id=".$row->product_id."&cid[]=".$category."&return=".base64_encode("index.php?option=com_tienda&view=products&task=edit&id=".$row->product_id); ?>">
                                        <?php echo JText::_('COM_TIENDA_REMOVE'); ?>
                                    </a>]
                                    <?php echo TiendaHelperCategory::getPathName( $category ); ?>
                                    <br/>
                                <?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
                ?>            
            </table>
            </fieldset>
		
            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_IMAGES'); ?></legend>
            <table class="table table-striped table-bordered" style="width: 100%;">            
                <tr>
                    <td style="width: 100px; text-align: right;" class="dsc-key">
                        <label for="product_full_image">
                        <?php echo JText::_('COM_TIENDA_CURRENT_DEFAULT_IMAGE'); ?>:
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
                    <td style="width: 100px; text-align: right;" class="dsc-key">
                        <label for="product_image_gallery">
                        <?php echo JText::_('COM_TIENDA_CURRENT_IMAGES'); ?>:
                        </label>
                    </td>
                    <td>
                        [
                        <?php
                        echo TiendaUrl::popup( "index.php?option=com_tienda&view=products&task=viewGallery&id=".@$row->product_id."&tmpl=component", JText::_('COM_TIENDA_VIEW_GALLERY') ); 
                        ?>
                        ]
                        <br/>
                        <?php $images = $helper_product->getGalleryImages( $helper_product->getGalleryPath( @$row->product_id ) ); ?> 
                        <?php foreach (@$images as $image) : ?>
                            [<a href="<?php echo "index.php?option=com_tienda&view=products&task=deleteImage&product_id=".@$row->product_id."&image=".$image."&return=".base64_encode("index.php?option=com_tienda&view=products&task=edit&id=".@$row->product_id); ?>">
                                <?php echo JText::_('COM_TIENDA_REMOVE'); ?>
                            </a>]
                            <?php echo $image; ?>
                            <br/>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="dsc-key">
                        <label for="product_full_image_new">
                        <?php echo JText::_('COM_TIENDA_UPLOAD_NEW_IMAGE'); ?>:
                        </label>
                    </td>
                    <td>
                        <div class="multiupload" id="oldUploader"> 
                        	<input name="product_full_image_new" type="file" size="40" />
                        </div>
                        <div id="uploadifyImage">
                        	<input id="uploadify_file_upload" type="file" name="uploadify_image" style="display: none;" width="120" height="30">
							<div id="uploadify-queue" class="uploadifyQueue"></div>
							<div id="uploadify-status-message"></div>
                        </div>
                        <div class="note" style="clear:both">
	                    	<?php echo JText::_('COM_TIENDA_UPLOAD_ZIP_IMAGES_MESSAGE'); ?>
	                    </div>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top; width: 100px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_IMAGES_GALLERY_PATH_OVERRIDE'); ?>:
                    </td>
                    <td>
                        <input name="product_images_path" id="product_images_path" value="<?php echo @$row->product_images_path; ?>" size="75" maxlength="255" type="text" />
                        <div class="note">
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
            
            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_FILES'); ?></legend>
            <table class="table table-striped table-bordered" style="width: 100%;">
             
                <?php 
                if (empty($row->product_id)) 
                {
                    // doing a new product, so display a notice
                    ?>
                    <tr>
                        <td width="100" align="right" class="dsc-key" style="vertical-align: top;">
                            <?php echo JText::_('COM_TIENDA_PRODUCT_FILES'); ?>:
                        </td>
                        <td>
                            <div class="note"><?php echo JText::_('COM_TIENDA_CLICK_APPLY_TO_BE_ABLE_TO_ADD_FILES_TO_THE_PRODUCT'); ?></div>
                        </td>
                    </tr>
                    <?php
                } 
                    else
                {
                    // display lightbox link to manage files
                    ?>
                    <tr>
                        <td style="width: 100px; text-align: right;" class="dsc-key">
                            <?php echo JText::_('COM_TIENDA_PRODUCT_FILES'); ?>:
                        </td>
                        <td>
                            <?php
                            Tienda::load( 'TiendaUrl', 'library.url' );
                            ?>
                            [<?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=products&task=setfiles&id=".$row->product_id."&tmpl=component", JText::_('COM_TIENDA_MANAGE_FILES') ); ?>]
                            <?php $files = $helper_product->getFiles( $row->product_id ); ?>
                            <div id="current_files">
                                <?php foreach (@$files as $file) : ?>
                                    [<a href="<?php echo "index.php?option=com_tienda&view=productfiles&task=delete&cid[]=".$file->productfile_id."&return=".base64_encode("index.php?option=com_tienda&view=products&task=edit&id=".$row->product_id); ?>">
                                        <?php echo JText::_('COM_TIENDA_REMOVE'); ?>
                                    </a>]
                                    [<a href="<?php echo "index.php?option=com_tienda&view=productfiles&task=downloadfile&id=".$file->productfile_id."&product_id=".$row->product_id; ?>">
                                    <?php echo JText::_('COM_TIENDA_DOWNLOAD');?>
                                    </a>]
                                    <?php echo $file->productfile_name; ?>
                                    <br/>
                                <?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; width: 100px; text-align: right;" class="dsc-key">
                            <?php echo JText::_('COM_TIENDA_PRODUCT_FILES_PATH_OVERRIDE'); ?>:
                        </td>
                        <td>
                            <input name="product_files_path" id="product_files_path" value="<?php echo @$row->product_files_path; ?>" size="75" maxlength="255" type="text" />
                            <div class="note">
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
            </fieldset>
            		
		<?php
    		// fire plugin event here to enable extending the form
            JDispatcher::getInstance()->trigger('onAfterDisplayProductFormRightColumn', array( $row ) );                    
		?>
		</td>
	</tr>
	</table></div>
  <div class="tab-pane fade" id="panel_pricing">   <div style="clear: both;"></div>
        
        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_PRICES_AND_INVENTORY'); ?></legend>
            
            <table class="table table-striped table-bordered">
                <tr>
                    <td style="width: 100px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_ITEM_FOR_SALE'); ?>
                    </td>
                    <td>
                        <?php  echo TiendaSelect::btbooleanlist( 'product_notforsale', '', @$row->product_notforsale ); ?>
                    </td>
                </tr>
                <?php
								$prices = $helper_product->getPrices( $row->product_id );
                if (empty($row->product_id) || empty($prices)) 
                {
                    // new product (or no prices set) - ask for normal price
                    ?>
                    <tr>
                        <td width="100" align="right" class="dsc-key" style="vertical-align: top;">
                            <label for="product_price">
                            <?php echo JText::_('COM_TIENDA_NORMAL_PRICE'); ?>:
                            </label>
                        </td>
                        <td>
                            <input type="text" name="product_price" id="product_price" value="<?php echo @$row->product_price; ?>" size="25" maxlength="25" />
                            <div class="note"><?php echo JText::_('COM_TIENDA_SET_NORMAL_PRICE_NOW_SPECIAL_PRICES_LATER'); ?></div>
                        </td>
                    </tr>
                    <?php
                } 
                    else
                {
                    // display lightbox link to manage prices
                    ?>
                    <tr>
                        <td style="width: 100px; text-align: right;" class="dsc-key">
                            <label for="product_prices">
                            <?php echo JText::_('COM_TIENDA_PRICES'); ?>:
                            </label>
                        </td>
                        <td>
                            <?php
                            Tienda::load( 'TiendaUrl', 'library.url' );
                            ?>
                            [<?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=products&task=setprices&id=".$row->product_id."&tmpl=component", JText::_('COM_TIENDA_SET_PRICES') ); ?>]
                            <div id="current_prices">
                                <?php foreach (@$prices as $price) : ?>
                                    [<a href="<?php echo $price->link_remove."&return=".base64_encode("index.php?option=com_tienda&view=products&task=edit&id=".$row->product_id); ?>">
                                        <?php echo JText::_('COM_TIENDA_REMOVE'); ?>
                                    </a>]
                                    <?php echo TiendaHelperBase::currency( $price->product_price ); ?>
                                    <br/>
                                <?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <td style="width: 100px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_TAX_CLASS'); ?>:
                    </td>
                    <td>
                        <?php echo TiendaSelect::taxclass( @$row->tax_class_id, 'tax_class_id', '', 'tax_class_id', false ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_CHECK_PRODUCT_INVENTORY'); ?>:
                    </td>
                    <td>
                        <?php  echo TiendaSelect::btbooleanlist( 'product_check_inventory', '', @$row->product_check_inventory ); ?>
                    </td>
                </tr>
                
                
                <?php
                if (empty($row->product_check_inventory) && !empty($row->product_id))
                {
                ?>
                <tr>
                        <td width="100" align="right" class="dsc-key" style="vertical-align: top;">
                            <?php echo JText::_('COM_TIENDA_PRODUCT_QUANTITIES'); ?>:
                        </td>
                        <td>
                            <div class="note"><?php echo JText::_('COM_TIENDA_PRODUCT_INVENTORY_IS_DISABLED._ENABLE_IT_TO_SET_QUANTITIES'); ?></div>
                        </td>
                </tr>
                <?php
                } 
                else 
                {
                    if (empty($row->product_id)) 
                    {
                        // doing a new product
                        ?>
                        <tr>
                            <td width="100" align="right" class="dsc-key" style="vertical-align: top;">
                                <?php echo JText::_('COM_TIENDA_STARTING_QUANTITY'); ?>:
                            </td>
                            <td>
                                <input type="text" name="product_quantity" value="" size="15" maxlength="11" />
                            </td>
                        </tr>
                        <?php
                    } 
                        else
                    {
                        // display lightbox link to manage quantities
                        ?>
                        <tr>
                            <td style="width: 100px; text-align: right;" class="dsc-key">
                                <?php echo JText::_('COM_TIENDA_PRODUCT_QUANTITIES'); ?>:
                            </td>
                            <td>
                                <?php
                                echo $row->product_quantity;
                                echo "<br/>";
                                Tienda::load( 'TiendaUrl', 'library.url' );
                                $options = array('update' => true ); 
                                ?>
                                [<?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=products&task=setquantities&id=".$row->product_id."&tmpl=component", JText::_('COM_TIENDA_SET_QUANTITIES'), $options); ?>]
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
                <tr>
                    <td title="<?php echo JText::_('COM_TIENDA_PURCHASE_QUANTITY_RESTRICTION').'::'.JText::_('COM_TIENDA_PURCHASE_QUANTITY_RESTRICTION_TIP'); ?>" style="width: 100px; text-align: right;" class="key hasTip" >
                        <?php echo JText::_('COM_TIENDA_PURCHASE_QUANTITY_RESTRICTION'); ?>:
                    </td>
                    <td>
                    	<div class="control-group"><div class="controls"><fieldset id="product_enabled" class="radio btn-group">
		<input  type="radio" <?php if (empty($row->quantity_restriction)) { echo "checked='checked'"; } ?> value="0" name="quantity_restriction" id="quantity_restriction0"/><label onclick="tiendaShowHideDiv('quantity_restrictions');" for="quantity_restriction0"><?php echo JText::_('COM_TIENDA_NO'); ?></label>
                        <input  type="radio" <?php if (!empty($row->quantity_restriction)) { echo "checked='checked'"; } ?> value="1" name="quantity_restriction" id="quantity_restriction1"/><label onclick="tiendaShowHideDiv('quantity_restrictions');" for="quantity_restriction1"><?php echo JText::_('COM_TIENDA_YES'); ?></label>
                      
</fieldset></div></div>
                      	 <?php // Only display if quantity restriction ?>
                        <div id="quantity_restrictions" style='float: right; width: 50%; <?php if (empty($row->quantity_restriction)) { echo "display: none;"; } ?>' >                
                        <table class="table table-striped table-bordered" style="width: 100%;">
                        <tr>
                            <td style="width: 100px; text-align: right;" class="dsc-key">
                                <label for="quantity_min">
                                <?php echo JText::_('COM_TIENDA_MINIMUM_QUANTITY'); ?>:
                                </label>
                            </td>
                            <td>
                                <input type="text" name="quantity_min" id="quantity_min" value="<?php echo @$row->quantity_min; ?>" size="30" maxlength="250" />
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 100px; text-align: right;" class="dsc-key">
                                <label for="quantity_max">
                                <?php echo JText::_('COM_TIENDA_MAXIUM_QUANTITY'); ?>:
                                </label>
                            </td>
                            <td>
                                <input type="text" name="quantity_max" id="quantity_max" value="<?php echo @$row->quantity_max; ?>" size="30" maxlength="250" />
                            </td>
                        </tr>
        
                        <tr>
                            <td style="width: 100px; text-align: right;" class="dsc-key">
                                <label for="quantity_step">
                                <?php echo JText::_('COM_TIENDA_STEP_QUANTITY'); ?>:
                                </label>
                            </td>
                            <td>
                                <input type="text" name="quantity_step" id="quantity_step" value="<?php echo @$row->quantity_step; ?>" size="30" maxlength="250" />
                            </td>
                        </tr>
                        </table>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_HIDE_QUANTITY_INPUT_ON_PRODUCT_FORM'); ?>:
                    </td>
                    <td>
                        <?php  echo TiendaSelect::btbooleanlist( 'param_hide_quantity_input', '', @$row->product_parameters->get('hide_quantity_input') ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_DEFAULT_QUANTITY_IF_INPUT_HIDDEN_ON_PRODUCT_FORM'); ?>:
                    </td>
                    <td>
                        <input type="text" name="param_default_quantity" id="param_default_quantity" value="<?php echo @$row->product_parameters->get('default_quantity'); ?>" size="10" maxlength="250" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_DISABLE_ABILITY_TO_UPDATE_QUANTITY_IN_CART'); ?>:
                    </td>
                    <td>
                        <?php  echo TiendaSelect::btbooleanlist( 'param_hide_quantity_cart', '', @$row->product_parameters->get('hide_quantity_cart') ); ?>
                    </td>
                </tr>
            </table>
            </fieldset>
        </div>
        
        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_PRODUCT_LIST_PRICE'); ?></legend>
            <table class="table table-striped table-bordered">
                <tr>
                    <td title="<?php echo JText::_('COM_TIENDA_DISPLAY_PRODUCT_LIST_PRICE').'::'.JText::_('COM_TIENDA_DISPLAY_PRODUCT_LIST_PRICE_TIP'); ?>" style="width: 100px; text-align: right;" class="key hasTip" >
                        <?php echo JText::_('COM_TIENDA_DISPLAY_PRODUCT_LIST_PRICE'); ?>:
                    </td>
                    <td>
                        <?php  echo TiendaSelect::btbooleanlist( 'product_listprice_enabled', '', @$row->product_listprice_enabled ); ?>
                    </td>
                </tr>
                <tr>
                    <td title="<?php echo JText::_('COM_TIENDA_DISPLAY_PRODUCT_LIST_PRICE').'::'.JText::_('COM_TIENDA_DISPLAY_PRODUCT_LIST_PRICE_TIP'); ?>" style="width: 100px; text-align: right;" class="key hasTip" >
                        <?php echo JText::_('COM_TIENDA_DISPLAY_PRODUCT_LIST_PRICE'); ?>
                    </td>
                    <td>
                        <input type="text" name="product_listprice" value="<?php echo @$row->product_listprice; ?>" size="15" maxlength="11" />
                    </td>
                </tr>

            </table>
            </fieldset>
        </div>
    
        <div style="clear: both;"></div>
        </div>
  <div class="tab-pane" id="subscriptions"> <div style="clear: both;"></div>
        
        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_NON_RECURRING_SUBSCRIPTION'); ?></legend>
            
            <div class="note"><?php echo JText::_('COM_TIENDA_NON_RECURRING_SUBSCRIPTION_NOTE'); ?></div>
            
            <table class="table table-striped table-bordered" style="width: 100%;">
                <tr>
                    <td style="width: 125px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_PRODUCT_CREATES_SUBSCRIPTION'); ?>:
                    </td>
                    <td>
                    	<div class="control-group"><div class="controls"><fieldset id="product_enabled" class="radio btn-group">
		 <input type="radio" <?php if (empty($row->product_subscription)) { echo "checked='checked'"; } ?> value="0" name="product_subscription" id="product_subscription0"/><label for="product_subscription0"><?php echo JText::_('COM_TIENDA_NO'); ?></label>
                        <input type="radio" <?php if (!empty($row->product_subscription)) { echo "checked='checked'"; } ?> value="1" name="product_subscription" id="product_subscription1"/><label for="product_subscription1"><?php echo JText::_('COM_TIENDA_YES'); ?></label>
                              
</fieldset></div></div>
                    	
                          </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_LIFETIME_SUBSCRIPTION'); ?>:
                    </td>
                    <td>
                        <?php  echo TiendaSelect::btbooleanlist( 'subscription_lifetime', '', @$row->subscription_lifetime ); ?>
                    </td>
                </tr>
                <tr>
                    <td title="<?php echo JText::_('COM_TIENDA_SUBSCRIPTION_PERIOD_INTERVAL').'::'.JText::_('COM_TIENDA_SUBSCRIPTION_PERIOD_INTERVAL_TIP'); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                        <?php echo JText::_('COM_TIENDA_SUBSCRIPTION_PERIOD_INTERVAL'); ?>:
                    </td>
                    <td>
                        <input name="subscription_period_interval" id="subscription_period_interval" value="<?php echo @$row->subscription_period_interval; ?>" size="10" maxlength="10" type="text" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_SUBSCRIPTION_PERIOD_UNIT'); ?>:
                    </td>
                    <td>
                        <?php echo TiendaSelect::periodUnit( @$row->subscription_period_unit, 'subscription_period_unit' ); ?>
                    </td>
                </tr>          
                <tr>
                    <td style="width: 125px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_ISSUES_LIST'); ?>:
                    </td>
                    <td>
		                <?php 
		                if (empty($row->product_id)) 
		                {
                    // doing a new product, so display a note
                    ?>
                      <div class="note"><?php echo JText::_('COM_TIENDA_CLICK_APPLY_TO_BE_ABLE_TO_ADD_ISSUES_TO_THE_PRODUCT'); ?></div>
                    <?php
		                } 
                    else
		                {
		                	Tienda::load( 'TiendaHelperSubscription', 'helpers.subscription' );
		                	$next_issue = TiendaHelperSubscription::getMarginalIssue( $row->product_id );
		                	$last_issue = TiendaHelperSubscription::getMarginalIssue( $row->product_id, 'DESC' );
		                	$num_issues = TiendaHelperSubscription::getNumberIssues( $row->product_id );
                    ?>
                       [<?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=products&task=setissues&id=".$row->product_id."&tmpl=component", JText::_('COM_TIENDA_SET_ISSUES') ); ?>]<br />

                       <?php 
                       		if( isset( $next_issue ) )
	                       		echo '<b>'.JText::_('COM_TIENDA_NEXT_ISSUE_PUBLISHED').':</b> '.JHTML::_('date', $next_issue->publishing_date, JText::_('DATE_FORMAT_LC4') ).'<br />'; 
                       		if( isset( $last_issue ) )
	                       		echo '<b>'.JText::_('COM_TIENDA_LAST_ISSUE_PUBLISHED').':</b> '.JHTML::_('date', $last_issue->publishing_date, JText::_('DATE_FORMAT_LC4') ).'<br />'; 
                       		echo '<b>'.JText::_('COM_TIENDA_ISSUES_LEFT').':</b> '.@$num_issues;?><br />
                    <?php } ?>
                    </td>
                </tr>          
            </table>
            </fieldset>
                        
        <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_SUBSCRIPTION_WITH_PRO_RATED_CHARGES'); ?></legend>
            <div class="note"><?php echo JText::_('COM_TIENDA_SUBSCRIPTION_WITH_PRO-RATED_CHARGES_NOTE'); ?></div>
            <table class="table table-striped table-bordered" style="width: 100%;">
            		<?php $onclick_prorated = 'showProRatedFields();'; ?>
                <tr>
                    <td style="width: 125px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_PRODUCT_CHARGES_PRO-RATED'); ?>:
                    </td>
                    <td>
                    	<div class="control-group"><div class="controls"><fieldset id="subscription_lifetime" class="radio btn-group">
	  <input type="radio" <?php if ( !$row->subscription_prorated ) { echo "checked='checked'"; } ?> value="0" name="subscription_prorated" id="subscription_prorated0" onchange="<?php echo $onclick_prorated; ?>"/><label for="subscription_prorated0"><?php echo JText::_('COM_TIENDA_NO'); ?></label>
                        <input type="radio" <?php if ( $row->subscription_prorated ) { echo "checked='checked'"; } ?> value="1" name="subscription_prorated" id="subscription_prorated1" onchange="<?php echo $onclick_prorated; ?>"/><label for="subscription_prorated1"><?php echo JText::_('COM_TIENDA_YES'); ?></label>
               </fieldset></div></div>
                           </td>
                </tr>
                <tr class="prorated_related">
                    <td style="width: 125px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_SUBSCRIPTION_PRO-RATED_INITIAL_CHARGE'); ?>:
                    </td>
                    <td>
                    	<div class="control-group"><div class="controls"><fieldset id="product_enabled" class="radio btn-group">
		 <input type="radio" <?php if ( !$row->subscription_prorated_charge ) { echo "checked='checked'"; } ?> value="0" name="subscription_prorated_charge" id="subscription_prorated_charge0"/><label for="subscription_prorated_charge0"><?php echo JText::_('COM_TIENDA_SUBSCRIPTION_PRO-RATED_CHARGE_FULL'); ?></label>
                        <input type="radio" <?php if ( $row->subscription_prorated_charge ) { echo "checked='checked'"; } ?> value="1" name="subscription_prorated_charge" id="subscription_prorated_charge1"/><label for="subscription_prorated_charge1"><?php echo JText::_('COM_TIENDA_SUBSCRIPTION_PRO-RATED_CHARGE_PRO-RATED'); ?></label>
                                      
</fieldset></div></div>
                                   </td>
                </tr>
                <tr class="prorated_related">
                    <td style="width: 125px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_SUBSCRIPTION_PRO-RATED_DATE'); ?>:<br />
                        <?php echo JText::_('COM_TIENDA_SUBSCRIPTION_PRO-RATED_DATE_NOTE');?>
                    </td>
                    <td>
                        <input name="subscription_prorated_date" id="subscription_prorated_date" value="<?php echo @$row->subscription_prorated_date; ?>" size="8" maxlength="5" type="text" />
                    </td>
                </tr>
                <tr class="prorated_related">
                    <td style="width: 125px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_SUBSCRIPTION_PRO-RATED_TERM'); ?>:
                    </td>
                    <td>
                    	<div class="control-group"><div class="controls"><fieldset id="product_enabled" class="radio btn-group">
		               <input type="radio" <?php if ( $row->subscription_prorated_term == 'D' ) { echo "checked='checked'"; } ?> value="D" name="subscription_prorated_term" id="subscription_prorated_termD"/><label for="subscription_prorated_termD"><?php echo JText::_('COM_TIENDA_DAY'); ?></label>
                        <input type="radio" <?php if ( $row->subscription_prorated_term == 'M' ) { echo "checked='checked'"; } ?> value="M" name="subscription_prorated_term" id="subscription_prorated_termM"/><label for="subscription_prorated_termM"><?php echo JText::_('COM_TIENDA_MONTH'); ?></label>
                            
</fieldset></div></div>
                          </td>
                </tr>
            </table>
            </fieldset>
        </div>
        
        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_SUBSCRIPTION_WITH_RECURRING_CHARGES'); ?></legend>
            <table class="table table-striped table-bordered" style="width: 100%;">
                <tr>
                    <td style="width: 125px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_PRODUCT_CHARGES_RECUR'); ?>:
                    </td>
                    <td>
                    	<div class="control-group"><div class="controls"><fieldset id="product_enabled" class="radio btn-group">
		              <input type="radio" <?php if (empty($row->product_recurs)) { echo "checked='checked'"; } ?> value="0" name="product_recurs" id="product_recurs0"/><label class="btn" for="product_recurs0"><?php echo JText::_('COM_TIENDA_NO'); ?></label>
                        <input type="radio" <?php if (!empty($row->product_recurs)) { echo "checked='checked'"; } ?> value="1" name="product_recurs" id="product_recurs1"/><label class="btn" for="product_recurs1"><?php echo JText::_('COM_TIENDA_YES'); ?></label>
                        
</fieldset></div></div>
                                 </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_NUMBER_OF_RECURRING_CHARGES'); ?>:
                    </td>
                    <td>
                        <input name="recurring_payments" id="recurring_payments" value="<?php echo @$row->recurring_payments; ?>" size="10" maxlength="10" type="text" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_RECURRING_PERIOD_INTERVAL'); ?>:
                    </td>
                    <td>
                        <input name="recurring_period_interval" id="recurring_period_interval" value="<?php echo @$row->recurring_period_interval; ?>" size="10" maxlength="10" type="text" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_RECURRING_PERIOD_UNITS'); ?>:
                    </td>
                    <td>
                        <?php echo TiendaSelect::periodUnit( @$row->recurring_period_unit, 'recurring_period_unit' ); ?>
                    </td>
                </tr>
                <tr class="prorated_unrelated">
                    <td style="width: 125px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_TRIAL_PERIOD'); ?>:
                    </td>
                    <td>
                        <?php  echo TiendaSelect::btbooleanlist( 'recurring_trial', '', @$row->recurring_trial ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="key trial_price">
                        <?php echo JText::_('COM_TIENDA_TRIAL_PERIOD_PRICE'); ?>:
                    </td>
                    <td>
                        <input name="recurring_trial_price" id="recurring_trial_price" value="<?php echo @$row->recurring_trial_price; ?>" size="10" maxlength="10" type="text" />
                    </td>
                </tr>
                <tr class="prorated_unrelated">
                    <td style="width: 125px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_TRIAL_PERIOD_INTERVAL'); ?>:
                    </td>
                    <td>
                        <input name="recurring_trial_period_interval" id="recurring_trial_period_interval" value="<?php echo @$row->recurring_trial_period_interval; ?>" size="10" maxlength="10" type="text" />
                    </td>
                </tr>
                <tr class="prorated_unrelated">
                    <td style="width: 125px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_TRIAL_PERIOD_UNITS'); ?>:
                    </td>
                    <td>
                        <?php echo TiendaSelect::periodUnit( @$row->recurring_trial_period_unit, 'recurring_trial_period_unit' ); ?>
                    </td>
                </tr>          
            </table>
            </fieldset>
            
        </div>
    
        <div style="clear: both;"></div>
        
        </div>
  <div class="tab-pane" id="panel_relations"><div style="clear: both;"></div>
        
        <div style="width: 100%;">
            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_ADD_NEW_RELATIONSHIP'); ?></legend>
                <div id="new_relationship" style="float: left;">
                    <?php echo TiendaSelect::relationship('', 'new_relationship_type'); ?>
                    <?php echo JText::_('COM_TIENDA_PRODUCT_ID').": "; ?>
                    <input name="new_relationship_productid_to" size="15" type="text" />
                    <input name="new_relationship_productid_from" value="<?php echo @$row->product_id; ?>" type="hidden" />
                    <button value="<?php echo JText::_('COM_TIENDA_ADD'); ?>" class="btn btn-primary" onclick="tiendaAddRelationship('existing_relationships', '<?php echo JText::_('COM_TIENDA_UPDATING_RELATIONSHIPS'); ?>');" ><?php echo JText::_('COM_TIENDA_ADD'); ?></button>
                </div>
                <div style="clear: both;"></div>
            </fieldset>
        </div>
        
        <div style="width: 100%;">
            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_EXISTING_RELATIONSHIPS'); ?></legend>
                <div id="existing_relationships">
                <?php echo $this->product_relations; ?>
                </div>
            </fieldset>
        </div>

        <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onDisplayProductFormRelations', array( $row ) );                    
        ?>
        
        <div style="clear: both;"></div>
        
        </div>
  <div class="tab-pane" id="panel_display">  <div style="clear: both;"></div>
        
        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_TEMPLATE'); ?></legend>
            <table class="table table-striped table-bordered" style="width: 100%;">
                <tr>
                    <td style="vertical-align: top; width: 100px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_PRODUCT_LAYOUT_FILE'); ?>:
                    </td>
                    <td>
                        <?php echo TiendaSelect::productlayout( @$row->product_layout, 'product_layout' ); ?>
                        <div class="note">
                            <?php echo JText::_('COM_TIENDA_PRODUCT_LAYOUT_FILE_DESC'); ?>
                        </div>                        
                    </td>
                </tr>
            </table>
            </fieldset>
            
            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_EXTRA'); ?></legend>
            <table class="table table-striped table-bordered" style="width: 100%;">
                <tr>
                    <td style="vertical-align: top; width: 100px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_FEATURE_COMPARISON'); ?>:
                    </td>
                    <td>
                         <?php  echo TiendaSelect::btbooleanlist( 'param_show_product_compare', 'class="inputbox"', @$row->product_parameters->get('show_product_compare', '1') ); ?>                      
                    </td>
                </tr>
            </table>
            </fieldset>
        </div>
        
        <div style="float: right; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_POST_PURCHASE_ARTICLE'); ?></legend>
            <table class="table table-striped table-bordered" style="width: 100%;">
                <tr>
                    <td style="vertical-align: top; width: 100px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_SELECT_AN_ARTICLE_TO_DISPLAY_AFTER_PURCHASE'); ?>:
                    </td>
                    <td>
                        <?php echo $this->elementArticleModel->_fetchElement( 'product_article', @$row->product_article ); ?>
                        <?php echo $this->elementArticleModel->_clearElement( 'product_article', 0 ); ?>
                    </td>
                </tr>
            </table>
            </fieldset>
        </div>

        <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onDisplayProductFormDisplay', array( $row ) );                    
        ?>
        
        <div style="clear: both;"></div>
    </div>
  <div class="tab-pane" id="panel_integrations"> <div style="clear: both;"></div>
        
        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_AMBRASUBSCRIPTIONS_INTEGRATION'); ?></legend>
            <?php if (Tienda::getClass('TiendaHelperAmbrasubs', 'helpers.ambrasubs')->isInstalled()) : ?>
                <table class="table table-striped table-bordered" style="width: 100%;">
                    <tr>
                        <td title="<?php echo JText::_('COM_TIENDA_ASSOCIATED_AMBRASUBS_SUBSCRIPTION_TYPE').'::'.JText::_('ASSOCIATED_AMBRASUBS_SUBSCRIPTION_TYPE_TIP'); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                            <?php echo JText::_('COM_TIENDA_ASSOCIATED_AMBRASUBS_SUBSCRIPTION_TYPE'); ?>:
                        </td>
                        <td>
                            <?php echo TiendaHelperAmbrasubs::selectTypes( $row->product_parameters->get('ambrasubs_type_id'), 'ambrasubs_type_id' ); ?>
                        </td>
                    </tr>
                </table>
            <?php else : ?>
                <div class="note">
                    <?php echo JText::_('COM_TIENDA_AMBRASUBS_INSTALLATION_NOTICE'); ?>
                </div>
            <?php endif; ?>
            </fieldset>
        </div>
        
        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_AMIGOS_INTEGRATION'); ?></legend>
            <?php if (Tienda::getClass('TiendaHelperAmigos', 'helpers.amigos')->isInstalled()) : ?>
                <table class="table table-striped table-bordered" style="width: 100%;">
                    <tr>
                        <td style="width: 125px; text-align: right;" class="key hasTip" title="<?php echo JText::_('COM_TIENDA_COMMISSION_RATE_OVERRIDE').'::'.JText::_('COM_TIENDA_COMMISSION_RATE_OVERRIDE_TIP'); ?>" >
                            <?php echo JText::_('COM_TIENDA_COMMISSION_RATE_OVERRIDE'); ?>:
                        </td>
                        <td>
                            <input name="amigos_commission_override" id="amigos_commission_override" value="<?php echo @$row->product_parameters->get('amigos_commission_override'); ?>" size="10" maxlength="10" type="text" />
                        </td>
                    </tr>
                </table>
            <?php else : ?>
                <div class="note">
                    <?php echo JText::_('COM_TIENDA_AMIGOS_INSTALLATION_NOTICE'); ?>
                </div>
            <?php endif; ?>
            </fieldset>
        </div>
        
        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_BILLETS_INTEGRATION'); ?></legend>
            
            <?php if (Tienda::getClass('TiendaHelperBillets', 'helpers.billets')->isInstalled()) : ?>
                <table class="table table-striped table-bordered" style="width: 100%;">
                    <tr>
                        <td title="<?php echo JText::_('COM_TIENDA_TICKET_LIMIT_INCREASE').'::'.JText::_('COM_TIENDA_TICKET_LIMIT_INCREASE_TIP'); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                            <?php echo JText::_('COM_TIENDA_TICKET_LIMIT_INCREASE'); ?>:
                        </td>
                        <td>
                            <input name="billets_ticket_limit_increase" value="<?php echo @$row->product_parameters->get('billets_ticket_limit_increase'); ?>" size="10" maxlength="10" type="text" />
                        </td>
                    </tr>
                    <tr>
                        <td title="<?php echo JText::_('COM_TIENDA_EXCLUDES_USER_FROM_TICKET_LIMITS').'::'.JText::_('COM_TIENDA_EXCLUDES_USER_FROM_TICKET_LIMITS_TIP'); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                            <?php echo JText::_('COM_TIENDA_EXCLUDES_USER_FROM_TICKET_LIMITS'); ?>:
                        </td>
                        <td>
                            <?php  echo TiendaSelect::btbooleanlist( 'billets_ticket_limit_exclusion', 'class="inputbox"', $row->product_parameters->get('billets_ticket_limit_exclusion') ); ?>
                        </td>
                    </tr>
                    <tr>
                        <td title="<?php echo JText::_('COM_TIENDA_HOUR_LIMIT_INCREASE').'::'.JText::_('COM_TIENDA_HOUR_LIMIT_INCREASE_TIP'); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                            <?php echo JText::_('COM_TIENDA_HOUR_LIMIT_INCREASE'); ?>:
                        </td>
                        <td>
                            <input name="billets_hour_limit_increase" value="<?php echo @$row->product_parameters->get('billets_hour_limit_increase'); ?>" size="10" maxlength="10" type="text" />
                        </td>
                    </tr>
                    <tr>
                        <td title="<?php echo JText::_('COM_TIENDA_EXCLUDES_USER_FROM_HOUR_LIMITS').'::'.JText::_('COM_TIENDA_EXCLUDES_USER_FROM_HOUR_LIMITS_TIP'); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                            <?php echo JText::_('COM_TIENDA_EXCLUDES_USER_FROM_HOUR_LIMITS'); ?>:
                        </td>
                        <td>
                            <?php  echo TiendaSelect::btbooleanlist( 'billets_hour_limit_exclusion', 'class="inputbox"', $row->product_parameters->get('billets_hour_limit_exclusion') ); ?>
                        </td>
                    </tr>
                </table>
            <?php else : ?>
                <div class="note">
                    <?php echo JText::_('COM_TIENDA_BILLETS_VERSION_NOTICE'); ?>
                </div>
            <?php endif; ?>
            </fieldset>
        </div>

        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_JUGA_INTEGRATION'); ?></legend>
            
            <?php if (Tienda::getClass('TiendaHelperJuga', 'helpers.juga')->isInstalled()) : ?>
                <table class="table table-striped table-bordered" style="width: 100%;">
                    <tr>
                        <td title="<?php echo JText::_('COM_TIENDA_JUGA_GROUP_IDS').'::'.JText::_('COM_TIENDA_JUGA_GROUP_IDS_TIP'); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                            <?php echo JText::_('COM_TIENDA_JUGA_GROUP_IDS'); ?>:
                        </td>
                        <td>
                            <textarea name="juga_group_csv_add" cols="25"><?php echo @$row->product_parameters->get('juga_group_csv_add'); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td title="<?php echo JText::_('COM_TIENDA_JUGA_GROUP_IDS_REMOVE').'::'.JText::_('COM_TIENDA_JUGA_GROUP_IDS_REMOVE_TIP'); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                            <?php echo JText::_('COM_TIENDA_JUGA_GROUP_IDS_REMOVE'); ?>:
                        </td>
                        <td>
                            <textarea name="juga_group_csv_remove" cols="25"><?php echo @$row->product_parameters->get('juga_group_csv_remove'); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 125px; text-align: right;" class="dsc-key" >
                        </td>
                        <td>
                            <?php echo JText::_('COM_TIENDA_ACTIONS_FOR_WHEN_SUBSCRIPTION_EXPIRES'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td title="<?php echo JText::_('COM_TIENDA_JUGA_GROUP_IDS_EXPIRATION').'::'.JText::_('COM_TIENDA_JUGA_GROUP_IDS_EXPIRATION_TIP'); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                            <?php echo JText::_('COM_TIENDA_JUGA_GROUP_IDS_EXPIRATION'); ?>:
                        </td>
                        <td>
                            <textarea name="juga_group_csv_add_expiration" cols="25"><?php echo @$row->product_parameters->get('juga_group_csv_add_expiration'); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td title="<?php echo JText::_('COM_TIENDA_JUGA_GROUP_IDS_REMOVE_EXPIRATION').'::'.JText::_('COM_TIENDA_JUGA_GROUP_IDS_REMOVE_EXPIRATION_TIP'); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                            <?php echo JText::_('COM_TIENDA_JUGA_GROUP_IDS_REMOVE_EXPIRATION'); ?>:
                        </td>
                        <td>
                            <textarea name="juga_group_csv_remove_expiration" cols="25"><?php echo @$row->product_parameters->get('juga_group_csv_remove_expiration'); ?></textarea>
                        </td>
                    </tr>
                </table>
            <?php else : ?>
                <div class="note">
                    <?php echo JText::_('COM_TIENDA_JUGA_VERSION_NOTICE'); ?>
                </div>
            <?php endif; ?>
            </fieldset>
        </div>
        
        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_TAGS_INTEGRATION'); ?></legend>
            <?php if (Tienda::getClass('TiendaHelperTags', 'helpers.tags')->isInstalled()) : ?>
                <table class="table table-striped table-bordered" style="width: 100%;">
                    <tr>                        
                        <td>
							<div class="note">
		                    	<?php echo JText::_('COM_TIENDA_TAGS_IS_INSTALLED'); ?>
		                	</div>
                        </td>
                    </tr>
                </table>
            <?php else : ?>
                <div class="note">
                    <?php echo JText::_('COM_TIENDA_TAGS_INSTALLATION_NOTICE'); ?>
                </div>
            <?php endif; ?>
            </fieldset>
        </div>

        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_CORE_JOOMLA_USER_INTEGRATION'); ?></legend>
            <table class="table table-striped table-bordered" style="width: 100%;">
                <tr>
                    <td title="<?php echo JText::_('COM_TIENDA_CHANGE_JOOMLA_ACL').'::'.JText::_('COM_TIENDA_CHANGE_JOOMLA_ACL_TIP'); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                        <?php echo JText::_('COM_TIENDA_CHANGE_JOOMLA_ACL'); ?>:
                    </td>
                    <td>
                        <?php  echo TiendaSelect::btbooleanlist( 'core_user_change_gid', 'class="inputbox"', $row->product_parameters->get('core_user_change_gid') ); ?>
                    </td>
                </tr>
                <tr>
                    <td title="<?php echo JText::_('COM_TIENDA_NEW_JOOMLA_ACL').'::'.JText::_('COM_TIENDA_NEW_JOOMLA_ACL_TIP'); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                        <?php echo JText::_('COM_TIENDA_NEW_JOOMLA_ACL'); ?>:
                    </td>
                    <td>
                        <?php
                        Tienda::load( 'TiendaHelperUser', 'helpers.user' );
                        $helper = new TiendaHelperUser();
                        echo $helper->getACLSelectList( $row->product_parameters->get('core_user_new_gid') );
                        ?>
                    </td>
                </tr>
            </table>
            </fieldset>        
        </div>
        
        <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onDisplayProductFormIntegrations', array( $row ) );                    
        ?>
        
        <div style="clear: both;"></div></div>
  <div class="tab-pane" id="panel_advanced"> <div style="clear: both;"></div>
        
        <div class="note">
            <?php echo JText::_('COM_TIENDA_ADVANCED_PANEL_NOTICE'); ?>
        </div>
        
        <div style="clear: both;"></div>
        
        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_PRODUCT_PARAMETERS'); ?></legend>
            <table class="table table-striped table-bordered" style="width: 100%;">
                <tr>
                    <td style="vertical-align: top; width: 100px; text-align: right;" class="dsc-key">
                        <?php echo JText::_('COM_TIENDA_PRODUCT_PARAMS'); ?>:
                    </td>
                    <td>
                        <textarea name="product_params" id="product_params" rows="10" cols="55"><?php echo @$row->product_params; ?></textarea>
                    </td>
                </tr>
                </table>
            </fieldset>
        </div>
        
        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_SQL_FOR_AFTER_PURCHASE'); ?></legend>
            <table class="table table-striped table-bordered" style="width: 100%;">
                <tr>
                    <td title="<?php echo JText::_('COM_TIENDA_PRODUCT_SQL').'::'.JText::_('COM_TIENDA_PRODUCT_SQL_TIP'); ?>" style="width: 100px; text-align: right;" class="key hasTip" >
                        <?php echo JText::_('COM_TIENDA_PRODUCT_SQL'); ?>:
                    </td>
                    <td>
                        <textarea name="product_sql" rows="10" cols="55"><?php echo @$row->product_sql; ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td title="<?php echo JText::_('COM_TIENDA_AVAILABLE_OBJECTS').'::'.JText::_('COM_TIENDA_AVAILABLE_OBJECTS_TIP'); ?>" style="width: 100px; text-align: right;" class="key hasTip" >
                        <?php echo JText::_('COM_TIENDA_AVAILABLE_OBJECTS'); ?>:
                    </td>
                    <td>
                        {user} = JFactory::getUser( <?php echo "$"."order->user_id"; ?> )<br/>
                        {date} = JFactory::getDate()<br/>
                        {request} = JRequest::getVar()<br/>
                        {order} = TiendaTableOrders()<br/>
                        {orderitem} = TiendaTableOrderItems()<br/>
                        {product} = TiendaTableProducts()<br/>
                    </td>
                </tr>
                <tr>
                    <td title="<?php echo JText::_('COM_TIENDA_NORMAL_USAGE').'::'.JText::_('COM_TIENDA_NORMAL_USAGE_TIP'); ?>" style="width: 100px; text-align: right;" class="key hasTip" >
                        <?php echo JText::_('COM_TIENDA_NORMAL_USAGE'); ?>:
                    </td>
                    <td>
                        <br/>
                        <?php echo "{user.name} == JFactory::getUser()->name"; ?><br/>
                        <?php echo "{user.username} == JFactory::getUser()->username"; ?><br/>
                        <?php echo "{user.email} == JFactory::getUser()->email"; ?><br/>
                        <?php echo "{date.toMySQL()} == JFactory::getDate()->toMySQL()"; ?><br/>
                        <?php echo "{request.task} == JRequest::getVar('task');"; ?><br/>
                    </td>
                </tr>
                </table>
            </fieldset>
        </div>

        <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onDisplayProductFormAdvanced', array( $row ) );                    
        ?>
        
        <div style="clear: both;"></div>
        </div>
        
 <?php 
  
    
    // fire plugin event here to enable extending the form's tabs
    JDispatcher::getInstance()->trigger('onAfterDisplayProductFormTabs', array( $tabs, $row ) );
    
 
    ?>
</div>


    <?php
    // fire plugin event here to enable extending the form
    JDispatcher::getInstance()->trigger('onAfterDisplayProductForm', array( $row ) );                    
    ?>
			
	<input type="hidden" name="id" id="product_id" value="<?php echo @$row->product_id; ?>" />
	<input type="hidden" name="task" value="" />
	
</form>

<?php $multiscript = Tienda::getInstance()->get( 'multiupload_script', 0 ); ?>
<script type="text/javascript">
window.addEvent('domready', function(){
<?php switch( $multiscript ) { 
	case '0' : ?>	
	// Check flash version!
	var flash = swfobject.getFlashPlayerVersion();
	if(flash.major < 9 || $('product_id').value == 0 )
	{
		// Use normal uploader
		$('oldUploader').setStyle('display', 'block');
		$('uploadifyImage').setStyle('display', 'none');
		new MultiUpload( $( 'adminForm' ).product_full_image_new, 0, '[{id}]', false, true );
	}
	else
	{
		// Use flash uploader
		$('uploadifyImage').setStyle('display', 'block');
		$('oldUploader').setStyle('display', 'none');
	
	// Uploadify!
    jQuery('#uploadify_file_upload').uploadify({
        'uploader': '<?php echo Tienda::getUrl("js"); ?>uploadify/uploadify.swf',
        'script': '<?php echo JURI::getInstance()->root(true); ?>/index.php',
        'cancelImg': '<?php echo Tienda::getUrl("images"); ?>cancel.png',
        'multi': true,
        'auto': true,
        'fileDataName': 'uploadify_image',
        'fileExt': '*.jpg;*.gif;*.png',
        'fileDesc': 'Image Files (.JPG, .GIF, .PNG)',
        'queueID': 'uploadify-queue',
        'method': 'POST',
        'scriptData': {'option':'com_tienda','view':'products','task':'uploadifyImage','format':'raw','product_id': '<?php echo @$row->product_id ?>'},
        'removeCompleted': false,
        'buttonImage': false,
        'onSelectOnce': function (event, data) {
            jQuery('#uploadify-status-message').text(data.filesSelected + ' files have been added to the queue.');
        },
        'onAllComplete': function (event, data) {
            jQuery('#uploadify-status-message').text(data.filesUploaded + ' files uploaded, ' + data.errors + ' errors.');
            tiendaNewModal('<?php echo JText::_('COM_TIENDA_SAVING_THE_PRODUCT'); ?>');
            submitbutton('apply');
        }
    });
    }
 <?php break; ?>
<?php	case 'multiupload' : ?>	
		$('oldUploader').setStyle('display', 'block');
		$('uploadifyImage').setStyle('display', 'none');
		new MultiUpload( $( 'adminForm' ).product_full_image_new, 0, '[{id}]', false, true );
 <?php break; ?>
<?php	case 'uploadify' : ?>	
		// Use flash uploader
		$('uploadifyImage').setStyle('display', 'block');
		$('oldUploader').setStyle('display', 'none');
	
	// Uploadify!
    jQuery('#uploadify_file_upload').uploadify({
        'uploader': '<?php echo Tienda::getUrl("js"); ?>uploadify/uploadify.swf',
        'script': '<?php echo JURI::getInstance()->root(true); ?>/index.php',
        'cancelImg': '<?php echo Tienda::getUrl("images"); ?>cancel.png',
        'multi': true,
        'auto': true,
        'fileDataName': 'uploadify_image',
        'fileExt': '*.jpg;*.gif;*.png',
        'fileDesc': 'Image Files (.JPG, .GIF, .PNG)',
        'queueID': 'uploadify-queue',
        'method': 'POST',
        'scriptData': {'option':'com_tienda','view':'products','task':'uploadifyImage','format':'raw','product_id': '<?php echo @$row->product_id ?>'},
        'removeCompleted': false,
        'buttonImage': false,
        'onSelectOnce': function (event, data) {
            jQuery('#uploadify-status-message').text(data.filesSelected + ' files have been added to the queue.');
        },
        'onAllComplete': function (event, data) {
            jQuery('#uploadify-status-message').text(data.filesUploaded + ' files uploaded, ' + data.errors + ' errors.');
            tiendaNewModal('<?php echo JText::_('COM_TIENDA_SAVING_THE_PRODUCT'); ?>');
            submitbutton('apply');
        }
    });
 <?php break; ?>
 <?php } ?>
});

// showing/hiding elementes related to pro-rated payments
function showProRatedFields()
{
	val = jQuery('input[name=subscription_prorated]:checked').val();
	if( val == 1 )
	{
		jQuery( '.prorated_unrelated' ).hide( 'fast' );
		jQuery( '.prorated_related' ).show( 'fast' );
		jQuery( '.trial_price' ).text( '<?php echo JText::_('COM_TIENDA_INITIAL_PERIOD_PRICE');?>:' );
	}
	else
	{
		jQuery( '.prorated_unrelated' ).show( 'fast' );
		jQuery( '.prorated_related' ).hide( 'fast' );
		jQuery( '.trial_price' ).text( '<?php echo JText::_('COM_TIENDA_TRIAL_PERIOD_PRICE');?>:' );
	}
}

//showProRatedFields();
</script>