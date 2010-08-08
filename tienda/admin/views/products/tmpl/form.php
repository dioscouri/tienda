<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('script', 'Stickman.MultiUpload.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('behavior.tooltip'); ?>
<?php jimport('joomla.html.pane'); ?>
<?php $tabs = &JPane::getInstance( 'tabs' ); ?>
<?php $form = @$this->form; ?>
<?php 
$row = @$this->row;
JFilterOutput::objectHTMLSafe( $row );
?>
<script type="text/javascript">
window.addEvent('domready', function(){
	new MultiUpload( $( 'adminForm' ).product_full_image_new, 0, '[{id}]', false, true );
});
</script>

<form id="adminForm" action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >

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
            <legend><?php echo JText::_( "Basic Information" ); ?></legend>
			<table class="admintable">
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'Name' ); ?>:
                    </td>
                    <td>
                        <input type="text" name="product_name" id="product_name" value="<?php echo @$row->product_name; ?>" size="48" maxlength="250" />
                    </td>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'Enabled' ); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::_('select.booleanlist', 'product_enabled', '', @$row->product_enabled ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'Alias' ); ?>:
                    </td>
                    <td>
                        <input name="product_alias" id="product_alias" value="<?php echo @$row->product_alias; ?>" type="text" size="48" maxlength="250" />
                    </td>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'ID' ); ?>:
                    </td>
                    <td>
                        <?php 
                        if (empty($row->product_id)) 
                        {
                            ?>
                            <div class="note"><?php echo JText::_( "Automatically Generated" ); ?></div>
                            <?php
                        }
                        else
                        {
                            echo @$row->product_id;
                        }
                        ?>
                    </td>
                </tr>
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<label for="product_model">
						<?php echo JText::_( 'Model' ); ?>:
						</label>
					</td>
					<td>
						<input type="text" name="product_model" id="product_model" value="<?php echo @$row->product_model; ?>" size="48" maxlength="250" />
					</td>
					<td style="width: 100px; text-align: right;" class="key">
						<?php echo JText::_( 'SKU' ); ?>:
					</td>
					<td>
						<input type="text" name="product_sku" id="product_sku" value="<?php echo @$row->product_sku; ?>" size="48" maxlength="250" />
					</td>
				</tr>
			</table>
			
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
			
            <fieldset>
            <legend><?php echo JText::_( "Other Information" ); ?></legend>
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
                            <?php
                            Tienda::load( 'TiendaUrl', 'library.url' );
                            Tienda::load( "TiendaHelperProduct", 'helpers.product' ); 
                            ?>
                            [<?php echo TiendaUrl::popup( "index.php?option=com_tienda&controller=products&task=setattributes&id=".$row->product_id."&tmpl=component", "Set Attributes" ); ?>]
                            <?php $attributes = TiendaHelperProduct::getAttributes( $row->product_id ); ?>
                            <div id="current_attributes">
                                <?php foreach (@$attributes as $attribute) : ?>
                                    [<a href="<?php echo "index.php?option=com_tienda&controller=productattributes&task=delete&cid[]=".$attribute->productattribute_id."&return=".base64_encode("index.php?option=com_tienda&controller=products&task=edit&id=".$row->product_id); ?>">
                                        <?php echo JText::_("Remove"); ?>
                                    </a>]
                                    [<?php echo TiendaUrl::popup( "index.php?option=com_tienda&controller=products&task=setattributeoptions&id=".$attribute->productattribute_id."&tmpl=component", "Set Attribute Options" ); ?>]
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
                <tr>
                    <td style="width: 100px; text-align: right;">
                        
                    </td>
                    <td>
                        <?php // Only display if product ships ?>
                        <div id="shipping_options" style='width: 100%; <?php if (empty($row->product_ships)) { echo "display: none;"; } ?>' >
                        <table class="admintable">
                        <tr>
                            <td style="width: 100px; text-align: right;" class="key">
                                <label for="product_weight">
                                <?php echo JText::_( 'Weight' ); ?>:
                                </label>
                            </td>
                            <td>
                                <input type="text" name="product_weight" id="product_weight" value="<?php echo @$row->product_weight; ?>" size="48" maxlength="250" />
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 100px; text-align: right;" class="key">
                                <label for="product_length">
                                <?php echo JText::_( 'Length' ); ?>:
                                </label>
                            </td>
                            <td>
                                <input type="text" name="product_length" id="product_length" value="<?php echo @$row->product_length; ?>" size="48" maxlength="250" />
                            </td>
                        </tr>
        
                        <tr>
                            <td style="width: 100px; text-align: right;" class="key">
                                <label for="product_width">
                                <?php echo JText::_( 'Width' ); ?>:
                                </label>
                            </td>
                            <td>
                                <input type="text" name="product_width" id="product_width" value="<?php echo @$row->product_width; ?>" size="48" maxlength="250" />
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 100px; text-align: right;" class="key">
                                <label for="product_height">
                                <?php echo JText::_( 'Height' ); ?>:
                                </label>
                            </td>
                            <td>
                                <input type="text" name="product_height" id="product_height" value="<?php echo @$row->product_height; ?>" size="48" maxlength="250" />
                            </td>
                        </tr>
                        </table>
                        </div>
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
                            [<?php echo TiendaUrl::popup( "index.php?option=com_tienda&controller=products&task=selectcategories&id=".$row->product_id."&tmpl=component", "Select Categories" ); ?>]
                            <?php $categories = TiendaHelperProduct::getCategories( $row->product_id ); ?>
                            <div id="current_categories">
                                <?php foreach (@$categories as $category) : ?>
                                    [<a href="<?php echo "index.php?option=com_tienda&controller=products&task=selected_disable&id=".$row->product_id."&cid[]=".$category."&return=".base64_encode("index.php?option=com_tienda&controller=products&task=edit&id=".$row->product_id); ?>">
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
                        <?php
                        jimport('joomla.filesystem.file');
                        if (!empty($row->product_full_image))
                        {
                            echo TiendaUrl::popup( TiendaHelperProduct::getImage($row->product_id, '', '', 'full', true), TiendaHelperProduct::getImage($row->product_id), array('update' => false, 'img' => true));
                        }
                        ?>
                        <br />
                        <input type="text" name="product_full_image" id="product_full_image" size="48" maxlength="250" value="<?php echo @$row->product_full_image; ?>" />
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
                        echo TiendaUrl::popup( "index.php?option=com_tienda&controller=products&task=viewGallery&id=".@$row->product_id."&tmpl=component", "View Gallery" ); 
                        ?>
                        ]
                        <br/>
                        <?php $images = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getGalleryImages( TiendaHelperProduct::getGalleryPath( @$row->product_id ) ); ?> 
                        <?php foreach (@$images as $image) : ?>
                            [<a href="<?php echo "index.php?option=com_tienda&controller=products&task=deleteImage&product_id=".@$row->product_id."&image=".$image."&return=".base64_encode("index.php?option=com_tienda&controller=products&task=edit&id=".@$row->product_id); ?>">
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
                            [<?php echo TiendaUrl::popup( "index.php?option=com_tienda&controller=products&task=setfiles&id=".$row->product_id."&tmpl=component", "Manage Files" ); ?>]
                            <?php $files = TiendaHelperProduct::getFiles( $row->product_id ); ?>
                            <div id="current_files">
                                <?php foreach (@$files as $file) : ?>
                                    [<a href="<?php echo "index.php?option=com_tienda&controller=productfiles&task=delete&cid[]=".$file->productfile_id."&return=".base64_encode("index.php?option=com_tienda&controller=products&task=edit&id=".$row->product_id); ?>">
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
            
            <fieldset>
            <legend><?php echo JText::_( "Publication Dates" ); ?></legend>
            <table class="admintable" style="width: 100%;">
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'Publish Up' ); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::calendar( @$row->publish_date, "publish_date", "publish_date", '%Y-%m-%d %H:%M:%S' ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'Publish Down' ); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::calendar( @$row->unpublish_date, "unpublish_date", "unpublish_date", '%Y-%m-%d %H:%M:%S' ); ?>
                    </td>
                </tr>
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
                <?php 
                if (empty($row->product_id)) 
                {
                    // doing a new product, so collect default info
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
                            Tienda::load( "TiendaHelperProduct", 'helpers.product' ); 
                            ?>
                            [<?php echo TiendaUrl::popup( "index.php?option=com_tienda&controller=products&task=setprices&id=".$row->product_id."&tmpl=component", "Set Prices" ); ?>]
                            <?php $prices = TiendaHelperProduct::getPrices( $row->product_id ); ?>
                            <div id="current_prices">
                                <?php foreach (@$prices as $price) : ?>
                                    [<a href="<?php echo $price->link_remove."&return=".base64_encode("index.php?option=com_tienda&controller=products&task=edit&id=".$row->product_id); ?>">
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
                if (empty($row->product_check_inventory))
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
                        // doing a new product, so display a notice
                        ?>
                        <tr>
                            <td width="100" align="right" class="key" style="vertical-align: top;">
                                <?php echo JText::_( 'Product Quantities' ); ?>:
                            </td>
                            <td>
                                <div class="note"><?php echo JText::_( "Click apply to be able to create product quantities" ); ?></div>
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
                                [<?php echo TiendaUrl::popup( "index.php?option=com_tienda&controller=products&task=setquantities&id=".$row->product_id."&tmpl=component", "Set Quantities", $options); ?>]
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </table>
            </fieldset>
        </div>
        
        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_( "Recurring Charges" ); ?></legend>
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
    echo $tabs->startPanel( JText::_( 'Display' ), "panel_display"); 
    ?>
        <div style="clear: both;"></div>
        
        <div style="float: left; width: 50%;">
            <fieldset>
            <legend><?php echo JText::_( "Display" ); ?></legend>
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
                </table>
            <?php else : ?>
                <div class="note">
                    <?php echo JText::_( "Billets Version Notice" ); ?>
                </div>
            <?php endif; ?>
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