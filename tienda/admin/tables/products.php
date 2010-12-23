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

	/**
	 * Recalculates the product's overall rating
	 * 
	 * @param $save    boolean
	 * @return unknown_type
	 */
	function updateOverallRating( $save=false )
	{
	    JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'ProductComments', 'TiendaModel' );
        $model->setState( 'filter_product', $this->product_id );
        $model->setState( 'filter_enabled', '1' );
        
        // get the count of all enabled comments             
        $count = $model->getResult( true );
        
        // get the sum product rating of all enabled comments
        $model->setState( 'select', 'SUM(productcomment_rating)' );
        $sum = $model->getResult( true );
        
        // get the avg product rating of all enabled comments
        $avg = $count ? $sum / $count : 0; 
        
        // update the product row
        $this->product_rating = $avg; 
        $this->product_comments = $count;

        if ($save)
        {
            $this->save();
        }
	}
	
	/**
	 * Creates a product and its related informations (price, quantity
	 * The price will be created from the $this->product_price property
	 * The quantity will be created from the $this->product_quantity property
	 */
	function create()
	{
		// If this product is already stored, we shouldn't create the product!
		if($this->product_id)
		{
			$this->setError( JText::_('You cannot create an already existing product') );
			return false;
		}
		
		$product_price = @$this->product_price;
		$product_quantity = @$this->product_quantity;
		$product_category = @$this->product_category;
		
		unset($this->product_price);
		unset($this->product_quantity);
		unset($this->product_category);
		
		// Save the product First
		$success = $this->save();
		
		if($success)
		{
			// now the price
			if($product_price)
			{
				$price = JTable::getInstance('ProductPrices', 'TiendaTable');
				$price->product_id = $this->product_id;
				$price->product_price = $product_price;
				$price->group_id = TiendaConfig::getInstance()->get('default_user_group', '1');
				$success = $price->save();
				
				if(!$success)
				{
					$this->setError($price->getError());
					return false;
				}
			}
			
			// now the quantities
			if($product_quantity)
			{
				$quantity = JTable::getInstance('ProductQuantities', 'TiendaTable');
				$quantity->product_id = $this->product_id;
				$quantity->quantity = $product_quantity;
				$success = $quantity->save();
				
				if(!$success)
				{
					$this->setError($quantity->getError());
					return false;
				}
			}
			
			// now the category
			if($product_category)
			{
						// This is probably not the best way to do it
		            	// Numeric = id, string = category name
		            	if(!is_numeric($product_category))
		            	{
		            	 	// check for existance
		            	 	JModel::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models');
		            		$model = JModel::getInstance('Categories', 'TiendaModel');
		            		$model->setState('filter_name', $product_category);
		            		$matches = $model->getList();
		            		$matched = false;
		            		
		            		if($matches)
		            		{
			            		foreach($matches as $match)
			            		{
			            			// is a perfect match?
			            			if(strtolower($product_category) == strtolower($match->category_name))
			            			{
			            				$product_category = $match->category_id;
			            				$matched = true;
			            			}	
			            		}
		            		}
		            		
		            		// Not matched, create category
		            		if(!$matched)
		            		{
		            			$category = JTable::getInstance('Categories', 'TiendaTable');
		            			$category->category_name = $product_category;
		            			$category->parent_id = 1;
		            			$category->category_enabled = 1;
		            			$category->save();
		            			
		            			$product_category = $category->category_id;
		            		}
		            		
		            	}
		            	
		            	// save xref in every case
                        $xref = JTable::getInstance( 'ProductCategories', 'TiendaTable' );
                        $xref->product_id = $this->product_id;
                        $xref->category_id = $product_category;
                        $xref->save();
		            }
		}
		else
		{
			return false;
		}
		
		return true;
	}
}
