<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

Tienda::load( 'TiendaTable', 'tables._base' );

class TiendaTableProducts extends TiendaTable 
{
	function TiendaTableProducts ( &$db ) 
	{
		$tbl_key 	= 'product_id';
		$tbl_suffix = 'products';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'tienda';
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );	
	}
	
	function check()
	{
		$nullDate	= $this->_db->getNullDate();
		if (empty($this->created_date) || $this->created_date == $nullDate)
		{
			$date = JFactory::getDate();
			$this->created_date = $date->toMysql();
		}
        jimport( 'joomla.filter.output' );
        if (empty($this->product_alias)) 
        {
            $this->product_alias = $this->product_name;
        }
        $this->product_alias = JFilterOutput::stringURLSafe($this->product_alias);
        		
		$date = JFactory::getDate();
		$this->modified_date = $date->toMysql();
		
		return true;
	}
	
    /**
     * 
     * @param unknown_type $updateNulls
     * @return unknown_type
     */
    function store( $updateNulls=false )
    {
        if ( $return = parent::store( $updateNulls ))
        {
            if (empty($this->_isNew))
            {
                // consolidate image gallery paths if necessary            
                Tienda::load( "TiendaHelperBase", 'helpers._base' );
                $helper = TiendaHelperBase::getInstance('Product');
                if ($helper->consolidateGalleryImages( $this->product_id ) === true )
                {
                    JFactory::getApplication()->enqueueMessage( JText::_( "Images Consolidated Message" ) );
                }
            }
        }
        return $return;
    }
	
	/**
	 * Get the path to the product current Image
	 * @return string $dir
	 */
	
	function getImagePath($check = true)
	{
		// Check where we should upload the file
		// This is the default one
		$dir = Tienda::getPath( 'products_images' );
		
		$helper = TiendaHelperBase::getInstance();
		
		// is the image path overridden?
		if (!empty($this->product_images_path) && $helper->checkDirectory($this->product_images_path, $check))
		{
			$dir = $this->product_images_path;
		} 
            else
		{
			// try with the SKU
			if (!empty($this->product_sku) && $helper->checkDirectory($dir.DS.$this->product_sku, $check))
			{
				$dir = $dir.DS.$this->product_sku.DS;
			} 
                else
			{
				// try with the product id
				if($helper->checkDirectory($dir.DS.$this->product_id, $check))
				{
					$dir = $dir.DS.$this->product_id.DS;
				}
			}
		}
		
		return $dir;
	}
	
	/**
	 * Get the URL to the path to images
	 * @return unknown_type
	 */
	function getImageUrl()
	{
		// Check where we should upload the file
		// This is the default one
		$dir = Tienda::getPath( 'products_images' );
		
		$url = Tienda::getUrl('products_images');
		
		$helper = TiendaHelperBase::getInstance();
		
		// is the image path overridden?
		if (!empty($this->product_images_path) && $helper->checkDirectory($this->product_images_path, false))
		{
			//$url = $this->product_images_path; ????????
			$url = "";
		} 
		    else
		{
			// try with the SKU
			if (!empty($this->product_sku) && $helper->checkDirectory($dir.DS.$this->product_sku, false))
			{
				$url = $url.$this->product_sku."/";
			} 
			    else
			{
				// try with the product id
				if ($helper->checkDirectory($dir.DS.$this->product_id, false))
				{
					$url = $url.$this->product_id."/";
				}
			}
		}
		
		return $url;
	}

}
