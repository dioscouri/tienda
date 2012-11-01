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
defined( '_JEXEC' ) or die( 'Restricted access' );

Tienda::load( 'TiendaHelperBase', 'helpers._base' );
jimport( 'joomla.filesystem.file' );
jimport( 'joomla.filesystem.folder' );

class TiendaHelperProduct extends TiendaHelperBase
{
    public static $products = array( );
    public static $categoriesxref = array( );

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
    function getLayouts( $options = array( ) )
    {
        $layouts = array( );
        // set the default exclusions array
        $exclusions = array(
                'default.php', 'form_askquestion.php', 'product_buy.php', 'product_children.php', 'product_comments.php',
                'product_files.php', 'product_gallery.php', 'product_rating.php', 'product_relations.php',
                'product_requirements.php', 'product_share_buttons.php', 'quickadd.php', 'search.php'
        );
        // TODO merge $exclusions with $options['exclude']

        jimport( 'joomla.filesystem.file' );
        $app = JFactory::getApplication( );
        if ( $app->isAdmin( ) )
        {
            // TODO This doesn't account for when templates are assigned to menu items.  Make it do so
            $db = JFactory::getDBO();
            if (version_compare(JVERSION, '1.6.0', 'ge')) {
                // Joomla! 1.6+ code here
                $db -> setQuery("SELECT `template` FROM #__template_styles WHERE `home` = '1' AND `client_id` = '0';");
            } else {
                // Joomla! 1.5 code here
                $db -> setQuery("SELECT `template` FROM #__templates_menu WHERE `menuid` = '0' AND `client_id` = '0';");
            }

            $template = $db -> loadResult();

        }
        else
        {
            $template = $app->getTemplate( );
        }
        $folder = JPATH_SITE . DS . 'templates' . DS . $template . DS . 'html' . DS . 'com_tienda' . DS . 'products';

        if ( JFolder::exists( $folder ) )
        {
            $extensions = array(
                    'php'
            );
            	
            $files = JFolder::files( $folder );
            foreach ( $files as $file )
            {
                $namebits = explode( '.', $file );
                $extension = $namebits[count( $namebits ) - 1];
                if ( in_array( $extension, $extensions ) && ( substr( $file, 0, 8 ) != 'product_' ) )
                {
                    if ( !in_array( $file, $exclusions ) && !in_array( $file, $layouts ) )
                    {
                        $layouts[] = $file;
                    }
                }
            }
        }

        // now do the tienda folder
        $folder = JPATH_SITE . DS . 'components' . DS . 'com_tienda' . DS . 'views' . DS . 'products' . DS . 'tmpl';

        if ( JFolder::exists( $folder ) )
        {
            $extensions = array(
                    'php'
            );
            	
            $files = JFolder::files( $folder );
            foreach ( $files as $file )
            {
                $namebits = explode( '.', $file );
                $extension = $namebits[count( $namebits ) - 1];
                if ( in_array( $extension, $extensions ) )
                {
                    if ( !in_array( $file, $exclusions ) && !in_array( $file, $layouts ) )
                    {
                        $layouts[] = $file;
                    }
                }
            }
        }

        // now do the media templates folder
        $folder = Tienda::getPath( 'products_templates' );

        if ( JFolder::exists( $folder ) )
        {
            $extensions = array(
                    'php'
            );
            	
            $files = JFolder::files( $folder );
            foreach ( $files as $file )
            {
                $namebits = explode( '.', $file );
                $extension = $namebits[count( $namebits ) - 1];
                if ( in_array( $extension, $extensions ) )
                {
                    if ( !in_array( $file, $exclusions ) && !in_array( $file, $layouts ) )
                    {
                        $layouts[] = $file;
                    }
                }
            }
        }

        sort( $layouts );

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
    function getLayout( $product_id, $options = array( ) )
    {
        static $template;

        $dispatcher = JDispatcher::getInstance();
        $layout = 'view';

        jimport( 'joomla.filesystem.file' );
        $app = JFactory::getApplication( );

        if ( empty( $template ) )
        {
            $template = $app->getTemplate( );
        }

        $templatePath = JPATH_SITE . DS . 'templates' . DS . $template . DS . 'html' . DS . 'com_tienda' . DS . 'products' . DS . '%s' . '.php';
        $extensionPath = JPATH_SITE . DS . 'components' . DS . 'com_tienda' . DS . 'views' . DS . 'products' . DS . 'tmpl' . DS . '%s' . '.php';
        $mediaPath = Tienda::getPath( 'products_templates' ) . DS . '%s' . '.php';

        if ( isset( $this ) && is_a( $this, 'TiendaHelperProduct' ) )
        {
            $helper = &$this;
        }
        else
        {
            $helper = TiendaHelperBase::getInstance( 'Product' );
        }
        $product = $helper->load( ( int ) $product_id );

        // if the product->product_layout file exists in the template, use it
        if ( !empty( $product->product_layout )
                && ( JFile::exists( sprintf( $templatePath, $product->product_layout ) )
                        || JFile::exists( sprintf( $extensionPath, $product->product_layout ) )
                        || JFile::exists( sprintf( $mediaPath, $product->product_layout ) ) ) )
        {
            $new_layout = $dispatcher->trigger('onGetLayoutProduct', array( $product, $product->product_layout ) );
            	
            if( count( $new_layout ) )
                return $new_layout[0];
            else
                return $product->product_layout;
        }

        if ( !empty( $options['category_id'] ) )
        {
            $helper_category = TiendaHelperBase::getInstance( 'Category' );
            $category_id = $options['category_id'];
            if ( empty( $helper_category->categories[$category_id] ) )
            {
                // if the options[category_id] has a layout and it exists, use it
                Tienda::load( 'TiendaTableCategories', 'tables.categories' );
                $helper_category->categories[$category_id] = JTable::getInstance( 'Categories', 'TiendaTable' );
                $helper_category->categories[$category_id]->load( $category_id );
            }
            $category = $helper_category->categories[$category_id];
            	
            if ( !empty( $category->categoryproducts_layout )
                    && ( JFile::exists( sprintf( $templatePath, $category->categoryproducts_layout ) )
                            || JFile::exists( sprintf( $extensionPath, $category->categoryproducts_layout ) ) ) )
            {
                $new_layout = $dispatcher->trigger('onGetLayoutProduct', array( $product, $category->categoryproducts_layout ) );

                if( count( $new_layout ) )
                    return $new_layout[0];
                else
                    return $category->categoryproducts_layout;
            }
        }

        // if the product is in a category, try to use the layout from that one
        $categories = $helper->getCategories( $product->product_id );
        if ( !empty( $categories ) )
        {
            $helper_category = TiendaHelperBase::getInstance( 'Category' );
            $category_id = $categories[0];
            if ( empty( $helper_category->categories[$category_id] ) )
            {
                // if the options[category_id] has a layout and it exists, use it
                Tienda::load( 'TiendaTableCategories', 'tables.categories' );
                $helper_category->categories[$category_id] = JTable::getInstance( 'Categories', 'TiendaTable' );
                $helper_category->categories[$category_id]->load( $category_id );
            }
            $category = $helper_category->categories[$category_id];
            	
            if ( !empty( $category->categoryproducts_layout )
                    && ( JFile::exists( sprintf( $templatePath, $category->categoryproducts_layout ) )
                            || JFile::exists( sprintf( $extensionPath, $category->categoryproducts_layout ) ) ) )
            {
                $new_layout = $dispatcher->trigger('onGetLayoutProduct', array( $product, $category->categoryproducts_layout ) );

                if( count( $new_layout ) )
                    return $new_layout[0];
                else
                    return $category->categoryproducts_layout;
            }
        }

        // TODO if there are multiple categories, which one determines product layout?
        // if the product is in multiple categories, try to use the layout from the deepest category
        // and move upwards in tree after that

        // if all else fails, use the default!
        $new_layout = $dispatcher->trigger('onGetLayoutProduct', array( $product, $layout ) );

        if( count( $new_layout ) )
            return $new_layout[0];
        else
            return $layout;
    }

    /**
     * Converts a path string to a URI string
     *
     * @param $path
     * @return unknown_type
     */
    public static function getUriFromPath( $path )
    {
        $path = str_replace( JPATH_SITE . DS, JURI::root( ), $path );
        $path = str_replace( DS, '/', $path );
        return $path;
    }

    /**
     * Will consolidate a product's images into its currently set path.
     * If an image already exists in the current path with the same name,
     * will either leave the iamge in the old path or delete it if delete_duplicates = true
     *
     * @param $row	Tienda Product
     * @param $delete_duplicates
     * @return unknown_type
     */
    function consolidateGalleryImages( $row, $delete_duplicates = false )
    {
        $file_moved = null;

        // get the current path for the product
        $path = $this->getGalleryPath( $row );

        // get the current list of images in the current path
        $images = $this->getGalleryImages( $path, array(), false);


        // if there are any images in the other possible paths for the product, move them to the current path
        $dir = Tienda::getPath( 'products_images' );

        // merge the SKU-based dir if it exists and isn't the current path
        if ( !empty( $row->product_sku ) && $this->checkDirectory( $dir . DS . $row->product_sku, false )
                && ( $dir . DS . $row->product_sku . DS != $path ) )
        {
            $old_dir = $dir . DS . $row->product_sku . DS;
            	
            $files = JFolder::files( $old_dir );
            foreach ( $files as $file )
            {
                if ( !in_array( $file, $images ) )
                {
                    if ( JFile::move( $old_dir . $file, $path . $file ) )
                    {
                        // create new thumb too
                        Tienda::load( 'TiendaImage', 'library.image' );
                        $img = new TiendaImage( $path . $file);
                        $img->setDirectory( $path );
                        Tienda::load( 'TiendaHelperImage', 'helpers.image' );
                        $imgHelper = TiendaHelperBase::getInstance( 'Image', 'TiendaHelper' );
                        $imgHelper->resizeImage( $img );

                        // delete old thumb
                        JFile::delete( $old_dir . 'thumbs' . DS . $file );

                        $file_moved = true;
                    }
                }
                else
                {
                    // delete the old one?
                    if ( $delete_duplicates )
                    {
                        JFile::delete( $old_dir . $file );
                    }
                }
            }
        }
        else
        {
            $subdirs = TiendaHelperProduct::getSha1Subfolders( $row->product_sku );
            // merge the SKU-based SHA1 dir if it exists and isn't the current path
            if ( !empty( $row->product_sku ) && $this->checkDirectory( $dir . DS . $subdirs . $row->product_sku, false )
                    && ( $dir . DS . $subdirs . $row->product_sku . DS != $path ) )
            {
                $old_dir = $dir . DS . $subdirs . $row->product_sku . DS;

                $files = JFolder::files( $old_dir );
                foreach ( $files as $file )
                {
                    if ( !in_array( $file, $images ) )
                    {
                        if ( JFile::move( $old_dir . $file, $path . $file ) )
                        {
                            // create new thumb too
                            Tienda::load( 'TiendaImage', 'library.image' );
                            $img = new TiendaImage( $path . $file);
                            $img->setDirectory( $path );
                            Tienda::load( 'TiendaHelperImage', 'helpers.image' );
                            $imgHelper = TiendaHelperBase::getInstance( 'Image', 'TiendaHelper' );
                            $imgHelper->resizeImage( $img );
                            	
                            // delete old thumb
                            JFile::delete( $old_dir . 'thumbs' . DS . $file );
                            	
                            $file_moved = true;
                        }
                    }
                    else
                    {
                        // delete the old one?
                        if ( $delete_duplicates )
                        {
                            JFile::delete( $old_dir . $file );
                        }
                    }
                }
            }
        }

        // merge the ID-based dir if it exists and isn't the current path
        if ( $this->checkDirectory( $dir . DS . $row->product_id, false ) && ( $dir . DS . $row->product_id . DS != $path ) )
        {
            $old_dir = $dir . DS . $row->product_id . DS;
            	
            $files = JFolder::files( $old_dir );
            foreach ( $files as $file )
            {
                if ( !in_array( $file, $images ) )
                {
                    if ( JFile::move( $old_dir . $file, $path . $file ) )
                    {
                        // create new thumb too
                        Tienda::load( 'TiendaImage', 'library.image' );
                        $img = new TiendaImage( $path . $file);
                        $img->setDirectory( $path );
                        Tienda::load( 'TiendaHelperImage', 'helpers.image' );
                        $imgHelper = TiendaHelperBase::getInstance( 'Image', 'TiendaHelper' );
                        $imgHelper->resizeImage( $img );
                        // delete old thumb
                        JFile::delete( $old_dir . 'thumbs' . DS . $file );

                        $file_moved = true;
                    }
                }
                else
                {
                    // delete the old one?
                    if ( $delete_duplicates )
                    {
                        JFile::delete( $old_dir . $file );
                    }
                }
            }
        }
        else
        {
            $subdirs = TiendaHelperProduct::getSha1Subfolders( $row->product_id );
            // merge the ID-based SHA1 dir if it exists and isn't the current path
            if ( $this->checkDirectory( $dir . DS . $subdirs . $row->product_id, false ) && ( $dir . DS . $subdirs . $row->product_id . DS != $path ) )
            {
                $old_dir = $dir . DS . $subdirs . $row->product_id . DS;

                $files = JFolder::files( $old_dir );
                foreach ( $files as $file )
                {
                    if ( !in_array( $file, $images ) )
                    {
                        if ( JFile::move( $old_dir . $file, $path . $file ) )
                        {
                            // create new thumb too
                            Tienda::load( 'TiendaImage', 'library.image' );
                            $img = new TiendaImage( $path . $file);
                            $img->setDirectory( $path );
                            Tienda::load( 'TiendaHelperImage', 'helpers.image' );
                            $imgHelper = TiendaHelperBase::getInstance( 'Image', 'TiendaHelper' );
                            $imgHelper->resizeImage( $img );
                            // delete old thumb
                            JFile::delete( $old_dir . 'thumbs' . DS . $file );
                            	
                            $file_moved = true;
                        }
                    }
                    else
                    {
                        // delete the old one?
                        if ( $delete_duplicates )
                        {
                            JFile::delete( $old_dir . $file );
                        }
                    }
                }
            }
        }

        return $file_moved;
    }

    /**
     *
     * Thanks to http://ryan.ifupdown.com/2008/08/17/warning-mkdir-too-many-links/
     * @param $string
     * @param $separator
     * @return unknown_type
     */
    function getSha1Subfolders( $string, $separator = DS )
    {
        $sha1 = strtoupper( sha1( $string ) );

        // 4 level subfolding using sha1
        $i = 0;
        $subdirs = '';
        while ( $i < 4 )
        {
            if ( strlen( $string ) >= $i )
            {
                $subdirs .= $sha1[$i] . $separator;
            }
            $i++;
        }

        return $subdirs;
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
    public static function getGalleryImages( $folder = null, $options = array( ), $triggerEvent = true )
    {
        $images = array( );

        if ( empty( $folder ) )
        {
            return $images;
        }

        if ( empty( $options['exclude'] ) )
        {
            $options['exclude'] = array( );
        }
        elseif ( !is_array( $options['exclude'] ) )
        {
            $options['exclude'] = array(
                    $options['exclude']
            );
        }

        if ( JFolder::exists( $folder ) )
        {
            $extensions = array(
                    'png', 'gif', 'jpg', 'jpeg'
            );
            	
            $files = JFolder::files( $folder );
            foreach ( $files as $file )
            {
                $namebits = explode( '.', $file );
                $extension = strtolower( $namebits[count( $namebits ) - 1] );
                if ( in_array( $extension, $extensions ) )
                {
                    if ( !in_array( $file, $options['exclude'] ) )
                    {
                        $images[] = $file;
                    }
                }
            }
        }

        if( $triggerEvent )
        {
            $dispatcher = JDispatcher::getInstance();
            $dispatcher->trigger( 'onPrepareGalleryImages', array( &$images ) );
        }

        return $images;
    }

    /**
     * Returns the full path to the product's image gallery files
     *
     * @param int $id
     * @return string
     */
    public static function getGalleryPath( $row )
    {
        static $paths;


        if( is_object( $row ) ) // passed TiendaTable object
        {
            $id = $row->product_id;
            $paths[ $id ] = $row->getImagePath( true );
        }
        else
        {
            $id = ( int ) $row;

            if ( !is_array( $paths ) )
            {
                $paths = array( );
            }

            if ( empty( $paths[$id] ) )
            {
                $paths[$id] = '';
                	
                if ( isset( $this ) && is_a( $this, 'TiendaHelperProduct' ) )
                {
                    $helper = &$this;
                }
                else
                {
                    $helper = new TiendaHelperProduct();
                }
                $row = $helper->load( ( int ) $id, true, false );
                	
                if ( empty( $row->product_id ) )
                {
                    // TODO figure out what to do if the id is invalid
                    return null;
                }
                	
                $paths[$id] = $row->getImagePath( true );
            }
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

        $id = ( int ) $id;

        if ( !is_array( $urls ) )
        {
            $urls = array( );
        }

        if ( empty( $urls[$id] ) )
        {
            $urls[$id] = '';
            	
            JTable::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables' );
            $row = JTable::getInstance( 'Products', 'TiendaTable' );
            $row->load( ( int ) $id, true, false );
            if ( empty( $row->product_id ) )
            {
                // TODO figure out what to do if the id is invalid
                return null;
            }
            	
            $urls[$id] = $row->getImageUrl( );
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

        $id = ( int ) $id;

        if ( !is_array( $paths ) )
        {
            $paths = array( );
        }

        if ( empty( $paths[$id] ) )
        {
            $paths[$id] = '';
            	
            JTable::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables' );
            $row = JTable::getInstance( 'Products', 'TiendaTable' );
            $row->load( ( int ) $id, true, false );
            if ( empty( $row->product_id ) )
            {
                // TODO figure out what to do if the id is invalid
                return null;
            }
            	
            // if product_images_path is valid and not empty, use it
            if ( !empty( $row->product_files_path ) )
            {
                $folder = $row->product_files_path;
                if ( JFolder::exists( $folder ) )
                {
                    $files = JFolder::files( $folder );
                    if ( !empty( $files ) )
                    {
                        $paths[$id] = $folder;
                    }
                }
            }
            	
            // if no override, use path based on sku if it is valid and not empty
            // TODO clean SKU so valid characters used for folder name?
            if ( empty( $paths[$id] ) && !empty( $row->product_sku ) )
            {
                $folder = Tienda::getPath( 'products_files' ) . DS . 'sku' . DS . $row->product_sku;
                if ( JFolder::exists( $folder ) )
                {
                    $files = JFolder::files( $folder );
                    if ( !empty( $files ) )
                    {
                        $paths[$id] = $folder;
                    }
                }
            }
            	
            // if still unset, use path based on id number
            if ( empty( $paths[$id] ) )
            {
                $folder = Tienda::getPath( 'products_files' ) . DS . 'id' . DS . $row->product_id;
                if ( !JFolder::exists( $folder ) )
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
    public static function getImage( $id, $by = 'id', $alt = '', $type = 'thumb', $url = false, $resize = false, $options = array( ), $main_product = false )
    {
        $style = "";
        $height_style = "";
        $width_style = "";

        $dimensions = "";
        if ( !empty( $options['width'] ) )
        {
            $dimensions .= "width=\"" . $options['width'] . "\" ";
        }

        if ( !empty( $options['height'] ) )
        {
            $dimensions .= "height=\"" . $options['height'] . "\" ";
        }

        if ( !empty( $options['height'] ) )
        {
            $height_style = "max-height: " . $options['height'] . "px;";
        }
        if ( !empty( $options['width'] ) )
        {
            $width_style = "max-width: " . $options['width'] . "px;";
        }
        if ( !empty( $height_style ) || !empty( $width_style ) )
        {
            $style = "style='$height_style $width_style'";
        }

        switch ( $type )
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
        if ( strpos( $id, '.' ) )
        {
            // then this is a filename, return the full img tag if file exists, otherwise use a default image
            $src = ( JFile::exists( Tienda::getPath( $path ) . DS . $id ) ) ? Tienda::getUrl( $path ) . $id
            : JURI::root( true ) . '/media/com_tienda/images/noimage.png';
            	
            // if url is true, just return the url of the file and not the whole img tag
            $tmpl = ( $url ) ? $src
            : "<img " . $dimensions . " src='" . $src . "' alt='" . JText::_( $alt ) . "' title='" . JText::_( $alt )
            . "' align='middle' border='0' " . $style . " />";
            	
        }
        else
        {
            if ( !empty( $id ) )
            {
                if ( isset( $this ) && is_a( $this, 'TiendaHelperProduct' ) )
                {
                    $helper = &$this;
                }
                else
                {
                    $helper = TiendaHelperBase::getInstance( 'Product' );
                }
                $row = $helper->load( ( int ) $id, true, false );
                $full_image = $row->product_full_image; // in case it'll be changed by an event
                // load the item, get the filename, create tmpl
                $urli = $row->getImageUrl( );
                $dir = $row->getImagePath( );

                if ( $path == 'products_thumbs' )
                {
                    $dir .= 'thumbs';
                    $urli .= 'thumbs/';
                }

                if( $main_product )
                {
                    $dispatcher = JDispatcher::getInstance();
                    $dispatcher->trigger('onGetProductMainImage', array( $row->product_id, &$full_image, $options ) );
                }
                $file = $dir . DS . $full_image;

                $id = $urli . $full_image;

                // Gotta do some resizing first?
                if ( $resize )
                {
                    // Add a suffix to the thumb to avoid conflicts with user settings
                    $suffix = '';
                    	
                    if ( isset( $options['width'] ) && isset( $options['height'] ) )
                    {
                        $suffix = '_' . $options['width'] . 'x' . $options['height'];
                    }
                    elseif ( isset( $options['width'] ) )
                    {
                        $suffix = '_w' . $options['width'];
                    }
                    elseif ( isset( $options['height'] ) )
                    {
                        $suffix = '_h' . $options['height'];
                    }
                    	
                    // Add suffix to file path
                    $dot = strrpos( $file, '.' );
                    $resize = substr( $file, 0, $dot ) . $suffix . substr( $file, $dot );
                    	
                    if ( !JFile::exists( $resize ) )
                    {

                        Tienda::load( 'TiendaImage', 'library.image' );
                        $image = new TiendaImage( $file);
                        $image->load( );
                        // If both width and height set, gotta figure hwo to resize
                        if ( isset( $options['width'] ) && isset( $options['height'] ) )
                        {
                            // If width is larger, proportionally
                            if ( ( $options['width'] / $image->getWidth( ) ) < ( $options['height'] / $image->getHeight( ) ) )
                            {
                                $image->resizeToWidth( $options['width'] );
                                $image->save( $resize );
                            }
                            // If height is larger, proportionally
                            else
                            {
                                $image->resizeToHeight( $options['height'] );
                                $image->save( $resize );
                            }
                        }
                        // If only width is set
                        elseif ( isset( $options['width'] ) )
                        {
                            $image->resizeToWidth( $options['width'] );
                            $image->save( $resize );
                        }
                        // If only height is set
                        elseif ( isset( $options['height'] ) )
                        {
                            $image->resizeToHeight( $options['height'] );
                            $image->save( $resize );
                        }

                    }
                    	
                    // Add suffix to url path
                    $dot = strrpos( $id, '.' );
                    $id = substr( $id, 0, $dot ) . $suffix . substr( $id, $dot );
                }

                $src = ( JFile::exists( $file ) ) ? $id : JURI::root( true ) . '/media/com_tienda/images/noimage.png';

                $tmpl = ( $url ) ? $src
                : "<img " . $dimensions . " src='" . $src . "' alt='" . JText::_( $alt ) . "' title='" . JText::_( $alt )
                . "' align='middle' border='0' " . $style . " />";
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
    public static function getPrices( $id )
    {
        static $sets;

        if( !$id )
        {
            return array();
        }

        if ( empty( $sets ) || !is_array( $sets ) )
        {
            $sets = array( );
        }

        if ( empty( $sets[$id] ) )
        {
            JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
            $model = JModel::getInstance( 'ProductPrices', 'TiendaModel' );
            $model->setState( 'filter_id', $id );
            $sets[$id] = $model->getList( );
        }

        return $sets[$id];
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
    function getPrice( $id, $quantity = '1', $group_id = '', $date = '' )
    {
        // $sets[$id][$quantity][$group_id][$date]
        static $sets;

        if ( !is_array( $sets ) )
        {
            $sets = array( );
        }

        $price = null;
        if ( empty( $id ) )
        {
            return $price;
        }

        if ( !isset( $sets[$id][$quantity][$group_id][$date] ) )
        {
            $product_helper = TiendaHelperBase::getInstance( 'Product' );
            $prices = $product_helper->getPrices( $id );
            	
            JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
            $model = JModel::getInstance( 'ProductPrices', 'TiendaModel' );
            $model->setState( 'filter_id', $id );
            	
            ( int ) $quantity;
            if ( $quantity <= '0' )
            {
                $quantity = '1';
            }
            //where price_quantity_start < $quantity
            $model->setState( 'filter_quantity', $quantity );
            	
            // does date even matter?
            $nullDate = JFactory::getDBO( )->getNullDate( );
            if ( empty( $date ) || $date == $nullDate )
            {
                $date = JFactory::getDate( )->toMysql( );
            }
            $model->setState( 'filter_date', $date );
            //where product_price_startdate <= $date
            //where product_price_enddate >= $date OR product_price_enddate == nullDate
            	
            // does group_id?
            ( int ) $group_id;
            $default_user_group = Tienda::getInstance( )->get( 'default_user_group', '1' ); /* Use a default $group_id */
            if ( $group_id <= '0' )
            {
                $group_id = $default_user_group;
            }
            // using ->getPrices(), do a getColumn() on the array for the group_id column
            $group_ids = TiendaHelperBase::getColumn( $prices, 'group_id' );
            if ( !in_array( $group_id, $group_ids ) )
            {
                // if $group_id is in the column, then set the query to pull an exact match on it,
                // otherwise, $group_id_determined = the default $group_id
                $group_id = $default_user_group;
            }
            $model->setState( 'filter_user_group', $group_id );
            	
            // set the ordering so the most discounted item is at the top of the list
            $model->setState( 'order', 'price_quantity_start' );
            $model->setState( 'direction', 'DESC' );
            	
            // TiendaModelProductPrices is a special model that overrides getItem
            $price = $model->getItem( );
            $sets[$id][$quantity][$group_id][$date] = $price;
        }

        return $sets[$id][$quantity][$group_id][$date];
    }

    /**
     *
     * Gets the tax total for a product based on an array of geozones
     * @param $product_id
     * @param $geozones
     * @return object
     */
    public function getTaxTotal( $product_id, $geozones, $price = null )
    {
        $product_price = 0;
        if( $price )
            $product_price = $price;
        else
        {
            JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
            $model = JModel::getInstance( 'Products', 'TiendaModel' );
            Tienda::load( 'TiendaHelperUser', 'helpers.user' );
            $user_id = JFactory::getUser( )->id;
            $filter_group = TiendaHelperUser::getUserGroup( $user_id, $product_id );
            $model->setState( 'filter_group', $filter_group );
            $model->setId( $product_id );
            $row = $model->getItem( false, false );
            $product_price = $row->price;
        }

        $zones = array();
        foreach( $geozones as $geozone )
            $zones []= $geozone->geozone_id;

        $orderitem_tax = 0;
        $tax_rates = array( );
        $tax_amounts = array( );
        Tienda::load('TiendaHelperTax', 'helpers.tax' );
        $product = new stdClass();
        $product->product_id = $product_id;
        $product->product_price = $product_price;
        $taxrates = TiendaHelperTax::calculateGeozonesTax( array( $product ), 2, $zones );

        foreach( $taxrates->tax_rate_rates as $rate )
        {
            $tax_rates[$rate->tax_rate_id] = $rate;
            $tax_amounts[$rate->tax_rate_id] = $rate->applied_tax;
        }

        $return = new stdClass( );
        $return->tax_rates = $tax_rates;
        $return->tax_amounts = $tax_amounts;
        $return->tax_total = $taxrates->tax_total;
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
    public function getTaxRate( $product_id, $geozone_id, $return_object = false )
    {
        // $sets[$product_id][$geozone_id] == object
        static $sets;
        if ( !is_array( $sets ) )
        {
            $sets = array( );
        }

        if ( !isset( $sets[$product_id][$geozone_id] ) )
        {
            JTable::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables' );
            $taxrate = JTable::getInstance( 'TaxRates', 'TiendaTable' );
            $taxrate->tax_rate = "0.00000";
            	
            Tienda::load( 'TiendaQuery', 'library.query' );
            	
            $db = JFactory::getDBO( );
            	
            $query = new TiendaQuery( );
            $query->select( 'tbl.*' );
            $query->from( '#__tienda_taxrates AS tbl' );
            $query->join( 'LEFT', '#__tienda_products AS product ON product.tax_class_id = tbl.tax_class_id' );
            $query->where( "product.product_id = '" . $product_id . "'" );
            $query->where( "tbl.geozone_id = '" . $geozone_id . "'" );
            	
            $db->setQuery( ( string ) $query );
            if ( $data = $db->loadObject( ) )
            {
                $taxrate->load( array(
                        'tax_rate_id' => $data->tax_rate_id
                ) );
            }
            $sets[$product_id][$geozone_id] = $taxrate;
        }
        if ( !$return_object )
        {
            return $sets[$product_id][$geozone_id]->tax_rate;
        }
        return $sets[$product_id][$geozone_id];
    }

    /**
     * Gets a product's list of categories
     *
     * @param $id
     * @return array
     */
    public static function getCategories( $id )
    {
        if ( isset( $this ) && is_a( $this, 'TiendaHelperProduct' ) )
        {
            $helper = $this;
        }
        else
        {
            $helper = TiendaHelperBase::getInstance( 'Product' );
        }

        if ( empty( self::$categoriesxref[$id] ) )
        {
            Tienda::load( 'TiendaQuery', 'library.query' );
            JTable::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables' );
            $table = JTable::getInstance( 'ProductCategories', 'TiendaTable' );
            	
            $query = new TiendaQuery( );
            $query->select( "tbl.category_id" );
            $query->from( $table->getTableName( ) . " AS tbl" );
            $query->where( "tbl.product_id = " . ( int ) $id );
            $db = JFactory::getDBO( );
            $db->setQuery( ( string ) $query );
            self::$categoriesxref[$id] = $db->loadResultArray( );
        }

        return self::$categoriesxref[$id];
    }

    /**
     * Returns a list of a product's attributes
     *
     * @param int      $id
     * @param int      $parent_option the id of the parent option. Use -1 for "all"
     * @return unknown_type
     */
    public static function getAttributes( $id, $parent_option = "-1" )
    {
        if ( empty( $id ) )
        {
            return array( );
        }
        JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
        $model = JModel::getInstance( 'ProductAttributes', 'TiendaModel' );
        $model->setState( 'filter_product', $id );

        if ( $parent_option != '-1' )
        {
            $model->setState( 'filter_parent_option', $parent_option );
        }

        $model->setState( 'order', 'tbl.ordering' );
        $model->setState( 'direction', 'ASC' );
        $items = $model->getList( );
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
        static $sets;

        if ( empty( $sets ) || !is_array( $sets ) )
        {
            $sets = array( );
        }

        if ( empty( $sets[$id] ) )
        {
            JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
            $model = JModel::getInstance( 'ProductAttributes', 'TiendaModel' );
            $model->setState( 'filter_product', $id );
            $model->setState( 'order', 'tbl.ordering' );
            $model->setState( 'direction', 'ASC' );
            $items = $model->getList( );
            if ( empty( $items ) )
            {
                $sets[$id] = array( );
                return $sets[$id];
            }
            $list = array( );
            foreach ( $items as $item )
            {
                $key = 'attribute_' . $item->productattribute_id;
                $model = JModel::getInstance( 'ProductAttributeOptions', 'TiendaModel' );
                $model->setState( 'filter_attribute', $item->productattribute_id );
                $model->setState( 'order', 'tbl.ordering' );
                $model->setState( 'direction', 'ASC' );
                $options = $model->getList( );
                if ( !empty( $options ) )
                {
                    $option = $options[0];
                    $list[$key] = $option->productattributeoption_id;
                }
            }
            $sets[$id] = $list;
        }

        return $sets[$id];
    }

    /**
     * Returns a list of a product's files
     *
     * @param unknown_type $id
     * @return unknown_type
     */
    function getFiles( $id )
    {
        if ( empty( $id ) )
        {
            return array( );
        }
        JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
        $model = JModel::getInstance( 'ProductFiles', 'TiendaModel' );
        $model->setState( 'filter_product', $id );
        $items = $model->getList( );
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
    function getServerFiles( $folder = null, $options = array( ) )
    {
        $files = array( );

        if ( empty( $folder ) )
        {
            return $files;
        }

        if ( empty( $options['exclude'] ) )
        {
            $options['exclude'] = array( );
        }
        elseif ( !is_array( $options['exclude'] ) )
        {
            $options['exclude'] = array(
                    $options['exclude']
            );
        }

        // Add .htaccess exclusion
        if ( !in_array( '.htaccess', $options['exclude'] ) ) $options['exclude'][] = '.htaccess';

        if ( JFolder::exists( $folder ) )
        {
            $serverfiles = JFolder::files( $folder );
            foreach ( $serverfiles as $file )
            {
                if ( !in_array( $file, $options['exclude'] ) )
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
        $return = array( );

        $prev = intval( JRequest::getVar( "prev" ) );
        $next = intval( JRequest::getVar( "next" ) );
        if ( $prev || $next )
        {
            $return["prev"] = $prev;
            $return["next"] = $next;
            return $return;
        }

        $app = JFactory::getApplication( );
        JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
        $model = JModel::getInstance( 'Products', 'TiendaModel' );
        $ns = $app->getName( ) . '::' . 'com.tienda.model.' . $model->getTable( )->get( '_suffix' );
        $state = array( );

        $config = Tienda::getInstance( );

        $state['limit'] = $app->getUserStateFromRequest( 'global.list.limit', 'limit', $app->getCfg( 'list_limit' ), 'int' );
        $state['limitstart'] = $app->getUserStateFromRequest( $ns . 'limitstart', 'limitstart', 0, 'int' );
        $state['filter'] = $app->getUserStateFromRequest( $ns . '.filter', 'filter', '', 'string' );
        $state['order'] = $app->getUserStateFromRequest( $ns . '.filter_order', 'filter_order', 'tbl.' . $model->getTable( )->getKeyName( ), 'cmd' );
        $state['direction'] = $app->getUserStateFromRequest( $ns . '.filter_direction', 'filter_direction', 'ASC', 'word' );

        $state['filter_id_from'] = $app->getUserStateFromRequest( $ns . 'id_from', 'filter_id_from', '', '' );
        $state['filter_id_to'] = $app->getUserStateFromRequest( $ns . 'id_to', 'filter_id_to', '', '' );
        $state['filter_name'] = $app->getUserStateFromRequest( $ns . 'name', 'filter_name', '', '' );
        $state['filter_enabled'] = $app->getUserStateFromRequest( $ns . 'enabled', 'filter_enabled', '', '' );
        $state['filter_quantity_from'] = $app->getUserStateFromRequest( $ns . 'quantity_from', 'filter_quantity_from', '', '' );
        $state['filter_quantity_to'] = $app->getUserStateFromRequest( $ns . 'quantity_to', 'filter_quantity_to', '', '' );
        $state['filter_category'] = $app->getUserStateFromRequest( $ns . 'category', 'filter_category', '', '' );
        $state['filter_sku'] = $app->getUserStateFromRequest( $ns . 'sku', 'filter_sku', '', '' );
        $state['filter_price_from'] = $app->getUserStateFromRequest( $ns . 'price_from', 'filter_price_from', '', '' );
        $state['filter_price_to'] = $app->getUserStateFromRequest( $ns . 'price_to', 'filter_price_to', '', '' );
        $state['filter_taxclass'] = $app->getUserStateFromRequest( $ns . 'taxclass', 'filter_taxclass', '', '' );
        $state['filter_ships'] = $app->getUserStateFromRequest( $ns . 'ships', 'filter_ships', '', '' );

        foreach ( @$state as $key => $value )
        {
            $model->setState( $key, $value );
        }
        $rowset = $model->getList( false, false );

        $found = false;
        $prev_id = '';
        $next_id = '';

        for ( $i = 0; $i < count( $rowset ) && empty( $found ); $i++ )
        {
            $row = $rowset[$i];
            if ( $row->product_id == $id )
            {
                $found = true;
                $prev_num = $i - 1;
                $next_num = $i + 1;
                if ( isset( $rowset[$prev_num]->product_id ) )
                {
                    $prev_id = $rowset[$prev_num]->product_id;
                }
                if ( isset( $rowset[$next_num]->product_id ) )
                {
                    $next_id = $rowset[$next_num]->product_id;
                }

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
    static function getCombinations( $string, $traits, $i, &$return )
    {
        if ( $i >= count( $traits ) )
        {
            $return[] = str_replace( ' ', ',', trim( $string ) );
        }
        else
        {
            foreach ( $traits[$i] as $trait )
            {
                TiendaHelperProduct::getCombinations( "$string $trait", $traits, $i + 1, $return );
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
    static function getProductAttributeCSVs( $product_id, $attributeOptionId = '0' )
    {
        $return = array( );
        $traits = array( );

        JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );

        // get all productattributes
        $model = JModel::getInstance( 'ProductAttributes', 'TiendaModel' );
        $model->setState( 'filter_product', $product_id );
        if ( $attributes = $model->getList( ) )
        {
            foreach ( $attributes as $attribute )
            {
                $paoModel = JModel::getInstance( 'ProductAttributeOptions', 'TiendaModel' );
                $paoModel->setState( 'filter_attribute', $attribute->productattribute_id );
                if ( $paos = $paoModel->getList( ) )
                {
                    $options = array( );
                    foreach ( $paos as $pao )
                    {
                        // Genrate the arrray of single value with the id of newly created attribute option
                        if ( $attributeOptionId == $pao->productattributeoption_id )
                        {
                            $newOption = array( );
                            $newOption[] = ( string ) $attributeOptionId;
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
        $result = array( );
        foreach ( $return as $csv )
        {
            $values = explode( ',', $csv );
            sort( $values );
            $result[] = implode( ',', $values );
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
    static function doProductQuantitiesReconciliation( $product_id, $vendor_id = '0', $attributeOptionId = '0' )
    {
        if ( empty( $product_id ) )
        {
            return false;
        }

        $csvs = TiendaHelperProduct::getProductAttributeCSVs( $product_id, $attributeOptionId );
        JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
        $model = JModel::getInstance( 'ProductQuantities', 'TiendaModel' );
        $model->setState( 'filter_productid', $product_id );
        $model->setState( 'filter_vendorid', $vendor_id );
        $items = $model->getList( );

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
    static function reconcileProductAttributeCSVs( $product_id, $vendor_id, $items, $csvs )
    {
        // remove extras
        $done = array( );
        foreach ( $items as $key => $item )
        {
            if ( !in_array( $item->product_attributes, $csvs ) || in_array( $item->product_attributes, $done ) )
            {
                $row = JTable::getInstance( 'ProductQuantities', 'TiendaTable' );
                if ( !$row->delete( $item->productquantity_id ) )
                {
                    JError::raiseNotice( '1', $row->getError( ) );
                }
                unset( $items[$key] );
            }
            $done[] = $item->product_attributes;
        }

        // add new ones
        $existingEntries = TiendaHelperBase::getColumn( $items, 'product_attributes' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables' );
        foreach ( $csvs as $csv )
        {
            if ( !in_array( $csv, $existingEntries ) )
            {
                $row = JTable::getInstance( 'ProductQuantities', 'TiendaTable' );
                $row->product_id = $product_id;
                $row->vendor_id = $vendor_id;
                $row->product_attributes = $csv;
                if ( !$row->save( ) )
                {
                    JError::raiseNotice( '1', $row->getError( ) );
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
        static $sets;

        if ( empty( $sets ) || !is_array( $sets ) )
        {
            $sets = array( );
        }

        if ( empty( $sets[$id] ) )
        {
            JTable::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables' );
            $table = JTable::getInstance( 'Products', 'TiendaTable' );
            $table->load( ( int ) $id, true, false );
            $sets[$id] = $table;
        }

        if ( $sets[$id]->product_ships )
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
    function relationshipExists( $product_from, $product_to, $relation_type = 'relates' )
    {
        JTable::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables' );
        $table = JTable::getInstance( 'ProductRelations', 'TiendaTable' );
        $keys = array(
                'product_id_from' => $product_from, 'product_id_to' => $product_to, 'relation_type' => $relation_type,
        );
        $table->load( $keys );
        if ( !empty( $table->product_id_from ) )
        {
            return true;
        }

        // relates can be inverted
        if ( $relation_type == 'relates' )
        {
            // so try the inverse
            $table = JTable::getInstance( 'ProductRelations', 'TiendaTable' );
            $keys = array(
                    'product_id_from' => $product_to, 'product_id_to' => $product_from, 'relation_type' => $relation_type,
            );
            $table->load( $keys );
            if ( !empty( $table->product_id_from ) )
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
        JTable::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables' );

        $tableQuantity = JTable::getInstance( 'ProductQuantities', 'TiendaTable' );
        $query = new TiendaQuery( );

        $select[] = "quantities.product_attributes";
        $select[] = "quantities.quantity";

        $query->select( $select );
        $query->from( $tableQuantity->getTableName( ) . " AS quantities" );
        $query->where( "quantities.product_id = " . $id );

        $db = JFactory::getDBO( );
        $db->setQuery( ( string ) $query );

        $results = $db->loadObjectList( );
        $inventoryList = array( );

        foreach ( $results as $result )
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
        JTable::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables' );

        $tableQuantity = JTable::getInstance( 'ProductQuantities', 'TiendaTable' );
        $tableProduct = JTable::getInstance( 'Products', 'TiendaTable' );

        $tableProduct->load( $id, true, false );
        if ( empty( $tableProduct->product_check_inventory ) )
        {
            $tableProduct->quantity = '9999';
            return $tableProduct;
        }

        $query = new TiendaQuery( );

        $select[] = "product.product_name";
        $select[] = "quantities.quantity";
        $select[] = "product.product_check_inventory";

        $query->select( $select );
        $query->from( $tableProduct->getTableName( ) . " AS product" );

        $leftJoinCondition = $tableQuantity->getTableName( ) . " as quantities ON product.product_id = quantities.product_id ";
        $query->leftJoin( $leftJoinCondition );

        $whereClause[] = "quantities.product_id = " . ( int ) $id;
        $whereClause[] = "quantities.product_attributes='" . $attribute . "'";
        $whereClause[] = "product.product_check_inventory =1 ";
        $query->where( $whereClause, "AND" );

        $db = JFactory::getDBO( );
        $db->setQuery( ( string ) $query );
        $item = $db->loadObject( );

        if ( empty( $item ) )
        {
            $return = new JObject( );
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

    public static function getOrders( $product_id )
    {
        //Check the registry to see if our Tienda class has been overridden
        if ( !class_exists( 'Tienda' ) ) JLoader::register( "Tienda",
                JPATH_ADMINISTRATOR . DS . "components" . DS . "com_tienda" . DS . "defines.php" );
        // load the config class
        Tienda::load( 'Tienda', 'defines' );
        JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
        // get the model
        $model = JModel::getInstance( 'OrderItems', 'TiendaModel' );
        $user = JFactory::getUser( );
        $model->setState( 'filter_userid', $user->id );
        $model->setState( 'filter_productid', $product_id );
        $orders = $model->getList( true, false );
        return $orders;

    }

    /**
     * Gets a product's id and User id from Product comment table
     *
     * @param $id
     * @return array
     */
    public static function getUserAndProductIdForReview( $product_id, $user_id )
    {
        if ( empty( $product_id ) && empty( $user_id ) )
        {
            return array( );
        }
        Tienda::load( 'TiendaQuery', 'library.query' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables' );
        $table = JTable::getInstance( 'productcomments', 'TiendaTable' );

        $query = new TiendaQuery( );
        $query->select( "tbl.productcomment_id" );
        $query->from( $table->getTableName( ) . " AS tbl" );
        //$query->where( "tbl.product_id = ".(int) $id ." AND tbl.user_id= ".(int)$uid);
        $query->where( "tbl.product_id = " . ( int ) $product_id );
        $query->where( "tbl.user_id = " . ( int ) $user_id );

        $db = JFactory::getDBO( );
        $db->setQuery( ( string ) $query );
        $items = $db->loadResultArray( );
        return $items;
    }

    /**
     * Gets user's emails from Product comment table
     *
     * @param int $product_id
     * @return array
     */
    public static function getUserEmailForReview( $product_id )
    {
        if ( empty( $product_id ) )
        {
            return array( );
        }
        Tienda::load( 'TiendaQuery', 'library.query' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR .'/components/com_tienda/tables' );
        $table = JTable::getInstance( 'productcomments', 'TiendaTable' );

        $query = new TiendaQuery( );
        $query->select( "tbl.user_email" );
        $query->from( $table->getTableName( ) . " AS tbl" );
        $query->where( "tbl.product_id = " . ( int ) $product_id );

        $db = JFactory::getDBO( );
        $db->setQuery( ( string ) $query );
        $items = $db->loadResultArray( );

        return $items;
    }

    /**
     *
     * Check if the current user already given a feedback to a review
     * @param $uid - the id of the user
     * @param $cid - comment id
     * @return boolean
     */
    public static function isFeedbackAlready( $uid, $cid )
    {

        Tienda::load( 'TiendaQuery', 'library.query' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_tienda/tables' );
        $table = JTable::getInstance( 'productcommentshelpfulness', 'TiendaTable' );

        $query = new TiendaQuery( );
        $query->select( "tbl.productcommentshelpfulness_id" );
        $query->from( $table->getTableName( ) . " AS tbl" );
        $query->where( "tbl.user_id = " . ( int ) $uid );
        $query->where( "tbl.productcomment_id = " . ( int ) $cid );

        $db = JFactory::getDBO( );
        $db->setQuery( ( string ) $query );
        $items = $db->loadResultArray( );

        return !empty( $items ) ? true : false;
    }

    /**
     * returns a product's quantity list for all combination
     * @return array of the entire Objects;
     */
    public function getProductQuantitiesObjects( $id )
    {
        Tienda::load( 'TiendaQuery', 'library.query' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables' );

        $tableQuantity = JTable::getInstance( 'ProductQuantities', 'TiendaTable' );
        $query = new TiendaQuery( );
        $select[] = "quantities.*";

        $query->select( $select );
        $query->from( $tableQuantity->getTableName( ) . " AS quantities" );
        $query->where( "quantities.product_id = " . $id );

        $db = JFactory::getDBO( );
        $db->setQuery( ( string ) $query );

        $results = $db->loadObjectList( );

        //are we going to dump the result???
        //JFactory::getApplication()->enqueueMessage( Tienda::dump( $results ) );
        return $results;
    }

    /**
     * returns a attributes's options list
     * @return array of the entire Objects;
     */
    public function getAttributeOptionsObjects( $id )
    {
        if ( empty( $id ) )
        {
            return array( );
        }

        JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
        $model = JModel::getInstance( 'ProductAttributeOptions', 'TiendaModel' );
        $model->setState( 'filter_attribute', $id );
        $model->setState( 'order', 'tbl.ordering' );
        $model->setState( 'direction', 'ASC' );
        $items = $model->getList( );
        return $items;
    }

    /**
     *
     * Used in diagnostic helper
     * @param $product_id
     * @return unknown_type
     */
    public function updateOverallRatings( )
    {
        $success = true;

        JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
        $model = JModel::getInstance( 'ProductComments', 'TiendaModel' );
        $model->setState( 'filter_enabled', '1' );
        $model->setState( 'select', 'tbl.product_id' );
        $query = $model->getQuery( );
        $query->select( 'tbl.productcomment_id' );
        $query->group( 'tbl.product_id' );
        $model->setQuery( $query );
        if ( $items = $model->getList( ) )
        {
            foreach ( $items as $item )
            {
                // get the product row
                $product = JTable::getInstance( 'Products', 'TiendaTable' );
                $product->load( $item->product_id, true, false );
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
    public static function getRatingImage( $num, $view=null, $clickable = false , $layout = 'product_rating' )
    {
        if( !$clickable )
        {
            if ( $num <= '0' )
            {
                $id = "0";
            }
            elseif ( $num > '0' && $num <= '0.5' )
            {
                $id = "0.5";
            }
            elseif ( $num > '0.5' && $num <= '1' )
            {
                $id = "1";
            }
            elseif ( $num > '1' && $num <= '1.5' )
            {
                $id = "1.5";
            }
            elseif ( $num > '1.5' && $num <= '2' )
            {
                $id = "2";
            }
            elseif ( $num > '2' && $num <= '2.5' )
            {
                $id = "2.5";
            }
            elseif ( $num > '2.5' && $num <= '3' )
            {
                $id = "3";
            }
            elseif ( $num > '3' && $num <= '3.5' )
            {
                $id = "3.5";
            }
            elseif ( $num > '3.5' && $num <= '4' )
            {
                $id = "4";
            }
            elseif ( $num > '4' && $num <= '4.5' )
            {
                $id = "4.5";
            }
            elseif ( $num > '4.5' && $num <= '5' )
            {
                $id = "5";
            }
        }
        $rating = new stdClass();
        $rating->clickable = $clickable;
        if( !$clickable )
            $rating->rating = $id;
        $rating->count = $num;

        if( $view === null ) // if nothing is specified, load products view
            $view = TiendaHelperProduct::getProductViewObject();

        $view->rating =  $rating;
        $view->setLayout( $layout );

        $result = $view->loadTemplate( null );
        if (JError::isError($result))
            return '';
        	
        unset( $view->rating );

        return $result;
    }

    /**
     *
     * Used in Diagnostic helper
     * @return unknown_type
     */
    function updatePriceUserGroups( )
    {
        $success = true;

        $db = JFactory::getDBO( );
        $db->setQuery( "UPDATE #__tienda_productprices SET `group_id` = '1' WHERE `group_id` = '0'; " );
        if ( !$db->query( ) )
        {
            $this->setError( $db->getErrorMsg( ) );
            $success = false;
        }
        return $success;
    }

    /**
     * Function executes after a product is saved
     * and is used for triggering native integrations
     * (rather than encurring the overhead of a plugin)
     *
     * @param $product
     * @return unknown_type
     */
    function onAfterSaveProducts( $product )
    {
        // add params to Ambrasubs types
        $helper = TiendaHelperBase::getInstance( 'Ambrasubs' );
        $helper->onAfterSaveProducts( $product );
    }

    /**
     * Function to display price with tax base on the configuration
     * @param float $price - item price
     * @param float $tax -  product price
     * @param int $show - to show price with tax
     * @return string
     */
    public static function dispayPriceWithTax( $price = '0', $tax = '0', $show = '0' )
    {
        $txt = '';
        if ( $show && $tax )
        {
            switch( $show )
            {
                case 1: // display tax next to price
                    {
                        $txt .= TiendaHelperBase::currency( $price );
                        $txt .= sprintf( JText::_('COM_TIENDA_INCLUDE_TAX'), TiendaHelperBase::currency( $tax ) );
                        break;
                    }
                case 2: // sum the tax and product price
                    {
                        $txt .= TiendaHelperBase::currency( $price + $tax );
                        break;
                    }
                case 3: // sum the tax and product price (+text that the price includes tax)
                    {
                        $txt .= sprintf( JText::_('COM_TIENDA_SUM_INCLUDE_PRICE_TAX'), TiendaHelperBase::currency( $price + $tax ) );
                        break;
                    }
                case 4: // display both price without and with tax
                    {
                        $txt .= TiendaHelperBase::currency( $price );
                        $txt .= sprintf( JText::_('COM_TIENDA_INCLUDING_TAX'), TiendaHelperBase::currency( $price + $tax ) );
                        break;
                    }
            }
        }
        else
        {
            $txt .= TiendaHelperBase::currency( $price );
        }

        return $txt;
    }

    /**
     * Loads a product by its ID
     * and stores it for later use by the application
     *
     * @param unknown_type $id
     * @param boolean $reset
     * @param boolean $load_eav
     * @return unknown_type
     */
    function load( $id, $reset = true, $load_eav = true )
    {
        if ( empty( self::$products[$id][$load_eav] ) )
        {
            JTable::addIncludePath( trim( JPATH_ADMINISTRATOR . '/components/com_tienda/tables' ) );
            $productTable = JTable::getInstance( 'Products', 'TiendaTable' );
            $productTable->load( $id, $reset, $load_eav );
            self::$products[$id][$load_eav] = $productTable;
        }
        return self::$products[$id][$load_eav];
    }

    /**
     * Guesses the default options (first in the list)
     * Enter description here ...
     * @param unknown_type $attributes
     */
    public static function getDefaultAttributeOptions( $attributes )
    {
        $default = array( );
        foreach ( @$attributes as $attribute )
        {
            JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
            $model = JModel::getInstance( 'ProductAttributeOptions', 'TiendaModel' );
            $model->setState( 'filter_attribute', $attribute->productattribute_id );
            $model->setState( 'order', 'tbl.ordering' );
            	
            $items = $model->getList( );
            	
            if ( count( $items ) )
            {
                $default[$attribute->productattribute_id] = $items[0]->productattributeoption_id;
            }
            else
            {
                $default[$attribute->productattribute_id] = 0;
            }
        }

        return $default;
    }

    /**
     * Get the cart button form for a specific product
     *
     * @param int $product_id 	The id of the product
     * @return html	The add to cart form
     */
    public static function getCartButton( $product_id, $layout = 'product_buy', $values = array( ) )
    {

        if( is_array( $values ) && !count( $values ) )
        {
            $values = JRequest::get( 'request' );
        }
        $html = '';
        $page = JRequest::getVar( 'page', 'product' );

        JLoader::register( "TiendaViewProducts", JPATH_SITE."/components/com_tienda/views/products/view.html.php" );

        $view = new TiendaViewProducts( );
        $model = JModel::getInstance( 'Products', 'TiendaModel' );
        $model->setId( $product_id );
        $model->setState( 'task', 'product_buy' );

        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $helper_product = TiendaHelperBase::getInstance( 'Product' );

        Tienda::load( 'TiendaHelperUser', 'helpers.user' );
        $user_id = JFactory::getUser( )->id;
        $filter_group = TiendaHelperUser::getUserGroup( $user_id, $product_id );
        $model->setState( 'filter_group', $filter_group );

        $row = $model->getItem( false, false );
        if ( $row->product_notforsale || Tienda::getInstance( )->get( 'shop_enabled' ) == '0' )
        {
            return $html;

        }
        // This enable this helper method to be used outside of tienda
        $view->addTemplatePath( JPATH_SITE . DS . 'components' . DS . 'com_tienda' . DS . 'views' . DS . 'products' . DS . 'tmpl' );
        // add extra templates
        $view->addTemplatePath( Tienda::getPath( 'product_buy_templates' ) );
        $view->set( '_controller', 'products' );
        $view->set( '_view', 'products' );
        $view->set( '_doTask', true );
        $view->set( 'hidemenu', true );
        $view->setModel( $model, true );
        $view->setLayout( $layout );
        $view->product_id = $product_id;
        $view->values = $values;
        $filter_category = $model->getState( 'filter_category', JRequest::getInt( 'filter_category', ( int ) @$values['filter_category'] ) );
        $view->filter_category = $filter_category;
        $view->validation = "index.php?option=com_tienda&view=products&task=validate&format=raw";

        $config = Tienda::getInstance( );
        // TODO What about this??
        $show_shipping = $config->get( 'display_prices_with_shipping' );
        if ( $show_shipping )
        {
            $article_link = $config->get( 'article_shipping', '' );
            $shipping_cost_link = JRoute::_( 'index.php?option=com_content&view=article&id=' . $article_link );
            $view->shipping_cost_link = $shipping_cost_link;
        }

        $quantity_min = 1;
        if ( $row->quantity_restriction )
        {
            $quantity_min = $row->quantity_min;
        }

        $invalidQuantity = '0';
        $attributes = array( );
        if ( empty( $values ) )
        {
            $product_qty = $quantity_min;
            // get the default set of attribute_csv
            $default_attributes = $helper_product->getDefaultAttributes( $product_id );
            sort( $default_attributes );
            $attributes_csv = implode( ',', $default_attributes );
            $availableQuantity = $helper_product->getAvailableQuantity( $product_id, $attributes_csv );
            if ( $availableQuantity->product_check_inventory && $product_qty > $availableQuantity->quantity )
            {
                $invalidQuantity = '1';
            }
            $attributes = $default_attributes;
        }
        else
        {
            $product_qty = !empty( $values['product_qty'] ) ? ( int ) $values['product_qty'] : $quantity_min;
            	
            // TODO only display attributes available based on the first selected attribute?
            foreach ( $values as $key => $value )
            {
                if ( substr( $key, 0, 10 ) == 'attribute_' )
                {
                    $attributes[] = $value;
                }
            }
            	
            if( !count( $attributes ) ) // no attributes are selected -> use default
                $attributes = $helper_product->getDefaultAttributes( $product_id );

            sort( $attributes );
            	
            // Add 0 to attributes to include all the root attributes
            //$attributes[] = 0;//remove this one. its causing the getAvailableQuantity to not get quantity because of wrong csv
            	
            // For getting child opts
            $view->selected_opts = json_encode( array_merge( $attributes, array(
                    '0'
            ) ) );
            	
            $attributes_csv = implode( ',', $attributes );
            	
            // Integrity checks on quantity being added
            if ( $product_qty < 0 )
            {
                $product_qty = '1';
            }
            	
            // using a helper file to determine the product's information related to inventory
            $availableQuantity = $helper_product->getAvailableQuantity( $product_id, $attributes_csv );
            if ( $availableQuantity->product_check_inventory && $product_qty > $availableQuantity->quantity )
            {
                $invalidQuantity = '1';
            }
        }

        // adjust the displayed price based on the selected or default attributes
        TiendaHelperProduct::calculateProductAttributeProperty( $row, $attributes, 'price', 'product_weight' );
        $show_tax = $config->get( 'display_prices_with_tax' );
        $show_product = $config->get( 'display_category_cartbuttons' );
        $view->show_tax = $show_tax;

        $row->tax = '0';
        $row->taxtotal = '0';
        if ( $show_tax )
        {
            // finish TiendaHelperUser::getGeoZone -- that's why this isn't working
            Tienda::load( 'TiendaHelperUser', 'helpers.user' );
            $geozones_user = TiendaHelperUser::getGeoZones( JFactory::getUser( )->id );
            if ( empty( $geozones_user ) )
            {
                $geozones = array( Tienda::getInstance( )->get( 'default_tax_geozone' ) );
            }
            else
            {
                $geozones = array();
                foreach( $geozones_user as $value )
                    $geozones[] = $value->geozone_id;
            }
            Tienda::load( 'TiendaHelperTax', 'helpers.tax' );
            $product = new stdClass();
            $product->product_price = $row->price;
            $product->product_id = $product_id;
            $tax = TiendaHelperTax::calculateGeozonesTax( array( $product ), 2, $geozones );
            $row->taxtotal = $tax->tax_total;
            $row->tax = $tax->tax_total;
        }

        $row->_product_quantity = $product_qty;

        if ($page == 'product') {
            $display_cartbutton = Tienda::getInstance( )->get( 'display_product_cartbuttons', '1' );
        }
        else {
            $display_cartbutton = Tienda::getInstance( )->get( 'display_category_cartbuttons', '1' );
        }


        $view->page = $page;
        $view->display_cartbutton = $display_cartbutton;
        $view->availableQuantity = $availableQuantity;
        $view->invalidQuantity = $invalidQuantity;
        $view->item = $row;

        $dispatcher = JDispatcher::getInstance( );

        ob_start( );
        $dispatcher->trigger( 'onDisplayProductAttributeOptions', array(
                $row->product_id
        ) );
        $view->onDisplayProductAttributeOptions = ob_get_contents( );
        ob_end_clean( );

        ob_start( );
        $view->display( );
        $html = ob_get_contents( );
        ob_end_clean( );

        return $html;
    }

    /**
     * Get the share buttons for a specific product
     *
     * @param int $product_id 	The id of the product
     * @return html	The add to product detail view
     */
    public static function getProductShareButtons( $view, $product_id, $layout = 'product_share_buttons' )
    {
        $share_data = new stdClass();
        $share_data->product_id = $product_id;
        if( $view === null ) // if nothing is specified, load products view
            $view = TiendaHelperProduct::getProductViewObject();
        $view->share_data = $share_data;

        $lt = $view->getLayout();
        $view->setLayout( $layout );
        ob_start( );
        echo $view->loadTemplate( null );
        $html = ob_get_contents( );
        ob_end_clean( );
        $view->setLayout( $lt );
        unset( $view->share_data );
        return $html;
    }

    public static function getSocialBookMarkUri( $uri = null )
    {
        if( $uri === null )
            $uri = JFactory::getUri()->toString();

        static $cached_uri = array();
        $type = Tienda::getInstance()->get( 'display_bookmark_uri', 0 );

        switch( $type )
        {
            case 0 : // Long URI
                {
                    return $uri;
                }
            case 1 : // Bit.ly
                {
                    if( !isset( $cached_uri[$type][$uri] ) )
                    {
                        $key = Tienda::getInstance()->get( 'bitly_key', '' );
                        $logn = Tienda::getInstance()->get( 'bitly_login', '' );
                        $link = 'http://api.bit.ly/v3/shorten?apiKey='.$key.'&login='.$login.'&longURL='.urlencode($uri);
                        $c = curl_init();
                        curl_setopt( $c, CURLOPT_RETURNTRANSFER, 1 );
                        curl_setopt( $c, CURLOPT_URL, $link );
                        $request = curl_exec( $c );
                        curl_close( $c );
                        $uri_short = json_decode( $request )->data->url;
                        $cached_uri[$type][$uri] = $uri_short;
                    }
                    return $cached_uri[$type][$uri];
                }
        }
    }

    /*
     * Converts product attributes from CSV format to an array
    *
    * @param $product_id    ID of the product with attributes
    * @param $values_csv    String with product attributes in CSV format
    *
    * @return Array of arrays in format (attribute_id, attribute_value)
    */
    public static function convertAttributesToArray( $product_id, $values_csv )
    {
        JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
        $model = JModel::getInstance( 'ProductAttributes', 'TiendaModel' );
        $model->setState( 'filter_product', $product_id );
        $list = $model->getList();
        $result = array();
        $values = explode( ',', $values_csv );
        for( $i = 0, $c = count( $list ); $i < $c; $i++ )
        {
            if( isset( $values[$i]) )
                $result []= array( $list[$i]->productattribute_id, $values[$i] );
        }
        	
        return $result;
    }

    /*
     * Method to display a selected gallery layout for a specific product
    *
    * @param $product_id Product ID
    * @param $product_name Product name
    * @param $exclude Exclude this image from the gallery
    * @param $layout Layout of the gallery ('product_gallery' by default)
    * @param $values Values from the request (POST array by default)
    *
    * @return HTML of the layout
    */
    public static function getGalleryLayout( $view, $product_id, $product_name = '', $exclude = '', $layout = 'product_gallery', $values = array() )
    {
        if( is_array( $values ) && !count( $values ) )
        {
            $values = JRequest::get( 'post' );
        }
        if( $view === null ) // if nothing is specified, load products view
            $view = TiendaHelperProduct::getProductViewObject();

        $path = TiendaHelperProduct::getGalleryPath( $product_id );
        $images = TiendaHelperProduct::getGalleryImages( $path, array( 'exclude' => $exclude ) );
        $uri = TiendaHelperProduct::getUriFromPath( $path );
        $show_gallery = false;
        if ( !empty( $path ) && !empty( $images ) )
            $show_gallery = true;

        Tienda::load( 'TiendaUrl', 'library.url' );
        $gallery_data = new stdClass();
        $gallery_data->values = $values;
        $gallery_data->show_gallery = $show_gallery;
        $gallery_data->uri = $uri;
        $gallery_data->images = $images;
        $gallery_data->product_name = $product_name;
        $view->gallery_data = $gallery_data;

        $lt = $view->getLayout();
        $view->setLayout( $layout );
        ob_start( );
        echo $view->loadTemplate( null );
        $html = ob_get_contents( );
        ob_end_clean( );
        $view->setLayout( $lt );
        unset( $view->gallery_data );
        return $html;
    }

    public static function getProductViewObject( $model = null, $hidemenu = true, $dotask = true )
    {
        JLoader::register( "TiendaViewProducts", JPATH_SITE."/components/com_tienda/views/products/view.html.php" );
        $view = new TiendaViewProducts( );
        $view->set( '_controller', 'products' );
        $view->set( '_view', 'products' );
        $view->set( '_doTask', $dotask );
        $view->set( 'hidemenu', $hidemenu );

        if( $model === null )
            $model = JModel::getInstance( 'Products', 'TiendaModel' );
        $view->setModel( $model, true );

        return $view;
    }

    public static function calculateProductAttributeProperty( &$product, $attributes, $product_price, $product_weight )
    {
        Tienda::load( 'TiendaQuery', 'library.query' );
        $q = new TiendaQuery();
        $q->select( 'tbl.`productattributeoption_price` , tbl.`productattributeoption_prefix`, tbl.`productattributeoption_id` ' );
        $q->select( 'tbl.`productattributeoption_code`, tbl.`productattributeoption_weight`, tbl.`productattributeoption_prefix_weight`' );
        $q->from( '`#__tienda_productattributeoptions` tbl' );
        $q->join( 'left','`#__tienda_productattributes` atr ON tbl.	productattribute_id = atr.productattribute_id' );
        $q->where( "tbl.productattributeoption_id IN ('".implode( "', '", $attributes )."')" );
        $q->order( 'atr.ordering ASC' );
        $db = JFactory::getDbo();
        $db->setQuery( $q );
        $res = $db->loadObjectList();

        $attributes = array();
        for( $i = 0, $c = count( $res ); $i < $c; $i++ )
        {
            // update product price
            // is not + or -
            if ( $res[$i]->productattributeoption_prefix == '=' )
            {
                $product->$product_price = floatval( $res[$i]->productattributeoption_price );
            }
            else
            {
                $product->$product_price = $product->$product_price + floatval( $res[$i]->productattributeoption_prefix.$res[$i]->productattributeoption_price );
            }

            // update product weight
            if ( $res[$i]->productattributeoption_prefix_weight == '=' )
            {
                $product->$product_weight = floatval( $res[$i]->productattributeoption_weight );
            }
            else
            {
                $product->$product_weight = $product->$product_weight + floatval( $res[$i]->productattributeoption_prefix_weight.$res[$i]->productattributeoption_weight );
            }
            $attributes[] = $res[$i]->productattributeoption_id;
        }

        $product->sku = self::getProductSKU($product, $attributes);
    }

    public static function getProductSKU($product, $attributes_array = array())
    {
        $product_sku = $product->product_sku;

        // Reorder to use the attributes order
        $attributes = array();
        if( !count( $attributes_array ) )
        {
            return $product_sku;
        }

        foreach($attributes_array as $id)
        {
            $table = JTable::getInstance('ProductAttributeOptions', 'TiendaTable');
            $table->load( $id );
            	
            // Load attribute
            $attr_id = $table->productattribute_id;
            	
            $attributes[] = $attr_id;
        }

        // Load list of attributes
        $model = JModel::getInstance('ProductAttributes', 'TiendaModel');
        $model->setState('filter_id', $attributes);
        $model->setState('order', 'ordering');
        $model->setState('direction', 'ASC');
        $list = $model->getList();



        // now that they are in order, generate sku
        foreach($list as $attr)
        {
            foreach($attributes_array as $attrib_id)
            {
                $table = JTable::getInstance('ProductAttributeOptions', 'TiendaTable');
                $table->load( $attrib_id );

                if($table->productattribute_id == $attr->productattribute_id)
                {
                    $product_sku .= $table->productattributeoption_code;
                    continue;
                }
            }
        }

        return $product_sku;
    }

}