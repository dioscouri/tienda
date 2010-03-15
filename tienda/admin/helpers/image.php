<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Daniele Rosario
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2010 Daniele Rosario. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

JLoader::import( 'com_tienda.helpers._base', JPATH_ADMINISTRATOR.DS.'components' );

class TiendaHelperImage extends TiendaHelperBase
{
	// Default Dimensions for the images
	var $product_img_height 		= 0;
	var $product_img_width 			= 0;
	var $category_img_height 		= 0;
	var $category_img_width			= 0;
	var $manufacturer_img_width		= 0;
	var $manufacturer_img_height	= 0;
	
	// Default Paths for the images
	var $product_img_path 			= '';
	var $category_img_path			= '';
	var $manufacturer_img_path 		= '';
	
	// Default Paths for the thumbs
	var $product_thumb_path 			= '';
	var $category_thumb_path			= '';
	var $manufacturer_thumb_path 		= '';
	
	
	/**
	 * Protected! Use the getInstance
	 */ 
	protected function TiendaHelperImage()
	{
		// Parent Helper Construction
		parent::__construct();
		
		$config = TiendaConfig::getInstance();
		
		// Load default Parameters
		$this->product_img_height 			= $config->get('product_img_height');
		$this->product_img_width 			= $config->get('product_img_width');
		$this->category_img_height 			= $config->get('category_img_height');
		$this->category_img_width			= $config->get('category_img_width');
		$this->manufacturer_img_width		= $config->get('manufacturer_img_width');
		$this->manufacturer_img_height		= $config->get('manufacturer_img_height');
		$this->product_img_path 			= Tienda::getPath('products_images');
		$this->category_img_path			= Tienda::getPath('categories_images');
		$this->manufacturer_img_path 		= Tienda::getPath('manufacturers_images');
		$this->product_thumb_path 			= Tienda::getPath('products_thumbs');
		$this->category_thumb_path			= Tienda::getPath('categories_thumbs');
		$this->manufacturer_thumb_path 		= Tienda::getPath('manufacturers_thumbs');
	}
	
	/**
	 * Resize Image
	 * 
	 * @param name	string	filename of the image
	 * @param type	string	what kind of image: product, category
	 * @param options	array	array of options: width, height, path, thumb_path
	 * @return thumb full path
	 */
	function resize($name, $type = 'product', $options = array()){
		
		// Check File presence
		if(!JFile::exists($name)){
			return false;
		}
		
		JImport( 'com_tienda.library.image', JPATH_ADMINISTRATOR . 'components' );
		$img = new TiendaImage($name);
		
		$types = array('product', 'category', 'manufacturer');
		if(!in_array($type, $types))
			$type = 'product';

		return $this->resizeImage(&$img, $type, $options);
	}
	
    /**
	 * Resize Image
	 * 
	 * @param image	string	filename of the image
	 * @param type	string	what kind of image: product, category
	 * @param options	array	array of options: width, height, thumb_path
	 * @return thumb full path
	 */
	function resizeImage( &$img, $type = 'product', $options = array() )
	{

		$types = array('product', 'category', 'manufacturer');
		if(!in_array($type, $types))
			$type = 'product';

		// Code less!
		$thumb_path = $img->getDirectory().DS.'thumbs';
		$img_width = $type.'_img_width';
		$img_height = $type.'_img_height';
		
		$img->load();
		
		// Default width or options width?
		if(!empty($options['width']) && is_numeric($options['width']))
			$width = $options['width'];
		else
			$width = $this->$img_width;
		
		// Default height or options height?
		if(!empty($options['height']) && is_numeric($options['height']))
			$height = $options['height'];
		else	
			$height= $this->$img_height;
		
		// Default thumb path or options thumb path?
		if(!empty($options['thumb_path']) && is_numeric($options['thumb_path']))
			$dest_dir = $options['thumb_path'];
		else	
			$dest_dir = $thumb_path;
			
		$this->checkDirectory($dest_dir);

		if($width >= $height)
			$img->resizeToWidth( $width );
		else
			$img->resizeToHeight( $height );
			
		$dest_path = $dest_dir.DS.$img->getPhysicalName();
		
		$img->save($dest_path);
		
		return $dest_path;
	}
}