<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('script', 'tienda_admin.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('script', 'Stickman.MultiUpload.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('behavior.tooltip'); ?>
<?php jimport('joomla.html.pane'); ?>
<?php $tabs = &JPane::getInstance( 'tabs' ); ?>
<?php $form = @$this->form; ?>
<?php  $row = @$this->row; JFilterOutput::objectHTMLSafe( $row ); ?>
<?php
Tienda::load( 'TiendaUrl', 'library.url' );
Tienda::load( "TiendaHelperProduct", 'helpers.product' ); 
?>

<script type="text/javascript">
window.addEvent('domready', function(){
	new MultiUpload( $( 'adminForm' ).product_full_image_new, 0, '[{id}]', false, true );
});
</script>

<form id="adminForm" action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >

    <fieldset>
    <legend><?php echo JText::_( "Basic Information" ); ?></legend>
        <div style="float: left;">
        <table class="admintable">
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Name' ); ?>:
                </td>
                <td>
                    <input type="text" name="product_name" id="product_name" value="<?php echo @$row->product_name; ?>" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Alias' ); ?>:
                </td>
                <td>
                    <input name="product_alias" id="product_alias" value="<?php echo @$row->product_alias; ?>" type="text" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'ID' ); ?>:
                </td>
                <td>
                    <?php 
                    if (empty($row->product_id)) 
                    {
                        ?>
                        <div style="color: grey;"><?php echo JText::_( "Automatically Generated" ); ?></div>
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
        <div style="float: left;">
        <table class="admintable">
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Model' ); ?>:
                </td>
                <td>
                    <input type="text" name="product_model" id="product_model" value="<?php echo @$row->product_model; ?>" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'SKU' ); ?>:
                </td>
                <td>
                    <input type="text" name="product_sku" id="product_sku" value="<?php echo @$row->product_sku; ?>" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Enabled' ); ?>:
                </td>
                <td>
                    <?php echo JHTML::_('select.booleanlist', 'product_enabled', '', @$row->product_enabled ); ?>
                </td>
            </tr>
        </table>
        </div>
        <div style="float: left;">
        <table class="admintable">
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Overall Rating' ); ?>:
                </td>
                <td>
                    <?php echo TiendaHelperProduct::getRatingImage( @$row->product_rating ); ?>
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Comments' ); ?>:
                </td>
                <td>
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
                echo TiendaUrl::popup( TiendaHelperProduct::getImage($row->product_id, '', '', 'full', true, false ), TiendaHelperProduct::getImage($row->product_id, 'id', $row->product_name, 'full', false, false, array( 'height'=>80 )), array('update' => false, 'img' => true));
            }
            ?>
        </div>
    </fieldset>

    <div class="reset"></div>
    
    <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onBeforeDisplayProductForm', array( $row ) );                    
    ?>

    <?php 
    // start tab pane
    echo $tabs->startPane( "pane_tienda" );
    
    // Tab
    echo $tabs->startPanel( JText::_( 'Product Properties' ), "panel_product_properties");
    ?>

	<table style="width: 100%">
	<tr>
		<td style="vertical-align: top; width: 65%;">
		
            <fieldset>
            <legend><?php echo JText::_( "Additional Information" ); ?></legend>
            
            <div style='float: left; width: 50%;'>
            <table class="admintable" style="width: 100%;">
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <label for="manufacturer_id">
                        <?php echo JText::_( 'Manufacturer' ); ?>:
                        </label>
                    </td>
                    <td>
                        <?php echo TiendaSelect::manufacturer( @$row->manufacturer_id, 'manufacturer_id', '', 'manufacturer_id', false, true ); ?>
                    </td>
                </tr>
                <?php 
                if (empty($row->product_id)) 
                {
                    // doing a new product, so display a notice
                    ?>
                    <tr>
                        <td width="100" align="right" class="key" style="vertical-align: top;">
                            <?php echo JText::_( 'Product Attributes' ); ?>:
                        </td>
                        <td>
                            <div class="note"><?php echo JText::_( "Click Apply to be able to create product attributes" ); ?></div>
                        </td>
                    </tr>
                    <?php
                } 
                    else
                {
                    // display lightbox link to manage attributes
                    ?>
                    <tr>
                        <td style="width: 100px; text-align: right;" class="key">
                            <?php echo JText::_( 'Product Attributes' ); ?>:
                        </td>
                        <td>
                            [<?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=products&task=setattributes&id=".$row->product_id."&tmpl=component", JText::_( "Set Attributes" ) ); ?>]
                            <?php $attributes = TiendaHelperProduct::getAttributes( $row->product_id ); ?>
                            <div id="current_attributes">
                                <?php foreach (@$attributes as $attribute) : ?>
                                    [<a href="<?php echo "index.php?option=com_tienda&view=productattributes&task=delete&cid[]=".$attribute->productattribute_id."&return=".base64_encode("index.php?option=com_tienda&view=products&task=edit&id=".$row->product_id); ?>">
                                        <?php echo JText::_("Remove"); ?>
                                    </a>]
                                    [<?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=products&task=setattributeoptions&id=".$attribute->productattribute_id."&tmpl=component", JText::_( "Set Attribute Options" ) ); ?>]
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
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'Requires Shipping' ); ?>:
                    </td>
                    <td>
                        <?php // Make the shipping options div only display if yes ?>
                        <input onclick="tiendaShowHideDiv('shipping_options');" type="radio" <?php if (empty($row->product_ships)) { echo "checked='checked'"; } ?> value="0" name="product_ships" id="product_ships0"/><label for="product_ships0"><?php echo JText::_("No"); ?></label>
                        <input onclick="tiendaShowHideDiv('shipping_options');" type="radio" <?php if (!empty($row->product_ships)) { echo "checked='checked'"; } ?> value="1" name="product_ships" id="product_ships1"/><label for="product_ships1"><?php echo JText::_("Yes"); ?></label>
                    </td>
                </tr>
                </table>
                </div>
                
                <?php // Only display if product ships ?>
                <div id="shipping_options" style='float: right; width: 50%; <?php if (empty($row->product_ships)) { echo "display: none;"; } ?>' >                
                <table class="admintable" style="width: 100%;">
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <label for="product_weight">
                        <?php echo JText::_( 'Weight' ); ?>:
                        </label>
                    </td>
                    <td>
                        <input type="text" name="product_weight" id="product_weight" value="<?php echo @$row->product_weight; ?>" size="30" maxlength="250" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <label for="product_length">
                        <?php echo JText::_( 'Length' ); ?>:
                        </label>
                    </td>
                    <td>
                        <input type="text" name="product_length" id="product_length" value="<?php echo @$row->product_length; ?>" size="30" maxlength="250" />
                    </td>
                </tr>

                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <label for="product_width">
                        <?php echo JText::_( 'Width' ); ?>:
                        </label>
                    </td>
                    <td>
                        <input type="text" name="product_width" id="product_width" value="<?php echo @$row->product_width; ?>" size="30" maxlength="250" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <label for="product_height">
                        <?php echo JText::_( 'Height' ); ?>:
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
            <legend><?php echo JText::_( "Long and Short Description" ); ?></legend>
            
            <table class="admintable" style="width: 100%;">
				<tr>
					<td style="width: 100px; text-align: right; vertical-align:top;" class="key">
						<?php echo JText::_( 'Full Description' ); ?>:
					</td>
					<td>
						<?php $editor = &JFactory::getEditor(); ?>
						<?php echo $editor->display( 'product_description',  @$row->product_description, '100%', '300', '75', '20' ) ; ?>
					</td>
				</tr>
                <tr>
                    <td style="width: 100px; text-align: right; vertical-align:top;" class="key">
                        <?php echo JText::_( 'Short Description' ); ?>:
                    </td>
                    <td>
                        <?php $editor = &JFactory::getEditor(); ?>
                        <?php echo $editor->display( 'product_description_short',  @$row->product_description_short, '100%', '300', '75', '10' ) ; ?>
                    </td>
                </tr>
            </table>
            </fieldset>
		    
            <?php
                // fire plugin event here to enable extending the form
                JDispatcher::getInstance()->trigger('onAfterDisplayProductFormMainColumn', array( $row ) );                    
            ?>
		    
		</td>
		<td style="max-width: 35%; min-width: 35%; width: 35%; vertical-align: top;">

            <fieldset>
            <legend><?php echo JText::_( "Publication Dates" ); ?></legend>
            <table class="admintable" style="width: 100%;">
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'Publish Up' ); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::calendar( @$row->publish_date, "publish_date", "publish_date", '%Y-%m-%d %H:%M:%S', array('size'=>25) ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'Publish Down' ); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::calendar( @$row->unpublish_date, "unpublish_date", "unpublish_date", '%Y-%m-%d %H:%M:%S', array('size'=>25) ); ?>
                    </td>
                </tr>
            </table>
            </fieldset>

            <fieldset>
            <legend><?php echo JText::_( "Categories" ); ?></legend>
            <table class="admintable" style="width: 100%;">
                <?php 
                if (empty($row->product_id)) 
                {
                    // doing a new product, so collect default info
                    ?>
                    <tr>
                        <td width="100" align="right" class="key" style="vertical-align: top;">
                            <label for="category_id">
                            <?php echo JText::_( 'Product Category' ); ?>:
                            </label>
                        </td>
                        <td>
                            <?php echo TiendaSelect::category( '', 'category_id', '', 'category_id' ); ?>
                            <div class="note"><?php echo JText::_( "Set Initial Category Now Additional Ones Later" ); ?></div>
                        </td>
                    </tr>
                    <?php
                } 
                    else
                {
                    // display lightbox link to manage categories
                    ?>
                    <tr>
                        <td style="width: 100px; text-align: right;" class="key">
                            <label for="product_categories">
                            <?php echo JText::_( 'Categories' ); ?>:
                            </label>
                        </td>
                        <td>
                            <?php Tienda::load( 'TiendaHelperCategory', 'helpers.category' ); ?>
                            <?php Tienda::load( 'TiendaUrl', 'library.url' ); ?>
                            [<?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=products&task=selectcategories&id=".$row->product_id."&tmpl=component", JText::_( "Select Categories" )); ?>]
                            <?php $categories = TiendaHelperProduct::getCategories( $row->product_id ); ?>
                            <div id="current_categories">
                                <?php foreach (@$categories as $category) : ?>
                                    [<a href="<?php echo "index.php?option=com_tienda&view=products&task=selected_disable&id=".$row->product_id."&cid[]=".$category."&return=".base64_encode("index.php?option=com_tienda&view=products&task=edit&id=".$row->product_id); ?>">
                                        <?php echo JText::_("Remove"); ?>
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
            <legend><?php echo JText::_( "Images" ); ?></legend>
            <table class="admintable" style="width: 100%;">            
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <label for="product_full_image">
                        <?php echo JText::_( 'Current Default Image' ); ?>:
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
                    <td style="width: 100px; text-align: right;" class="key">
                        <label for="product_image_gallery">
                        <?php echo JText::_( 'Current Images' ); ?>:
                        </label>
                    </td>
                    <td>
                        [
                        <?php
                        echo TiendaUrl::popup( "index.php?option=com_tienda&view=products&task=viewGallery&id=".@$row->product_id."&tmpl=component", JText::_( "View Gallery" ) ); 
                        ?>
                        ]
                        <br/>
                        <?php $images = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getGalleryImages( TiendaHelperProduct::getGalleryPath( @$row->product_id ) ); ?> 
                        <?php foreach (@$images as $image) : ?>
                            [<a href="<?php echo "index.php?option=com_tienda&view=products&task=deleteImage&product_id=".@$row->product_id."&image=".$image."&return=".base64_encode("index.php?option=com_tienda&view=products&task=edit&id=".@$row->product_id); ?>">
                                <?php echo JText::_("Remove"); ?>
                            </a>]
                            <?php echo $image; ?>
                            <br/>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <label for="product_full_image_new">
                        <?php echo JText::_( 'Upload New Image' ); ?>:
                        </label>
                    </td>
                    <td>
                        <div class="multiupload"> 
                        <input name="product_full_image_new" type="file" size="40" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top; width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'Images Gallery Path Override' ); ?>:
                    </td>
                    <td>
                        <input name="product_images_path" id="product_images_path" value="<?php echo @$row->product_images_path; ?>" size="75" maxlength="255" type="text" />
                        <div class="note">
                            <?php echo JText::_( "If no image path override is specified message" ); ?>
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
            <legend><?php echo JText::_( "Files" ); ?></legend>
            <table class="admintable" style="width: 100%;">
             
                <?php 
                if (empty($row->product_id)) 
                {
                    // doing a new product, so display a notice
                    ?>
                    <tr>
                        <td width="100" align="right" class="key" style="vertical-align: top;">
                            <?php echo JText::_( 'Product Files' ); ?>:
                        </td>
                        <td>
                            <div class="note"><?php echo JText::_( "Click Apply to be able to add files to the product" ); ?></div>
                        </td>
                    </tr>
                    <?php
                } 
                    else
                {
                    // display lightbox link to manage files
                    ?>
                    <tr>
                        <td style="width: 100px; text-align: right;" class="key">
                            <?php echo JText::_( 'Product Files' ); ?>:
                        </td>
                        <td>
                            <?php
                            Tienda::load( 'TiendaUrl', 'library.url' );
                            Tienda::load( "TiendaHelperProduct", 'helpers.product' ); 
                            ?>
                            [<?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=products&task=setfiles&id=".$row->product_id."&tmpl=component", JText::_( "Manage Files" ) ); ?>]
                            <?php $files = TiendaHelperProduct::getFiles( $row->product_id ); ?>
                            <div id="current_files">
                                <?php foreach (@$files as $file) : ?>
                                    [<a href="<?php echo "index.php?option=com_tienda&view=productfiles&task=delete&cid[]=".$file->productfile_id."&return=".base64_encode("index.php?option=com_tienda&view=products&task=edit&id=".$row->product_id); ?>">
                                        <?php echo JText::_("Remove"); ?>
                                    </a>]
                                    <?php echo $file->productfile_name; ?>
                                    <br/>
                                <?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; width: 100px; text-align: right;" class="key">
                            <?php echo JText::_( 'Product Files Path Override' ); ?>:
                        </td>
                        <td>
                            <input name="product_files_path" id="product_files_path" value="<?php echo @$row->product_files_path; ?>" size="75" maxlength="255" type="text" />
                            <div class="note">
                                <?php echo JText::_( "If no file path override is specified message" ); ?>
                                <ul>
                                    <li>/images/com_tienda/files/[SKU]</li>
                                    <li>/images/com_tienda/files/[ID]</li>
                                </ul>
                                <?php echo JText::_( "Changing file path note" ); ?>
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
	</table>

    <?php 
    echo $tabs->endPanel();
    
    // Tab
    echo $tabs->startPanel( JText::_( 'Pricing and Inventory' ), "panel_pricing"); 
    ?>

        <div style="clear: both;"></div>
        
        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_( "Prices and Inventory" ); ?></legend>
            
            <table class="admintable">
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'Item for Sale' ); ?>
                    </td>
                    <td>
                        <?php echo JHTML::_('select.booleanlist', 'product_notforsale', '', @$row->product_notforsale, 'no', 'yes' ); ?>
                    </td>
                </tr>
                <?php
				Tienda::load( "TiendaHelperProduct", 'helpers.product' );
				$prices = TiendaHelperProduct::getPrices( $row->product_id );
                if (empty($row->product_id) || empty($prices)) 
                {
                    // new product (or no prices set) - ask for normal price
                    ?>
                    <tr>
                        <td width="100" align="right" class="key" style="vertical-align: top;">
                            <label for="product_price">
                            <?php echo JText::_( 'Normal Price' ); ?>:
                            </label>
                        </td>
                        <td>
                            <input type="text" name="product_price" id="product_price" value="<?php echo @$row->product_price; ?>" size="25" maxlength="25" />
                            <div class="note"><?php echo JText::_( "Set Normal Price Now Special Prices Later" ); ?></div>
                        </td>
                    </tr>
                    <?php
                } 
                    else
                {
                    // display lightbox link to manage prices
                    ?>
                    <tr>
                        <td style="width: 100px; text-align: right;" class="key">
                            <label for="product_prices">
                            <?php echo JText::_( 'Prices' ); ?>:
                            </label>
                        </td>
                        <td>
                            <?php
                            Tienda::load( 'TiendaUrl', 'library.url' );
                            ?>
                            [<?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=products&task=setprices&id=".$row->product_id."&tmpl=component", JText::_( "Set Prices" ) ); ?>]
                            <div id="current_prices">
                                <?php foreach (@$prices as $price) : ?>
                                    [<a href="<?php echo $price->link_remove."&return=".base64_encode("index.php?option=com_tienda&view=products&task=edit&id=".$row->product_id); ?>">
                                        <?php echo JText::_("Remove"); ?>
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
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'Tax Class' ); ?>:
                    </td>
                    <td>
                        <?php echo TiendaSelect::taxclass( @$row->tax_class_id, 'tax_class_id', '', 'tax_class_id', false ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'Check Product Inventory' ); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::_('select.booleanlist', 'product_check_inventory', '', @$row->product_check_inventory ); ?>
                    </td>
                </tr>
                
                
                <?php
                if (empty($row->product_check_inventory) && !empty($row->product_id))
                {
                ?>
                <tr>
                        <td width="100" align="right" class="key" style="vertical-align: top;">
                            <?php echo JText::_( 'Product Quantities' ); ?>:
                        </td>
                        <td>
                            <div class="note"><?php echo JText::_( "Product Inventory is disabled. Enable it to set Quantities" ); ?></div>
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
                            <td width="100" align="right" class="key" style="vertical-align: top;">
                                <?php echo JText::_( 'Starting Quantity' ); ?>:
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
                            <td style="width: 100px; text-align: right;" class="key">
                                <?php echo JText::_( 'Product Quantities' ); ?>:
                            </td>
                            <td>
                                <?php
                                echo $row->product_quantity;
                                echo "<br/>";
                                Tienda::load( 'TiendaUrl', 'library.url' );
                                $options = array('update' => true ); 
                                ?>
                                [<?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=products&task=setquantities&id=".$row->product_id."&tmpl=component", JText::_( "Set Quantities" ), $options); ?>]
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
                <tr>
                    <td title="<?php echo JText::_("Purchase Quantity Restriction").'::'.JText::_( "Purchase Quantity Restriction Tip" ); ?>" style="width: 100px; text-align: right;" class="key hasTip" >
                        <?php echo JText::_( 'Purchase Quantity Restriction' ); ?>:
                    </td>
                    <td>
                    	<input onclick="tiendaShowHideDiv('quantity_restrictions');" type="radio" <?php if (empty($row->quantity_restriction)) { echo "checked='checked'"; } ?> value="0" name="quantity_restriction" id="quantity_restriction0"/><label for="quantity_restriction0"><?php echo JText::_("No"); ?></label>
                        <input onclick="tiendaShowHideDiv('quantity_restrictions');" type="radio" <?php if (!empty($row->quantity_restriction)) { echo "checked='checked'"; } ?> value="1" name="quantity_restriction" id="quantity_restriction1"/><label for="quantity_restriction1"><?php echo JText::_("Yes"); ?></label>
                        	 <?php // Only display if quantity restriction ?>
                <div id="quantity_restrictions" style='float: right; width: 50%; <?php if (empty($row->quantity_restriction)) { echo "display: none;"; } ?>' >                
                <table class="admintable" style="width: 100%;">
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <label for="quantity_min">
                        <?php echo JText::_( 'Minimum Quantity' ); ?>:
                        </label>
                    </td>
                    <td>
                        <input type="text" name="quantity_min" id="quantity_min" value="<?php echo @$row->quantity_min; ?>" size="30" maxlength="250" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <label for="quantity_max">
                        <?php echo JText::_( 'Maxium Quantity' ); ?>:
                        </label>
                    </td>
                    <td>
                        <input type="text" name="quantity_max" id="quantity_max" value="<?php echo @$row->quantity_max; ?>" size="30" maxlength="250" />
                    </td>
                </tr>

                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <label for="quantity_step">
                        <?php echo JText::_( 'Step Quantity' ); ?>:
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
            </table>
            </fieldset>
            
            <fieldset>
            <legend><?php echo JText::_( "Product List Price" ); ?></legend>
            <table class="admintable">
                <tr>
                    <td title="<?php echo JText::_("Display Product List Price").'::'.JText::_( "Display Product List Price Tip" ); ?>" style="width: 100px; text-align: right;" class="key hasTip" >
                        <?php echo JText::_( 'Display Product List Price' ); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::_('select.booleanlist', 'product_listprice_enabled', '', @$row->product_listprice_enabled ); ?>
                    </td>
                </tr>
                <tr>
                    <td title="<?php echo JText::_("Product List Price").'::'.JText::_( "Product List Price Tip" ); ?>" style="width: 100px; text-align: right;" class="key hasTip" >
                        <?php echo JText::_( 'Product List Price' ); ?>
                    </td>
                    <td>
                        <input type="text" name="product_listprice" value="<?php echo @$row->product_listprice; ?>" size="15" maxlength="11" />
                    </td>
                </tr>

            </table>
            </fieldset>
        </div>
        
        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_( "Non Recurring Subscription" ); ?></legend>
            
            <div class="note"><?php echo JText::_( "Non Recurring Subscription NOTE" ); ?></div>
            
            <table class="admintable" style="width: 100%;">
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_( 'Product Creates Subscription' ); ?>:
                    </td>
                    <td>
                        <input type="radio" <?php if (empty($row->product_subscription)) { echo "checked='checked'"; } ?> value="0" name="product_subscription" id="product_subscription0"/><label for="product_subscription0"><?php echo JText::_("No"); ?></label>
                        <input type="radio" <?php if (!empty($row->product_subscription)) { echo "checked='checked'"; } ?> value="1" name="product_subscription" id="product_subscription1"/><label for="product_subscription1"><?php echo JText::_("Yes"); ?></label>
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_( 'Lifetime Subscription' ); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::_('select.booleanlist', 'subscription_lifetime', '', @$row->subscription_lifetime ); ?>
                    </td>
                </tr>
                <tr>
                    <td title="<?php echo JText::_("Subscription Period Interval").'::'.JText::_( "Subscription Period Interval Tip" ); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                        <?php echo JText::_( 'Subscription Period Interval' ); ?>:
                    </td>
                    <td>
                        <input name="subscription_period_interval" id="subscription_period_interval" value="<?php echo @$row->subscription_period_interval; ?>" size="10" maxlength="10" type="text" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_( 'Subscription Period Unit' ); ?>:
                    </td>
                    <td>
                        <?php echo TiendaSelect::periodUnit( @$row->subscription_period_unit, 'subscription_period_unit' ); ?>
                    </td>
                </tr>          
            </table>
            </fieldset>
            
            <fieldset>
            <legend><?php echo JText::_( "Subscription with Recurring Charges" ); ?></legend>
            <table class="admintable" style="width: 100%;">
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_( 'Product Charges Recur' ); ?>:
                    </td>
                    <td>
                        <input type="radio" <?php if (empty($row->product_recurs)) { echo "checked='checked'"; } ?> value="0" name="product_recurs" id="product_recurs0"/><label for="product_recurs0"><?php echo JText::_("No"); ?></label>
                        <input type="radio" <?php if (!empty($row->product_recurs)) { echo "checked='checked'"; } ?> value="1" name="product_recurs" id="product_recurs1"/><label for="product_recurs1"><?php echo JText::_("Yes"); ?></label>
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_( 'Number of Recurring Charges' ); ?>:
                    </td>
                    <td>
                        <input name="recurring_payments" id="recurring_payments" value="<?php echo @$row->recurring_payments; ?>" size="10" maxlength="10" type="text" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_( 'Recurring Period Interval' ); ?>:
                    </td>
                    <td>
                        <input name="recurring_period_interval" id="recurring_period_interval" value="<?php echo @$row->recurring_period_interval; ?>" size="10" maxlength="10" type="text" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_( 'Recurring Period Units' ); ?>:
                    </td>
                    <td>
                        <?php echo TiendaSelect::periodUnit( @$row->recurring_period_unit, 'recurring_period_unit' ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_( 'Trial Period' ); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::_('select.booleanlist', 'recurring_trial', '', @$row->recurring_trial ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_( 'Trial Period Price' ); ?>:
                    </td>
                    <td>
                        <input name="recurring_trial_price" id="recurring_trial_price" value="<?php echo @$row->recurring_trial_price; ?>" size="10" maxlength="10" type="text" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_( 'Trial Period Interval' ); ?>:
                    </td>
                    <td>
                        <input name="recurring_trial_period_interval" id="recurring_trial_period_interval" value="<?php echo @$row->recurring_trial_period_interval; ?>" size="10" maxlength="10" type="text" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 125px; text-align: right;" class="key">
                        <?php echo JText::_( 'Trial Period Units' ); ?>:
                    </td>
                    <td>
                        <?php echo TiendaSelect::periodUnit( @$row->recurring_trial_period_unit, 'recurring_trial_period_unit' ); ?>
                    </td>
                </tr>          
            </table>
            </fieldset>
        </div>
    
        <div style="clear: both;"></div>
    
    <?php 
    echo $tabs->endPanel();

    // Tab
    echo $tabs->startPanel( JText::_( 'Related Items' ), "panel_relations"); 
    ?>
        <div style="clear: both;"></div>
        
        <div style="width: 100%;">
            <fieldset>
            <legend><?php echo JText::_( "Add New Relationship" ); ?></legend>
                <div id="new_relationship" style="float: left;">
                    <?php echo TiendaSelect::relationship('', 'new_relationship_type'); ?>
                    <?php echo JText::_( "Product ID" ).": "; ?>
                    <input name="new_relationship_productid_to" size="15" type="text" />
                    <input name="new_relationship_productid_from" value="<?php echo @$row->product_id; ?>" type="hidden" />
                    <input value="<?php echo JText::_( "Add" ); ?>" type="button" onclick="tiendaAddRelationship('existing_relationships', '<?php echo JText::_( "Updating Relationships" ); ?>');" />
                </div>
                <div style="clear: both;"></div>
            </fieldset>
        </div>
        
        <div style="width: 100%;">
            <fieldset>
            <legend><?php echo JText::_( "Existing Relationships" ); ?></legend>
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
    
    <?php 
    echo $tabs->endPanel();
    
    // Tab
    echo $tabs->startPanel( JText::_( 'Display' ), "panel_display"); 
    ?>
        <div style="clear: both;"></div>
        
        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_( "Template" ); ?></legend>
            <table class="admintable" style="width: 100%;">
                <tr>
                    <td style="vertical-align: top; width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'Product Layout File' ); ?>:
                    </td>
                    <td>
                        <?php echo TiendaSelect::productlayout( @$row->product_layout, 'product_layout' ); ?>
                        <div class="note">
                            <?php echo JText::_( "PRODUCT LAYOUT FILE DESC" ); ?>
                        </div>                        
                    </td>
                </tr>
            </table>
            </fieldset>
        </div>
        
        <div style="float: right; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_( "Post Purchase Article" ); ?></legend>
            <table class="admintable" style="width: 100%;">
                <tr>
                    <td style="vertical-align: top; width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'Select an Article to Display After Purchase' ); ?>:
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
    
    <?php 
    echo $tabs->endPanel();
    
    // Tab
    echo $tabs->startPanel( JText::_( 'Integrations' ), "panel_integrations"); 
    ?>

        <div style="clear: both;"></div>
        
        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_( "Amigos Integration" ); ?></legend>
            <?php if (Tienda::getClass('TiendaHelperAmigos', 'helpers.amigos')->isInstalled()) : ?>
                <table class="admintable" style="width: 100%;">
                    <tr>
                        <td style="width: 125px; text-align: right;" class="key hasTip" title="<?php echo JText::_("Commission Rate Override").'::'.JText::_( "Commission Rate Override Tip" ); ?>" >
                            <?php echo JText::_( 'Commission Rate Override' ); ?>:
                        </td>
                        <td>
                            <input name="amigos_commission_override" id="amigos_commission_override" value="<?php echo @$row->product_parameters->get('amigos_commission_override'); ?>" size="10" maxlength="10" type="text" />
                        </td>
                    </tr>
                </table>
            <?php else : ?>
                <div class="note">
                    <?php echo JText::_( "Amigos Installation Notice" ); ?>
                </div>
            <?php endif; ?>
            </fieldset>
        </div>
        
        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_( "Billets Integration" ); ?></legend>
            
            <?php if (Tienda::getClass('TiendaHelperBillets', 'helpers.billets')->isInstalled()) : ?>
                <table class="admintable" style="width: 100%;">
                    <tr>
                        <td title="<?php echo JText::_("Ticket Limit Increase").'::'.JText::_( "Ticket Limit Increase Tip" ); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                            <?php echo JText::_( 'Ticket Limit Increase' ); ?>:
                        </td>
                        <td>
                            <input name="billets_ticket_limit_increase" value="<?php echo @$row->product_parameters->get('billets_ticket_limit_increase'); ?>" size="10" maxlength="10" type="text" />
                        </td>
                    </tr>
                    <tr>
                        <td title="<?php echo JText::_("Excludes User From Ticket Limits").'::'.JText::_( "Excludes User From Ticket Limits Tip" ); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                            <?php echo JText::_( 'Excludes User From Ticket Limits' ); ?>:
                        </td>
                        <td>
                            <?php echo JHTML::_('select.booleanlist', 'billets_ticket_limit_exclusion', 'class="inputbox"', $row->product_parameters->get('billets_ticket_limit_exclusion') ); ?>
                        </td>
                    </tr>
                    <tr>
                        <td title="<?php echo JText::_("Hour Limit Increase").'::'.JText::_( "Hour Limit Increase Tip" ); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                            <?php echo JText::_( 'Hour Limit Increase' ); ?>:
                        </td>
                        <td>
                            <input name="billets_hour_limit_increase" value="<?php echo @$row->product_parameters->get('billets_hour_limit_increase'); ?>" size="10" maxlength="10" type="text" />
                        </td>
                    </tr>
                    <tr>
                        <td title="<?php echo JText::_("Excludes User From Hour Limits").'::'.JText::_( "Excludes User From Hour Limits Tip" ); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                            <?php echo JText::_( 'Excludes User From Hour Limits' ); ?>:
                        </td>
                        <td>
                            <?php echo JHTML::_('select.booleanlist', 'billets_hour_limit_exclusion', 'class="inputbox"', $row->product_parameters->get('billets_hour_limit_exclusion') ); ?>
                        </td>
                    </tr>
                </table>
            <?php else : ?>
                <div class="note">
                    <?php echo JText::_( "Billets Version Notice" ); ?>
                </div>
            <?php endif; ?>
            </fieldset>
        </div>

        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_( "JUGA Integration" ); ?></legend>
            
            <?php if (Tienda::getClass('TiendaHelperJuga', 'helpers.juga')->isInstalled()) : ?>
                <table class="admintable" style="width: 100%;">
                    <tr>
                        <td title="<?php echo JText::_("JUGA Group IDs").'::'.JText::_( "JUGA Group IDs Tip" ); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                            <?php echo JText::_( 'JUGA Group IDs' ); ?>:
                        </td>
                        <td>
                            <textarea name="juga_group_csv_add" cols="25"><?php echo @$row->product_parameters->get('juga_group_csv_add'); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td title="<?php echo JText::_("JUGA Group IDs REMOVE").'::'.JText::_( "JUGA Group IDs REMOVE Tip" ); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                            <?php echo JText::_( 'JUGA Group IDs REMOVE' ); ?>:
                        </td>
                        <td>
                            <textarea name="juga_group_csv_remove" cols="25"><?php echo @$row->product_parameters->get('juga_group_csv_remove'); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 125px; text-align: right;" class="key" >
                        </td>
                        <td>
                            <?php echo JText::_( "Actions for When Subscription Expires" ); ?>
                        </td>
                    </tr>
                    <tr>
                        <td title="<?php echo JText::_("JUGA Group IDs EXPIRATION").'::'.JText::_( "JUGA Group IDs EXPIRATION Tip" ); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                            <?php echo JText::_( 'JUGA Group IDs EXPIRATION' ); ?>:
                        </td>
                        <td>
                            <textarea name="juga_group_csv_add_expiration" cols="25"><?php echo @$row->product_parameters->get('juga_group_csv_add_expiration'); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td title="<?php echo JText::_("JUGA Group IDs REMOVE EXPIRATION").'::'.JText::_( "JUGA Group IDs REMOVE EXPIRATION Tip" ); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                            <?php echo JText::_( 'JUGA Group IDs REMOVE EXPIRATION' ); ?>:
                        </td>
                        <td>
                            <textarea name="juga_group_csv_remove_expiration" cols="25"><?php echo @$row->product_parameters->get('juga_group_csv_remove_expiration'); ?></textarea>
                        </td>
                    </tr>
                </table>
            <?php else : ?>
                <div class="note">
                    <?php echo JText::_( "Juga Version Notice" ); ?>
                </div>
            <?php endif; ?>
            </fieldset>
        </div>
        
        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_( "AmbraSubscriptions Integration" ); ?></legend>
            <?php if (Tienda::getClass('TiendaHelperAmbrasubs', 'helpers.ambrasubs')->isInstalled()) : ?>
                <table class="admintable" style="width: 100%;">
                    <tr>
                        <td title="<?php echo JText::_("Associated Ambrasubs Subscription Type").'::'.JText::_( "Associated Ambrasubs Subscription Type Tip" ); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                            <?php echo JText::_( 'Associated Ambrasubs Subscription Type' ); ?>:
                        </td>
                        <td>
                            <?php echo TiendaHelperAmbrasubs::selectTypes( $row->product_parameters->get('ambrasubs_type_id'), 'ambrasubs_type_id' ); ?>
                        </td>
                    </tr>
                </table>
            <?php else : ?>
                <div class="note">
                    <?php echo JText::_( "Ambrasubs Installation Notice" ); ?>
                </div>
            <?php endif; ?>
            </fieldset>
        </div>

        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_( "Core Joomla User Integration" ); ?></legend>
            <table class="admintable" style="width: 100%;">
                <tr>
                    <td title="<?php echo JText::_("Change Joomla ACL").'::'.JText::_( "Change Joomla ACL Tip" ); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                        <?php echo JText::_( 'Change Joomla ACL' ); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::_('select.booleanlist', 'core_user_change_gid', 'class="inputbox"', $row->product_parameters->get('core_user_change_gid') ); ?>
                    </td>
                </tr>
                <tr>
                    <td title="<?php echo JText::_("New Joomla ACL").'::'.JText::_( "New Joomla ACL Tip" ); ?>" style="width: 125px; text-align: right;" class="key hasTip" >
                        <?php echo JText::_( 'New Joomla ACL' ); ?>:
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
        
        <div style="clear: both;"></div>
    <?php 
    echo $tabs->endPanel();
    
    // Tab
    echo $tabs->startPanel( JText::_( 'Advanced' ), "panel_advanced"); 
    ?>
        <div style="clear: both;"></div>
        
        <div class="note">
            <?php echo JText::_( "Advanced Panel Notice" ); ?>
        </div>
        
        <div style="clear: both;"></div>
        
        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_( "Product Parameters" ); ?></legend>
            <table class="admintable" style="width: 100%;">
                <tr>
                    <td style="vertical-align: top; width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'Product Params' ); ?>:
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
            <legend><?php echo JText::_( "SQL for After Purchase" ); ?></legend>
            <table class="admintable" style="width: 100%;">
                <tr>
                    <td title="<?php echo JText::_("Product SQL").'::'.JText::_( "Product SQL Tip" ); ?>" style="width: 100px; text-align: right;" class="key hasTip" >
                        <?php echo JText::_( 'Product SQL' ); ?>:
                    </td>
                    <td>
                        <textarea name="product_sql" rows="10" cols="55"><?php echo @$row->product_sql; ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td title="<?php echo JText::_("Available Objects").'::'.JText::_( "Available Objects Tip" ); ?>" style="width: 100px; text-align: right;" class="key hasTip" >
                        <?php echo JText::_( 'Available Objects' ); ?>:
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
                    <td title="<?php echo JText::_("Normal Usage").'::'.JText::_( "Normal Usage Tip" ); ?>" style="width: 100px; text-align: right;" class="key hasTip" >
                        <?php echo JText::_( 'Normal Usage' ); ?>:
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
        
    <?php 
    echo $tabs->endPanel();
    
    // fire plugin event here to enable extending the form's tabs
    JDispatcher::getInstance()->trigger('onAfterDisplayProductFormTabs', array( $tabs, $row ) );
    
    echo $tabs->endPane();
    ?>

    <?php
    // fire plugin event here to enable extending the form
    JDispatcher::getInstance()->trigger('onAfterDisplayProductForm', array( $row ) );                    
    ?>
			
	<input type="hidden" name="id" value="<?php echo @$row->product_id; ?>" />
	<input type="hidden" name="task" value="" />
	
</form>