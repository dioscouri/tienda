<?php
/**
 * @version 1.5
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaHelperBase', 'helpers._base' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class TiendaHelperProduct extends TiendaHelperBase
{
    /**
     * Gets the list of available product layout files
     * from the template's override folder
     * and the tienda products view folder
     * 
     * Returns array of filenames
     * Array
     * (
     *     [0] => view.php
     *     [1] => camera.php
     *     [2] => cameras.php
     *     [3] => computers.php
     *     [4] => laptop.php
     * )
     *  
     * @param array $options
     * @return array
     */
    function getLayouts( $options=array() )
    {
        $layouts = array();
        // set the default exclusions array
        $exclusions = array(
            'default.php',
            'product_buy.php',
            'product_children.php',
            'product_comments.php',
            'product_files.php',
            'product_relations.php',
            'product_requirements.php',
            'product_reviews.php',
            'quickadd.php',
            'search.php',
            'view.php',
        );
        // TODO merge $exclusions with $options['exclude']
        
        jimport('joomla.filesystem.file');
        $app = JFactory::getApplication();
        if ($app->isAdmin())
        {
            // TODO This doesn't account for when templates are assigned to menu items.  Make it do so
            $db = JFactory::getDBO();
            $db->setQuery( "SELECT `template` FROM #__templates_menu WHERE `menuid` = '0' AND `client_id` = '0';" );
            $template = $db->loadResult();
        }
            else
        {
            $template = $app->getTemplate();
        }
        $folder = JPATH_SITE.DS.'templates'.DS.$template.DS.'html'.DS.'com_tienda'.DS.'products';
        
        if (JFolder::exists( $folder ))
        {
            $extensions = array( 'php' );
            
            $files = JFolder::files( $folder );
            foreach ($files as $file)
            {
                $namebits = explode('.', $file);
                $extension = $namebits[count($namebits)-1];
                if (in_array($extension, $extensions) && (substr($file, 0, 8) != 'product_'))
                {
                    if (!in_array($file, $exclusions) && !in_array($file, $layouts))
                    {
                        $layouts[] = $file;
                    }
                }
            }
        }
        
        // now do the tienda folder
        $folder = JPATH_SITE.DS.'components'.DS.'com_tienda'.DS.'views'.DS.'products'.DS.'tmpl';
        
        if (JFolder::exists( $folder ))
        {
            $extensions = array( 'php' );
            
            $files = JFolder::files( $folder );
            foreach ($files as $file)
            {
                $namebits = explode('.', $file);
                $extension = $namebits[count($namebits)-1];
                if (in_array($extension, $extensions))
                {
                    if (!in_array($file, $exclusions) && !in_array($file, $layouts))
                    {
                        $layouts[] = $file;
                    }
                }
            }
        }
        
        return $layouts;    
    }
    
    /**
     * Determines a product's layout 
     * 
     * @param int $product_id
     * @param array options(
     *              'category_id' = if specified, will be used to determine layout if product doesn't have specific one
     *              )
     * @return unknown_type
     */
    function getLayout( $product_id, $options=array() )
    {
        $layout = 'view';
        
        jimport('joomla.filesystem.file');
        $app = JFactory::getApplication();
        if ($app->isAdmin())
        {
            // TODO This doesn't account for when templates are assigned to menu items.  Make it do so
            $db = JFactory::getDBO();
            $db->setQuery( "SELECT `template` FROM #__templates_menu WHERE `menuid` = '0' AND `client_id` = '0';" );
            $template = $db->loadResult();
        }
            else
        {
            $template = $app->getTemplate();
        }
        $templatePath = JPATH_SITE.DS.'templates'.DS.$template.DS.'html'.DS.'com_tienda'.DS.'products'.DS.'%s'.'.php';
        $extensionPath = JPATH_SITE.DS.'components'.DS.'com_tienda'.DS.'views'.DS.'products'.DS.'tmpl'.DS.'%s'.'.php';

        Tienda::load( 'TiendaTableProducts', 'tables.products' );
        $product = JTable::getInstance( 'Products', 'TiendaTable' );
        $product->load( $product_id );

        // if the product->product_layout file exists in the template, use it
        if (
            !empty($product->product_layout) && 
            (JFile::exists( sprintf($templatePath, $product->product_layout) ) || JFile::exists( sprintf($extensionPath, $product->product_layout) )) 
        )
        {
            return $product->product_layout;
        }
        
        if (!empty($options['category_id']))
        {
            // if the options[category_id] has a layout and it exists, use it
            Tienda::load( 'TiendaTableCategories', 'tables.categories' );
            $category = JTable::getInstance( 'Categories', 'TiendaTable' );
            $category->load( $options['category_id'] );
            if (
                !empty($category->categoryproducts_layout) && 
                (JFile::exists( sprintf($templatePath, $category->categoryproducts_layout) ) || JFile::exists( sprintf($extensionPath, $category->categoryproducts_layout) ))
            )
            {
                return $category->categoryproducts_layout;
            }
        }

        // if the product is in a category, try to use the layout from that one 
        $categories = TiendaHelperProduct::getCategories( $product->product_id );
        if (!empty($categories))
        {
            Tienda::load( 'TiendaTableCategories', 'tables.categories' );
            $category = JTable::getInstance( 'Categories', 'TiendaTable' );
            $category->load( $categories[0] ); // load the first category
            if (
                !empty($category->categoryproducts_layout) && 
                (JFile::exists( sprintf($templatePath, $category->categoryproducts_layout) ) || JFile::exists( sprintf($extensionPath, $category->categoryproducts_layout) ))
            )
            {
                return $category->categoryproducts_layout;
            }
        }
        
        // TODO if there are multiple categories, which one determines product layout?
        // if the product is in multiple categories, try to use the layout from the deepest category
            // and move upwards in tree after that
            
        // if all else fails, use the default!
        return $layout;
    }
    
    /**
     * Converts a path string to a URI string
     * 
     * @param $path
     * @return unknown_type
     */
    function getUriFromPath( $path )
    {
        $path = str_replace(JPATH_SITE.DS, JURI::root(), $path);        
        $path = str_replace(DS, '/', $path);
        return $path;
    }
    
    /**
     * Will consolidate a product's images into its currently set path.
     * If an image already exists in the current path with the same name, 
     * will either leave the iamge in the old path or delete it if delete_duplicates = true
     * 
     * @param $product_id
     * @param $delete_duplicates
     * @return unknown_type 
     */
    function consolidateGalleryImages( $product_id, $delete_duplicates=false )
    {
        $file_moved = null;
        
        // get the current path for the product
        $path = $this->getGalleryPath( $product_id );
        
        // get the current list of images in the current path
        $images = $this->getGalleryImages( $path );

        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $row = JTable::getInstance('Products', 'TiendaTable');
        $row->load( (int) $product_id );

        // if there are any images in the other possible paths for the product, move them to the current path
        $dir = Tienda::getPath( 'products_images' );
        
        // merge the SKU-based dir if it exists and isn't the current path 
        if (!empty($row->product_sku) && $this->checkDirectory($dir.DS.$row->product_sku, false) && ($dir.DS.$row->product_sku.DS != $path))
        {
            $old_dir = $dir.DS.$row->product_sku.DS;
            
            $files = JFolder::files( $old_dir );
            foreach ($files as $file)
            {
                if (!in_array($file, $images))
                {
                    if (JFile::move( $old_dir.$file, $path.$file ))
                    {
                        // create new thumb too
                        Tienda::load( 'TiendaImage', 'library.image' );
                        $img = new TiendaImage($path.$file);
                        $img->setDirectory( $path );
                        Tienda::load( 'TiendaHelperImage', 'helpers.image' );
                        $imgHelper = TiendaHelperBase::getInstance('Image', 'TiendaHelper');
                        $imgHelper->resizeImage( $img );
                        
                        // delete old thumb
                        JFile::delete( $old_dir.'thumbs'.DS.$file );
                        
                        $file_moved = true;
                    }
                }
                    else
                {
                    // delete the old one?
                    if ($delete_duplicates)
                    {
                        JFile::delete( $old_dir.$file );
                    }
                }
            }
        }

        // merge the ID-based dir if it exists and isn't the current path
        if ($this->checkDirectory($dir.DS.$row->product_id, false) && ($dir.DS.$row->product_id.DS != $path))
        {
            $old_dir = $dir.DS.$row->product_id.DS;
            
            $files = JFolder::files( $old_dir );
            foreach ($files as $file)
            {
                if (!in_array($file, $images))
                {
                    if (JFile::move( $old_dir.$file, $path.$file ))
                    {
                        // create new thumb too
                        Tienda::load( 'TiendaImage', 'library.image' );
                        $img = new TiendaImage($path.$file);
                        $img->setDirectory( $path );
                        Tienda::load( 'TiendaHelperImage', 'helpers.image' );
                        $imgHelper = TiendaHelperBase::getInstance('Image', 'TiendaHelper');
                        $imgHelper->resizeImage( $img );
                        // delete old thumb
                        JFile::delete( $old_dir.'thumbs'.DS.$file );
                        
                        $file_moved = true;
                    }
                }
                    else
                {
                    // delete the old one?
                    if ($delete_duplicates)
                    {
                        JFile::delete( $old_dir.$file );   
                    }
                }
            }
        }
        
        return $file_moved;
    }
    
    /**
     * Returns array of filenames
     * Array
     * (
     *     [0] => airmac.png
     *     [1] => airportdisk.png
     *     [2] => applepowerbook.png
     *     [3] => cdr.png
     *     [4] => cdrw.png
     *     [5] => cinemadisplay.png
     *     [6] => floppy.png
     *     [7] => macmini.png
     *     [8] => shirt1.jpg
     * )
     * @param $folder
     * @return array
     */
    function getGalleryImages( $folder=null, $options=array() )
    {
        $images = array();
        
        if (empty($folder))
        {
            return $images;
        }
        
        if (empty( $options['exclude'] ))
        {
            $options['exclude'] = array();
        }
            elseif (!is_array($options['exclude']))
        {
            $options['exclude'] = array($options['exclude']);
        }
        
        if (JFolder::exists( $folder ))
        {
            $extensions = array( 'png', 'gif', 'jpg', 'jpeg' );
            
            $files = JFolder::files( $folder );
            foreach ($files as $file)
            {
                $namebits = explode('.', $file);
                $extension = strtolower( $namebits[count($namebits)-1] );
                if (in_array($extension, $extensions))
                {
                    if (!in_array($file, $options['exclude']))
                    {
                        $images[] = $file;
                    }
                }
            }
        }
        
        return $images;
    }
    
    /**
     * Returns the full path to the product's image gallery files
     * 
     * @param int $id
     * @return string
     */
    function getGalleryPath( $id )
    {
        static $paths;
        
        $id = (int) $id;
        
        if (!is_array($paths)) { $paths = array(); }
        
        if (empty($paths[$id]))
        {
            $paths[$id] = '';
            
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $row = JTable::getInstance('Products', 'TiendaTable');
            $row->load( (int) $id );
            if (empty($row->product_id))
            {
                // TODO figure out what to do if the id is invalid 
                return null;
            }

            $paths[$id] = $row->getImagePath(true);
        }
        
        return $paths[$id];
    }
    
    /**
     * Returns the full path to the product's image gallery files
     * 
     * @param int $id
     * @return string
     */
    function getGalleryUrl( $id )
    {
        static $urls;
        
        $id = (int) $id;
        
        if (!is_array($urls)) { $urls = array(); }
        
        if (empty($urls[$id]))
        {
            $urls[$id] = '';
            
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $row = JTable::getInstance('Products', 'TiendaTable');
            $row->load( (int) $id );
            if (empty($row->product_id))
            {
                // TODO figure out what to do if the id is invalid 
                return null;
            }

            $urls[$id] = $row->getImageUrl();
        }
        
        return $urls[$id];
    }
    
    /**
     * Returns the full path to the product's files
     * 
     * @param int $id
     * @return string
     */
    function getFilePath( $id )
    {
        static $paths;
        
        $id = (int) $id;
        
        if (!is_array($paths)) { $paths = array(); }
        
        if (empty($paths[$id]))
        {
            $paths[$id] = '';
            
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $row = JTable::getInstance('Products', 'TiendaTable');
            $row->load( (int) $id );
            if (empty($row->product_id))
            {
                // TODO figure out what to do if the id is invalid 
                return null;
            }
            
            // if product_images_path is valid and not empty, use it
            if (!empty($row->product_files_path))
            {
                $folder = $row->product_files_path;
                if (JFolder::exists( $folder )) 
                {
                    $files = JFolder::files( $folder );
                    if (!empty($files))
                    {
                        $paths[$id] = $folder;
                    }
                }
            }
            
            // if no override, use path based on sku if it is valid and not empty
            // TODO clean SKU so valid characters used for folder name?
            if (empty($paths[$id]) && !empty($row->product_sku))
            {
                $folder = Tienda::getPath( 'products_files' ).DS.'sku'.DS.$row->product_sku;
                if (JFolder::exists( $folder )) 
                {
                    $files = JFolder::files( $folder );
                    if (!empty($files))
                    {
                        $paths[$id] = $folder;
                    }
                }
            }
            
            // if still unset, use path based on id number
            if (empty($paths[$id]))
            {
                $folder = Tienda::getPath( 'products_files' ).DS.'id'.DS.$row->product_id;
                if (!JFolder::exists( $folder )) 
                {
                    JFolder::create( $folder );
                }
                $paths[$id] = $folder;
            }
        }
        
        // TODO Make sure the files folder has htaccess file
        return $paths[$id];
    }   
    
    /**
     * 
     * @param $id
     * @param $by
     * @param $alt
     * @param $type
     * @param $url
     * @return unknown_type
     */
    function getImage( $id, $by='id', $alt='', $type='thumb', $url=false, $resize=false, $options=array() )
    {
        $style = "";
        $height_style = "";
        $width_style = "";
        
        $dimensions = "";
		if($options['width'])
		{
			$dimensions .= "width=\"".$options['width']."\" ";
		}
        if($options['height'])
		{
			$dimensions .= "height=\"".$options['height']."\" ";
		}
        
        if (!empty($options['height'])) { $height_style = "max-height: ".$options['height']."px;"; }
        if (!empty($options['width'])) { $width_style = "max-width: ".$options['width']."px;"; }
        if (!empty($height_style) || !empty($width_style))
        {
            $style = "style='$height_style $width_style'";
        }
        
        switch($type)
        {
            case "full":
                $path = 'products_images';
              break;
            case "thumb":
            default:
                $path = 'products_thumbs';
              break;
        }
        
        $tmpl = "";
        if (strpos($id, '.'))
        {
            // then this is a filename, return the full img tag if file exists, otherwise use a default image
            $src = (JFile::exists( Tienda::getPath( $path ).DS.$id))
                ? Tienda::getUrl( $path ).$id : JURI::root(true).'/media/com_tienda/images/noimage.png';
            
            // if url is true, just return the url of the file and not the whole img tag
            $tmpl = ($url)
                ? $src : "<img ".$dimensions." src='".$src."' alt='".JText::_( $alt )."' title='".JText::_( $alt )."' align='middle' border='0' ".$style." />";

        }
            else
        {
            if (!empty($id))
            {
                // load the item, get the filename, create tmpl
                JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
                $row = JTable::getInstance('Products', 'TiendaTable');
                $row->load( (int) $id );
                
                $urli = $row->getImageUrl();
                $dir = $row->getImagePath();
                
                if($path == 'products_thumbs'){
                    $dir .= 'thumbs';
                    $urli .= 'thumbs/';
                }
                
                $file = $dir.DS.$row->product_full_image;
                
                $id = $urli.$row->product_full_image;
                
                // Gotta do some resizing first?
                if ($resize)
                {
                    // Add a suffix to the thumb to avoid conflicts with user settings
                    $suffix = '';
                
                    if (isset($options['width']) && isset($options['height'])) 
                    {
                        $suffix = '_'.$options['width'].'x'.$options['height'];
                    }
                    elseif (isset($options['width']))
                    {
                        $suffix = '_w'.$options['width'];
                    }
                    elseif (isset($options['height']))
                    {
                        $suffix = '_h'.$options['height'];
                    }
                    
                    // Add suffix to file path
                    $dot = strrpos($file, '.');
                    $resize = substr($file, 0, $dot).$suffix.substr($file, $dot);
                    
                    if (!JFile::exists($resize))
                    {
                        
                        Tienda::load('TiendaImage', 'library.image');
                        $image = new TiendaImage($file);
                        $image->load();
                        // If both width and height set, gotta figure hwo to resize
                        if (isset($options['width']) && isset($options['height'])) 
                        {
                            // If width is larger, proportionally
                            if (($options['width'] / $image->getWidth()) < ($options['height'] / $image->getHeight()))
                            {
                                $image->resizeToWidth($options['width']);
                                $image->save($resize);
                            }
                            // If height is larger, proportionally
                            else
                            {
                                $image->resizeToHeight($options['height']);
                                $image->save($resize);
                            }
                        }
                        // If only width is set
                        elseif (isset($options['width']))
                        {
                            $image->resizeToWidth($options['width']);
                            $image->save($resize);
                        }
                        // If only height is set
                        elseif (isset($options['height']))
                        {
                            $image->resizeToHeight($options['height']);
                            $image->save($resize);
                        }
                        
                    }
                    
                    // Add suffix to url path
                    $dot = strrpos($id, '.');
                    $id = substr($id, 0, $dot).$suffix.substr($id, $dot);
                }

                $src = (JFile::exists( $file ))
                    ? $id : JURI::root(true).'/media/com_tienda/images/noimage.png';

                $tmpl = ($url)
                    ? $src : "<img ".$dimensions." src='".$src."' alt='".JText::_( $alt )."' title='".JText::_( $alt )."' align='middle' border='0' ".$style." />";
            }           
        }
        return $tmpl;
    }
    
    /**
     * Gets a product's list of prices
     * 
     * @param $id
     * @return array
     */
    function getPrices( $id )
    {
        if (empty($id))
        {
            return array();
        }
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'ProductPrices', 'TiendaModel' );
        $model->setState( 'filter_id', $id );
        $items = $model->getList();
        return $items;
    }
    
    /**
     * Returns a product's price based on the quantity purchased, user's group, and date
     * 
     * @param int $id
     * @param unknown_type $quantity
     * @param unknown_type $group_id
     * @param unknown_type $date
     * @return unknown_type
     */
    function getPrice( $id, $quantity='1', $group_id='', $date='' )
    {
        $price = null;
        if (empty($id))
        {
            return $price;
        }
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'ProductPrices', 'TiendaModel' );
        $model->setState( 'filter_id', $id );
        $prices = TiendaHelperProduct::getPrices($id);
        
        (int) $quantity;
        if ($quantity <= '0') { $quantity = '1'; }
            //where price_quantity_start < $quantity
            $model->setState( 'filter_quantity', $quantity );
            
        // does date even matter?
        $nullDate = JFactory::getDBO()->getNullDate();
        if (empty($date) || $date == $nullDate) { $date = JFactory::getDate()->toMysql(); }
            $model->setState( 'filter_date', $date );
            //where product_price_startdate <= $date
            //where product_price_enddate >= $date OR product_price_enddate == nullDate 
            
        // does group_id?
        (int) $group_id;
        $default_user_group = '1'; /* TODO Use a default $group_id */
        if ($group_id <= '0') { $group_id = $default_user_group; }
            // using ->getPrices(), do a getColumn() on the array for the group_id column
            $group_ids = TiendaHelperBase::getColumn($prices, 'group_id');
            if (in_array($group_id, $group_ids))
            {
                // if $group_id is in the column, then set the query to pull an exact match on it,
                $model->setState( 'filter_user_group', $group_id ); 
            } 
                else
            {
                // otherwise, $group_id_determined = the default $group_id
                $model->setState( 'filter_user_group', $default_user_group );               
            }
        
        // set the ordering so the most discounted item is at the top of the list
        $model->setState( 'order', 'price_quantity_start' );
        $model->setState( 'direction', 'DESC' );

        // TiendaModelProductPrices is a special model that overrides getItem
        $price = $model->getItem();
        return $price;  
    }
    
    /**
     * 
     * Gets the tax total for a product based on an array of geozones
     * @param $product_id
     * @param $geozones
     * @return object
     */
    public function getTaxTotal( $product_id, $geozones )
    {
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model  = JModel::getInstance( 'Products', 'TiendaModel' );
        $model->setId( $product_id );
        $row = $model->getItem();
        
        $orderitem_tax = 0;
        $tax_rates = array();
        $tax_amounts = array();
        
        // For each item in $this->getBillingGeoZone, calculate the tax total
        // and update the item's tax value
        foreach ($geozones as $geozone)
        {
            $geozone_id = $geozone->geozone_id;
            $taxrate = TiendaHelperProduct::getTaxRate($product_id, $geozone_id, true );
            $product_tax_rate = $taxrate->tax_rate;
            
            // track the total tax for this item
            $orderitem_tax += ($product_tax_rate/100) * $row->price;
            
            $tax_rates[$taxrate->tax_rate_id] = $taxrate; 
            $tax_amounts[$taxrate->tax_rate_id] = ($product_tax_rate/100) * $row->price;; 
        }
        
        $return = new JObject();
        $return->tax_rates = $tax_rates;
        $return->tax_amounts = $tax_amounts;
        $return->tax_total = $orderitem_tax;
         
        return $return;
    }
    
    /**
     * Returns the tax rate for an item
     *  
     * @param int $product_id
     * @param int $geozone_id
     * @param boolean $return_object
     * @return float | object if $return_object=true
     */
    public function getTaxRate( $product_id, $geozone_id, $return_object=false )
    {
        Tienda::load( 'TiendaQuery', 'library.query' );
            
        $taxrate = "0.00000";
        $db = JFactory::getDBO();
        
        $query = new TiendaQuery();
        $query->select( 'tbl.*' );
        $query->from('#__tienda_taxrates AS tbl');
        $query->join('LEFT', '#__tienda_products AS product ON product.tax_class_id = tbl.tax_class_id');
        $query->where("product.product_id = '".$product_id."'");
        $query->where("tbl.geozone_id = '".$geozone_id."'");
        
        $db->setQuery( (string) $query );
        if ($data = $db->loadObject())
        {
            $taxrate = $data->tax_rate;
            if ($return_object)
            {
                JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
                $taxrate = JTable::getInstance( 'TaxRates', 'TiendaTable' );
                $taxrate->load( array( 'tax_rate_id'=>$data->tax_rate_id ) );
            }
        }
            elseif ($return_object)
        {
            // if there is no defined tax rate, but an object is expected to be returned
            //  return an object
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $taxrate = JTable::getInstance( 'TaxRates', 'TiendaTable' );
        }
        
        return $taxrate;
    }
    
    /**
     * Gets a product's list of categories
     * 
     * @param $id
     * @return array
     */
    function getCategories( $id )
    {
        if (empty($id))
        {
            return array();
        }
        Tienda::load( 'TiendaQuery', 'library.query' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $table = JTable::getInstance( 'ProductCategories', 'TiendaTable' );
        
        $query = new TiendaQuery();
        $query->select( "tbl.category_id" );
        $query->from( $table->getTableName()." AS tbl" );
        $query->where( "tbl.product_id = ".(int) $id );
        $db = JFactory::getDBO();
        $db->setQuery( (string) $query );
        $items = $db->loadResultArray();
        return $items;
    }
    
    /**
     * Returns a list of a product's attributes
     * 
     * @param int $id
     * @return unknown_type
     */
    function getAttributes( $id )
    {
        if (empty($id))
        {
            return array();
        }
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'ProductAttributes', 'TiendaModel' );
        $model->setState( 'filter_product', $id );
        $model->setState( 'order', 'tbl.ordering' );
        $model->setState( 'direction', 'ASC' );
        $items = $model->getList();
        return $items;
    }
    
    /**
     * Returns a default list of a product's attributes
     * 
     * @param int $id
     * @return array
     */
    function getDefaultAttributes( $id )
    {
        if (empty($id))
        {
            return array();
        }
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'ProductAttributes', 'TiendaModel' );
        $model->setState( 'filter_product', $id );
        $items = $model->getList();
        if (empty($items))
        {
            return array();
        }
        
        $list = array();
        foreach ($items as $item)
        {
            $key = 'attribute_'.$item->productattribute_id;
            $model = JModel::getInstance( 'ProductAttributeOptions', 'TiendaModel' );
            $model->setState( 'filter_attribute', $item->productattribute_id );
            $model->setState('order', 'tbl.ordering');
            $model->setState('direction', 'ASC');
            $options = $model->getList();
            if (!empty($options))
            {
                $option = $options[0];
                $list[$key] = $option->productattributeoption_id;
            }
        }
        return $list;
    }
    
    
    /**
     * Returns a list of a product's files
     * 
     * @param unknown_type $id
     * @return unknown_type
     */
    function getFiles( $id )
    {
        if (empty($id))
        {
            return array();
        }
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'ProductFiles', 'TiendaModel' );
        $model->setState( 'filter_product', $id );
        $items = $model->getList();
        return $items;
    }
    
    
    /**
     * Returns array of filenames
     * Array
     * (
     *     [0] => airmac.png
     *     [1] => airportdisk.png
     *     [2] => applepowerbook.png
     *     [3] => cdr.png
     *     [4] => cdrw.png
     *     [5] => cinemadisplay.png
     *     [6] => floppy.png
     *     [7] => macmini.png
     *     [8] => shirt1.jpg
     * )
     * @param $folder
     * @return array
     */
    function getServerFiles( $folder=null, $options=array() )
    {
        $files = array();
        
        if (empty($folder))
        {
            return $files;
        }
        
        if (empty( $options['exclude'] ))
        {
            $options['exclude'] = array();
        }
            elseif (!is_array($options['exclude']))
        {
            $options['exclude'] = array($options['exclude']);
        }
        
        // Add .htaccess exclusion
        if(!in_array('.htaccess', $options['exclude']))
            $options['exclude'][] = '.htaccess';
        
        if (JFolder::exists( $folder ))
        {           
            $serverfiles = JFolder::files( $folder );
            foreach ($serverfiles as $file)
            {
                if (!in_array($file, $options['exclude']))
                {
                        $files[] = $file;
                }
            }
        }
        
        return $files;
    }
    
    /**
     * Finds the prev & next items in the list 
     *  
     * @param $id   product id
     * @return array( 'prev', 'next' )
     */
    function getSurrounding( $id )
    {
        $return = array();
        
        $prev = intval( JRequest::getVar( "prev" ) );
        $next = intval( JRequest::getVar( "next" ) );
        if ($prev || $next) 
        {
            $return["prev"] = $prev;
            $return["next"] = $next;
            return $return;
        }
        
        $app = JFactory::getApplication();
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'Products', 'TiendaModel' );
        $ns = $app->getName().'::'.'com.tienda.model.'.$model->getTable()->get('_suffix');
        $state = array();
        
        $config = TiendaConfig::getInstance();
        
        $state['limit']     = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $state['limitstart'] = $app->getUserStateFromRequest($ns.'limitstart', 'limitstart', 0, 'int');
        $state['filter']    = $app->getUserStateFromRequest($ns.'.filter', 'filter', '', 'string');
        $state['order']     = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.'.$model->getTable()->getKeyName(), 'cmd');
        $state['direction'] = $app->getUserStateFromRequest($ns.'.filter_direction', 'filter_direction', 'ASC', 'word');
                
        $state['filter_id_from']    = $app->getUserStateFromRequest($ns.'id_from', 'filter_id_from', '', '');
        $state['filter_id_to']      = $app->getUserStateFromRequest($ns.'id_to', 'filter_id_to', '', '');
        $state['filter_name']       = $app->getUserStateFromRequest($ns.'name', 'filter_name', '', '');
        $state['filter_enabled']    = $app->getUserStateFromRequest($ns.'enabled', 'filter_enabled', '', '');
        $state['filter_quantity_from']  = $app->getUserStateFromRequest($ns.'quantity_from', 'filter_quantity_from', '', '');
        $state['filter_quantity_to']        = $app->getUserStateFromRequest($ns.'quantity_to', 'filter_quantity_to', '', '');
        $state['filter_category']       = $app->getUserStateFromRequest($ns.'category', 'filter_category', '', '');
        $state['filter_sku']        = $app->getUserStateFromRequest($ns.'sku', 'filter_sku', '', '');
        $state['filter_price_from']     = $app->getUserStateFromRequest($ns.'price_from', 'filter_price_from', '', '');
        $state['filter_price_to']       = $app->getUserStateFromRequest($ns.'price_to', 'filter_price_to', '', '');
        $state['filter_taxclass']   = $app->getUserStateFromRequest($ns.'taxclass', 'filter_taxclass', '', '');
        $state['filter_ships']   = $app->getUserStateFromRequest($ns.'ships', 'filter_ships', '', '');
        
        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );   
        }
        $rowset = $model->getList();
            
        $found = false;
        $prev_id = '';
        $next_id = '';

        for ($i=0; $i < count($rowset) && empty($found); $i++) 
        {
            $row = $rowset[$i];     
            if ($row->product_id == $id) 
            { 
                $found = true; 
                $prev_num = $i - 1;
                $next_num = $i + 1;
                if (isset($rowset[$prev_num]->product_id)) { $prev_id = $rowset[$prev_num]->product_id; }
                if (isset($rowset[$next_num]->product_id)) { $next_id = $rowset[$next_num]->product_id; }
    
            }
        }
        
        $return["prev"] = $prev_id;
        $return["next"] = $next_id; 
        return $return;
    }
    
    /**
     * Given a multi-dimensional array, 
     * this will find all possible combinations of the array's elements
     *
     * Given:
     * 
     * $traits = array
     * (
     *   array('Happy', 'Sad', 'Angry', 'Hopeful'),
     *   array('Outgoing', 'Introverted'),
     *   array('Tall', 'Short', 'Medium'),
     *   array('Handsome', 'Plain', 'Ugly')
     * );
     * 
     * Returns:
     * 
     * Array
     * (
     *      [0] => Happy,Outgoing,Tall,Handsome
     *      [1] => Happy,Outgoing,Tall,Plain
     *      [2] => Happy,Outgoing,Tall,Ugly
     *      [3] => Happy,Outgoing,Short,Handsome
     *      [4] => Happy,Outgoing,Short,Plain
     *      [5] => Happy,Outgoing,Short,Ugly
     *      etc
     * )
     * 
     * @param string $string   The result string
     * @param array $traits    The multi-dimensional array of values
     * @param int $i           The current level
     * @param array $return    The final results stored here
     * @return array           An Array of CSVs
     */
    function getCombinations($string, $traits, $i, &$return)
    {
        if ($i >= count($traits))
        {
            $return[] = str_replace(' ', ',', trim($string)); 
        }
            else
        {
            foreach ($traits[$i] as $trait)
            {
                TiendaHelperProduct::getCombinations("$string $trait", $traits, $i + 1, $return);
            }
        }
    }
    
    /**
     * Will return all the CSV combinations possible from a product's attribute options
     * 
     * @param unknown_type $product_id 
     * @param $attributeOptionId
     * @return unknown_type
     */
    function getProductAttributeCSVs( $product_id, $attributeOptionId='0' )
    {
        $return = array();
        $traits = array();
        
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        
        // get all productattributes
        $model = JModel::getInstance('ProductAttributes', 'TiendaModel');
        $model->setState('filter_product', $product_id);
        if ($attributes = $model->getList())
        {
            foreach ($attributes as $attribute)
            {
                $paoModel = JModel::getInstance('ProductAttributeOptions', 'TiendaModel');
                $paoModel->setState('filter_attribute', $attribute->productattribute_id);
                if ($paos = $paoModel->getList())
                {
                    $options = array();
                    foreach ($paos as $pao)
                    {   
                        // Genrate the arrray of single value with the id of newly created attribute option    
                    	if ($attributeOptionId == $pao->productattributeoption_id)
                    	{
                        	$newOption = array();
                        	$newOption[] = (string) $attributeOptionId;
                        	$options = $newOption;
                        	break;
                        }
                    	
                    	$options[] = $pao->productattributeoption_id;
                                       
                    }
                    $traits[] = $options;
                }
            }
        }
        // run recursive function on the data
        TiendaHelperProduct::getCombinations( "", $traits, 0, $return );
        
        // before returning them, loop through each record and sort them
        $result = array();
        foreach ($return as $csv)
        {
            $values = explode( ',', $csv );
            sort($values);
            $result[] = implode(',', $values);
        }
        
       return $result;
    }

    /**
     * Given a product_id and vendor_id
     * will perform a full CSV reconciliation of the _productquantities table
     * 
     * @param $product_id
     * @param $vendor_id
     * @param $attributeOptionId
     * @return unknown_type
     */
    function doProductQuantitiesReconciliation( $product_id, $vendor_id='0', $attributeOptionId='0' )
    {
        if (empty($product_id))
        {
            return false;
        }
        
    	$csvs = TiendaHelperProduct::getProductAttributeCSVs( $product_id, $attributeOptionId );
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance('ProductQuantities', 'TiendaModel');
        $model->setState('filter_productid', $product_id );
        $model->setState('filter_vendorid', $vendor_id );
        $items = $model->getList();
        
        $results = TiendaHelperProduct::reconcileProductAttributeCSVs( $product_id, $vendor_id, $items, $csvs );
    }
    
    /**
     * Adds any necessary _productsquantities records 
     * 
     * @param unknown_type $product_id     Product ID
     * @param unknown_type $vendor_id      Vendor ID
     * @param array $items                 Array of productQuantities objects
     * @param unknown_type $csvs           CSV output from getProductAttributeCSVs
     * @return array $items                Array of objects
     */
    function reconcileProductAttributeCSVs( $product_id, $vendor_id, $items, $csvs )
    {
        // remove extras
        $done = array();
        foreach ($items as $key=>$item)
        {
            if (!in_array($item->product_attributes, $csvs) || in_array($item->product_attributes, $done) )
            {
                $row = JTable::getInstance('ProductQuantities', 'TiendaTable');
                if (!$row->delete($item->productquantity_id))
                {
                    JError::raiseNotice('1', $row->getError());
                }
                unset($items[$key]);
            }
            $done[] = $item->product_attributes;
        }
        
        // add new ones
        $existingEntries = TiendaHelperBase::getColumn( $items, 'product_attributes' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        foreach ($csvs as $csv)
        {
            if (!in_array($csv, $existingEntries))
            {
                $row = JTable::getInstance('ProductQuantities', 'TiendaTable');
                $row->product_id = $product_id;
                $row->vendor_id = $vendor_id;
                $row->product_attributes = $csv;
                if (!$row->save())
                {
                    JError::raiseNotice('1', $row->getError());
                }
                $items[] = $row; 
            }
        }      
        
        return $items;
    }
    
    
    
    /**
     * Gets whether a product requires shipping or not
     * 
     * @param $id
     * @return boolean
     */
    function isShippingEnabled( $id )
    {
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $table = JTable::getInstance( 'Products', 'TiendaTable' );
        $table->load( (int) $id);
        if ($table->product_ships)
        {
            return true;
        }
        return false;
    }

    /**
     * Checks if the specified relationship exists
     * TODO Make this support $product_to='any'
     * TODO Make this support $relation_type='any'
     * 
     * @param $product_from
     * @param $product_to
     * @param $relation_type
     * @return unknown_type
     */
    function relationshipExists( $product_from, $product_to, $relation_type='relates' )
    {
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $table = JTable::getInstance('ProductRelations', 'TiendaTable');
        $keys = array(
            'product_id_from'=>$product_from,
            'product_id_to'=>$product_to,
            'relation_type'=>$relation_type,
        );
        $table->load( $keys );
        if (!empty($table->product_id_from))
        {
            return true;
        }
        
        // relates can be inverted
        if ($relation_type == 'relates')
        {
            // so try the inverse
            $table = JTable::getInstance('ProductRelations', 'TiendaTable');
            $keys = array(
                'product_id_from'=>$product_to,
                'product_id_to'=>$product_from,
                'relation_type'=>$relation_type,
            );
            $table->load( $keys );
            if (!empty($table->product_id_from))
            {
                return true;
            }
        }
        
        return false;

    }

    /**
     * returns a product's quantity list for all combination
     * @return array with CSV and quantity; 
     */
	public function getProductQuantities( $id )
	{
        Tienda::load( 'TiendaQuery', 'library.query' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        
        $tableQuantity = JTable::getInstance( 'ProductQuantities', 'TiendaTable' );
        $query = new TiendaQuery();
       
        $select[]="quantities.product_attributes";
        $select[]="quantities.quantity";		
		
		$query->select( $select );
        $query->from($tableQuantity->getTableName()." AS quantities");
		$query->where("quantities.product_id = ".$id );
	  	
		$db = JFactory::getDBO();
        $db->setQuery( (string) $query );
	    
	    $results = $db->loadObjectList();
	    $inventoryList = array();
	    
	    foreach ($results as $result) 
	    {
	    	$inventoryList[$result->product_attributes] = $result->quantity;
	    }
	    
	    return $inventoryList;
	}  

    /**
     * return the total quantity and Product name of product on the basis of the attribute 
     * 
     * @param $id
     * @param $attribute  CSV of attribute properties, in numeric order
     * @return an array with the name and the quantity of Product;
     */
    function getAvailableQuantity( $id, $attribute )
    {
        Tienda::load( 'TiendaQuery', 'library.query' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        
        $tableQuantity = JTable::getInstance( 'ProductQuantities', 'TiendaTable' );
        $tableProduct = JTable::getInstance( 'Products', 'TiendaTable' );
        
        $tableProduct->load( $id );
        if (empty($tableProduct->product_check_inventory))
        {
            $tableProduct->quantity = '9999';
            return $tableProduct;
        }
        
        $query = new TiendaQuery();
        
        $select[]="product.product_name";
        $select[]="quantities.quantity";
        $select[]="product.product_check_inventory";
        
        $query->select( $select );
        $query->from($tableProduct->getTableName()." AS product");
       
        $leftJoinCondition=$tableQuantity->getTableName()." as quantities ON product.product_id = quantities.product_id ";
        $query->leftJoin ($leftJoinCondition);
		
        $whereClause[]="quantities.product_id = ".(int) $id;
        $whereClause[]="quantities.product_attributes='".$attribute."'";
        $whereClause[]="product.product_check_inventory =1 ";
		$query->where($whereClause,"AND" );
        
        $db = JFactory::getDBO();
        $db->setQuery( (string) $query );
        $item = $db->loadObject();

        if (empty($item))
        {
            $return = new JObject();
            $return->product_id = $id;
            $return->product_name = $tableProduct->product_name;
            $return->quantity = 0;
            $return->product_check_inventory = $tableProduct->product_check_inventory;
            return $return;
        }
        
        return $item;
        
        
    }
    /**
     * Checks orderitem table for provided product id 
     * @param $product_id
     * 
     */

	function getOrders($product_id)
	{
		 //Check the registry to see if our Tienda class has been overridden
        if ( !class_exists('Tienda') ) 
             JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );
                // load the config class
         Tienda::load( 'TiendaConfig', 'defines' );
         JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
    	JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
         // get the model
     	$model = JModel::getInstance( 'OrderItems', 'TiendaModel' );
     	$user = JFactory::getUser();
     	$model->setState( 'filter_userid', $user->id); 
     	$model->setState( 'filter_productid', $product_id);
     	$orders = $model->getList();
    	return $orders;
		
		
	}
    
	/**
     * Gets a product's id and User id from Product comment table
     * 
     * @param $id
     * @return array
     */
    function getUserAndProductIdForReview( $product_id ,$user_id)
    {
        if (empty($product_id) && empty($user_id))
        {
            return array();
        }
        Tienda::load( 'TiendaQuery', 'library.query' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $table = JTable::getInstance( 'productcomments', 'TiendaTable' );
        
        $query = new TiendaQuery();
        $query->select( "tbl.productcomment_id" );
        $query->from( $table->getTableName()." AS tbl" );
        //$query->where( "tbl.product_id = ".(int) $id ." AND tbl.user_id= ".(int)$uid);
        $query->where("tbl.product_id = ".(int)$product_id);
        $query->where("tbl.user_id = ".(int)$user_id);
    
        $db = JFactory::getDBO();
        $db->setQuery( (string) $query );
        $items = $db->loadResultArray();
        return $items;
    }
	/**
     * Gets user's emails from Product comment table
     * 
     * @param int $product_id    
     * @return array
     */
    function getUserEmailForReview( $product_id )
    {
        if (empty($product_id))
        {
            return array();
        }
        Tienda::load( 'TiendaQuery', 'library.query' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $table = JTable::getInstance( 'productcomments', 'TiendaTable' );
        
        $query = new TiendaQuery();
        $query->select( "tbl.user_email" ) ;
        $query->from( $table->getTableName()." AS tbl" );      
        $query->where("tbl.product_id = ".(int)$product_id);
          
        $db = JFactory::getDBO();
        $db->setQuery( (string) $query );
        $items = $db->loadResultArray();

        return $items;
    }
    
    /**
     * 
     * Check if the current user already given a feedback to a review
     * @param $uid - the id of the user
     * @param $cid - comment id
     * @return boolean
     */
    function isFeedbackAlready($uid, $cid)
    {
		
        Tienda::load( 'TiendaQuery', 'library.query' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $table = JTable::getInstance( 'productcommentshelpfulness', 'TiendaTable' );
        
        $query = new TiendaQuery();
        $query->select( "tbl.productcommentshelpfulness_id" ) ;
        $query->from( $table->getTableName()." AS tbl" );      
        $query->where("tbl.user_id = ".(int)$uid);
        $query->where("tbl.productcomment_id = ".(int)$cid);
          
        $db = JFactory::getDBO();
        $db->setQuery( (string) $query );
        $items = $db->loadResultArray();
    	
		return !empty($items) ? true : false;
    }

     /**
     * returns a product's quantity list for all combination
     * @return array of the entire Objects; 
     */
	public function getProductQuantitiesObjects( $id )
	{
      Tienda::load( 'TiendaQuery', 'library.query' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        
        $tableQuantity = JTable::getInstance( 'ProductQuantities', 'TiendaTable' );
        $query = new TiendaQuery();
        $select[]="quantities.*";		
		
		$query->select( $select );
        $query->from($tableQuantity->getTableName()." AS quantities");
		$query->where("quantities.product_id = ".$id );
	  	
		$db = JFactory::getDBO();
		$db->setQuery( (string) $query );
	    
	    $results = $db->loadObjectList();
	    return $results;
	}  
    
	 /**
     * returns a attributes's options list 
     * @return array of the entire Objects; 
     */
	public function getAttributeOptionsObjects( $id )
	{
        if (empty($id))
        {
            return array();
        }
        
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'ProductAttributeOptions', 'TiendaModel' );
        $model->setState( 'filter_attribute', $id );
        $model->setState( 'order', 'tbl.ordering' );
        $model->setState( 'direction', 'ASC' );
        $items = $model->getList();
        return $items;
	}
	
    /**
     * 
     * Enter description here ...
     * @param $product_id
     * @return unknown_type
     */
	public function updateOverallRatings()
	{
	    $success = true;
	    
	    JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'ProductComments', 'TiendaModel' );
        $model->setState( 'filter_enabled', '1' );
        $model->setState( 'select', 'tbl.product_id' );
        $query = $model->getQuery();
        $query->select( 'tbl.productcomment_id');
        $query->group( 'tbl.product_id');
        $model->setQuery( $query );
        if ($items = $model->getList())
        {
            foreach ($items as $item)
            {
                // get the product row
                $product = JTable::getInstance('Products', 'TiendaTable');
                $product->load( $item->product_id );
                $product->updateOverallRating( true );
            }
        }

        return $success;
	}

    /**
     * Returns a rating image
     * @param mixed Boolean
     * @return array
     */
    function getRatingImage( $num ) 
    {
        if ($num <= '0')                     { $id = "0"; }
        elseif ($num > '0' && $num <= '0.5') { $id = "0.5"; }
        elseif ($num > '0.5' && $num <= '1') { $id = "1"; }
        elseif ($num > '1' && $num <= '1.5') { $id = "1.5"; }
        elseif ($num > '1.5' && $num <= '2') { $id = "2"; }
        elseif ($num > '2' && $num <= '2.5') { $id = "2.5"; }
        elseif ($num > '2.5' && $num <= '3') { $id = "3"; }
        elseif ($num > '3' && $num <= '3.5') { $id = "3.5"; }
        elseif ($num > '3.5' && $num <= '4') { $id = "4"; }
        elseif ($num > '4' && $num <= '4.5') { $id = "4.5"; }
        elseif ($num > '4.5' && $num <= '5') { $id = "5"; }
    
        
        switch ($id) 
        {
          case "5":   $return = "<img src='".Tienda::getURL('ratings')."five.png' alt='".JText::_( 'Great' )."' title='".JText::_( 'Great' )."' name='".JText::_( 'Great' )."' align='center' border='0'>"; break;
          case "4.5": $return = "<img src='".Tienda::getURL('ratings')."four_half.png' alt='".JText::_( 'Great' )."' title='".JText::_( 'Great' )."' name='".JText::_( 'Great' )."' align='center' border='0'>"; break;
          case "4":   $return = "<img src='".Tienda::getURL('ratings')."four.png' alt='".JText::_( 'Good' )."' title='".JText::_( 'Good' )."' name='".JText::_( 'Good' )."' align='center' border='0'>"; break;
          case "3.5": $return = "<img src='".Tienda::getURL('ratings')."three_half.png' alt='".JText::_( 'Good' )."' title='".JText::_( 'Good' )."' name='".JText::_( 'Good' )."' align='center' border='0'>"; break;
          case "3":   $return = "<img src='".Tienda::getURL('ratings')."three.png' alt='".JText::_( 'Average' )."' title='".JText::_( 'Average' )."' name='".JText::_( 'Average' )."' align='center' border='0'>"; break;
          case "2.5": $return = "<img src='".Tienda::getURL('ratings')."two_half.png' alt='".JText::_( 'Average' )."' title='".JText::_( 'Average' )."' name='".JText::_( 'Average' )."' align='center' border='0'>"; break;
          case "2":   $return = "<img src='".Tienda::getURL('ratings')."two.png' alt='".JText::_( 'Poor' )."' title='".JText::_( 'Poor' )."' name='".JText::_( 'Poor' )."' align='center' border='0'>"; break;
          case "1.5": $return = "<img src='".Tienda::getURL('ratings')."one_half.png' alt='".JText::_( 'Poor' )."' title='".JText::_( 'Poor' )."' name='".JText::_( 'Poor' )."' align='center' border='0'>"; break;
          case "1":   $return = "<img src='".Tienda::getURL('ratings')."one.png' alt='".JText::_( 'Unsatisfactory' )."' title='".JText::_( 'Unsatisfactory' )."' name='".JText::_( 'Unsatisfactory' )."' align='center' border='0'>"; break;
          case "0.5": $return = "<img src='".Tienda::getURL('ratings')."zero_half.png' alt='".JText::_( 'Unsatisfactory' )."' title='".JText::_( 'Unsatisfactory' )."' name='".JText::_( 'Unsatisfactory' )."' align='center' border='0'>"; break;
          default:    $return = "<img src='".Tienda::getURL('ratings')."zero.png' alt='".JText::_( 'Unrated' )."' title='".JText::_( 'Unrated' )."' name='".JText::_( 'Unrated' )."' align='center' border='0'>"; break;
        }
        
        return $return;
    }
    
    /**
     * 
     * Enter description here ...
     * @return unknown_type
     */
    function updatePriceUserGroups()
    {
        $success = true;
        
        $db = JFactory::getDBO();
        $db->setQuery("UPDATE #__tienda_productprices SET `group_id` = '1' WHERE `group_id` = '0'; ");
        if (!$db->query())
        {
            $this->setError( $db->getErrorMsg() );
            $success = false;
        }
        return $success;
    }
}
