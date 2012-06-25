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

Tienda::load( 'TiendaTableEav', 'tables._baseeav' );

class TiendaTableProducts extends TiendaTableEav
{
	function TiendaTableProducts( &$db )
	{
		$tbl_key = 'product_id';
		$tbl_suffix = 'products';
		$this->set( '_suffix', $tbl_suffix );
		$name = 'tienda';
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );
	}
	
	function check( )
	{
		$nullDate = $this->_db->getNullDate( );
		if ( empty( $this->created_date ) || $this->created_date == $nullDate )
		{
			$date = JFactory::getDate( );
			$this->created_date = $date->toMysql( );
		}
		jimport( 'joomla.filter.output' );
		if ( empty( $this->product_alias ) )
		{
			$this->product_alias = $this->product_name;
		}
		$this->product_alias = JFilterOutput::stringURLSafe( $this->product_alias );
		
		$date = JFactory::getDate( );
		$this->modified_date = $date->toMysql( );
		
		return true;
	}
	
	/**
	 * 
	 * @param unknown_type $updateNulls
	 * @return unknown_type
	 */
	function store( $updateNulls = false )
	{
		if ( $return = parent::store( $updateNulls ) )
		{
			if ( empty( $this->_isNew ) )
			{
				// consolidate image gallery paths if necessary            
				$helper = TiendaHelperBase::getInstance( 'Product' );
				if ( $helper->consolidateGalleryImages( $this ) === true )
				{
					JFactory::getApplication( )->enqueueMessage( JText::_('COM_TIENDA_IMAGES_CONSOLIDATED_MESSAGE') );
				}
			}
		}
		return $return;
	}
	
	function load( $oid = null, $reset = true, $load_eav = true )
	{
		if ( $return = parent::load( $oid, $reset, $load_eav ) )
		{
			// consolidate image gallery paths if necessary (SHA1 Images)            
			$helper = TiendaHelperBase::getInstance( 'Product' );
			if ( $helper->consolidateGalleryImages( $this ) === true )
			{
				JFactory::getApplication( )->enqueueMessage( JText::_('COM_TIENDA_IMAGES_CONSOLIDATED_MESSAGE') );
			}
		}
		return $return;
	}
	
	/**
	 * Get the path to the product current Image
	 * @return string $dir
	 */
	
	function getImagePath( $check = true )
	{
		// Check where we should upload the file
		// This is the default one
		$dir = Tienda::getPath( 'products_images' );
		
		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance( );
		
		// is the image path overridden?
		if ( !empty( $this->product_images_path ) && $helper->checkDirectory( $this->product_images_path, $check ) )
		{
			$dir = $this->product_images_path;
		}
		else
		{
			// try with the SKU
			if ( Tienda::getInstance( )->get( 'sha1_images', '0' ) ) // Sha1 images for 32k product invortories
			{		    
				if ( !empty( $this->product_sku ) )
				{
					$subdirs = $this->getSha1Subfolders( $this->product_sku );
					$image_dir = $dir . DS . $subdirs . $this->product_sku . DS;
				}
			}
			else
			{
				$image_dir = $dir . DS . $this->product_sku . DS;
			}
			
			if ( !empty( $this->product_sku ) && $helper->checkDirectory( $image_dir, $check ) )
			{
				$dir = $image_dir;
			}
			else
			{
				if ( Tienda::getInstance( )->get( 'sha1_images', '0' ) )
				{
					$subdirs = $this->getSha1Subfolders( $this->product_id );
					$image_dir = $dir . DS . $subdirs . $this->product_id . DS;
				}
				else
				{
					$image_dir = $dir . DS . $this->product_id . DS;
				}
				
				// try with the product id
				if ( $helper->checkDirectory( $image_dir, $check ) )
				{
					$dir = $image_dir;
				}
			}
		}
		
		return $dir;
	}
	
	// Thanks to http://ryan.ifupdown.com/2008/08/17/warning-mkdir-too-many-links/
	protected function getSha1Subfolders( $string, $separator = DS )
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
	 * Get the URL to the path to images
	 * @return unknown_type
	 */
	function getImageUrl( )
	{
		// Check where we should upload the file
		// This is the default one
		$dir = Tienda::getPath( 'products_images' );
		
		$url = Tienda::getUrl( 'products_images' );
		
		$helper = TiendaHelperBase::getInstance( );
		
		// is the image path overridden?
		if ( !empty( $this->product_images_path ) && $helper->checkDirectory( $this->product_images_path, false ) )
		{
			$url = str_replace(JPATH_SITE.DS, JURI::root(), $this->product_images_path);	
		}
		else
		{
			// try with the SKU
			if ( Tienda::getInstance( )->get( 'sha1_images', '0' ) ) // Sha1 images for 32k product invortories
			{
				if ( !empty( $this->product_sku ) )
				{
					$subdirs = $this->getSha1Subfolders( $this->product_sku, '/' );
					$image_dir = $url . $subdirs . $this->product_sku . '/';
				}
			}
			else
			{
				$image_dir = $url . $this->product_sku . '/';
			}
			
			// try with the SKU
			if ( !empty( $this->product_sku ) )
			{
				$url = $image_dir;
			}
			else
			{
				if ( Tienda::getInstance( )->get( 'sha1_images', '0' ) )
				{
					$subdirs = $this->getSha1Subfolders( $this->product_id, '/' );
					$image_dir = $url . $subdirs . $this->product_id . '/';
				}
				else
				{
					$image_dir = $url . $this->product_id . '/';
				}
				
				$url = $image_dir;
				
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
	function updateOverallRating( $save = false )
	{
		JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
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
		
		if ( $save )
		{
			$this->save( );
		}
	}
	
	/**
	 * Creates a product and its related informations (price, quantity & categories)
	 * The price will be created from the $this->product_price property
	 * The quantity will be created from the $this->product_quantity property
	 * The categories will be created from the $this->product_category property
	 */
	function create( )
	{
		// If this product is already stored, we shouldn't create the product!
		if ( $this->product_id )
		{
			$this->setError( JText::_('COM_TIENDA_YOU_CANNOT_CREATE_AN_ALREADY_EXISTING_PRODUCT') );
			return false;
		}
		
		$product_price = @$this->product_price;
		$product_quantity = @$this->product_quantity;
		$product_categories = @$this->product_category;
		
		if ( !is_array( $product_categories ) )
		{
			$product_categories = array(
				$product_categories
			);
		}
		
		unset( $this->product_price );
		unset( $this->product_quantity );
		unset( $this->product_category );
		
		// Save the product First
		$success = $this->save( );
		
		if ( $success )
		{
			//we dont do the $product_price checking since the product will not show if no entry in the #__tienda_productprices table
			// now the price
			//if ( $product_price )
			//{
				Tienda::load( 'TiendaTableProductPrices', 'tables.productprices' );
				$price = JTable::getInstance( 'ProductPrices', 'TiendaTable' );
				$price->product_id = $this->product_id;
				$price->product_price = $product_price;
				$price->group_id = Tienda::getInstance( )->get( 'default_user_group', '1' );
				$success = $price->save( );
				
				if ( !$success )
				{
					$this->setError( $price->getError( ) );
					return false;
				}
			//}
			
			// now the quantities
			if ( $product_quantity )
			{
				Tienda::load( 'TiendaTableProductQuantities', 'tables.productquantities' );
				$quantity = JTable::getInstance( 'ProductQuantities', 'TiendaTable' );
				$quantity->product_id = $this->product_id;
				$quantity->quantity = $product_quantity;
				$success = $quantity->save( );
				
				if ( !$success )
				{
					$this->setError( $quantity->getError( ) );
					return false;
				}
			}
			
			// now the categories
			if ( $product_categories )
			{
				foreach ( $product_categories as $product_category )
				{
					// This is probably not the best way to do it
					// Numeric = id, string = category name
					if ( !is_numeric( $product_category ) )
					{
						// check for existance
						JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
						$model = JModel::getInstance( 'Categories', 'TiendaModel' );
						$model->setState( 'filter_name', $product_category );
						$matches = $model->getList( );
						$matched = false;
						
						if ( $matches )
						{
							foreach ( $matches as $match )
							{
								// is a perfect match?
								if ( trim( strtolower( $product_category ) ) == trim( strtolower( $match->category_name ) ) )
								{
									$product_category = $match->category_id;
									$matched = true;
								}
							}
						}
						
						// Not matched, create category
						if ( !$matched )
						{
							Tienda::load( 'TiendaTableCategories', 'tables.categories' );
							$category = JTable::getInstance( 'Categories', 'TiendaTable' );
							$category->category_name = $product_category;
							$category->parent_id = 1;
							$category->category_enabled = 1;
							$category->save( );
							
							$product_category = $category->category_id;
						}
						
					}
					
					// save xref in every case
					Tienda::load( 'TiendaTableProductCategories', 'tables.productcategories' );
					$xref = JTable::getInstance( 'ProductCategories', 'TiendaTable' );
					$xref->product_id = $this->product_id;
					$xref->category_id = $product_category;
					$xref->save( );
				}
			}
		}
		else
		{
			return false;
		}
		
		return true;
	}
	
	/**
	 * Updates a product and its related informations (price, quantity & categories)
	 * The price will be created from the $this->product_price property
	 * The quantity will be created from the $this->product_quantity property
	 * The categories will be created from the $this->product_category property
	 */
	function update( )
	{
		// If this product is already stored, we shouldn't create the product!
		if ( !$this->product_id )
		{
			$this->setError( JText::_('COM_TIENDA_YOU_CANNOT_UPDATE_A_NON_EXISTING_PRODUCT') );
			return false;
		}
		
		$product_price = @$this->product_price;
		$product_quantity = @$this->product_quantity;
		$product_categories = @$this->product_category;
		
		if ( !is_array( $product_categories ) )
		{
			$product_categories = array(
				$product_categories
			);
		}
		
		unset( $this->product_price );
		unset( $this->product_quantity );
		unset( $this->product_category );
		
		// Save the product First
		$success = $this->save( );
		
		if ( $success )
		{
			// now the price
			if ( $product_price )
			{
				// Load the default price
				Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
				$prices = TiendaHelperProduct::getPrices( $this->product_id );
				

				if ( count( $prices ) )
				{
					$price_id = $prices[0]->product_price_id;
				}
				else
				{
					$price_id = 0;
				}
				
				Tienda::load( 'TiendaTableProductPrices', 'tables.productprices' );
				$price = JTable::getInstance( 'ProductPrices', 'TiendaTable' );
				// load the price if it does exist
				if ( $price_id )
				{
					$price->load( $price_id );
				}
				// else just save it as a new price
				$price->product_id = $this->product_id;
				$price->product_price = $product_price;
				$price->group_id = Tienda::getInstance( )->get( 'default_user_group', '1' );
				$success = $price->save( );
				
				if ( !$success )
				{
					$this->setError( $price->getError( ) );
					return false;
				}
			}
			
			// now the quantities
			if ( $product_quantity )
			{
				// Load the default quantity
				Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
				$quantities = TiendaHelperProduct::getProductQuantities( $this->product_id );
				
				if ( count( $quantities ) )
				{
					$quantity_id = $quantities[0]->productquantity_id;
				}
				else
				{
					$quantity_id = 0;
				}
				
				Tienda::load( 'TiendaTableProductQuantities', 'tables.productquantities' );
				$quantity = JTable::getInstance( 'ProductQuantities', 'TiendaTable' );
				// load the quantity if it does exist
				if ( $quantity_id )
				{
					$quantity->load( $quantity_id );
				}
				// else just save it as a new quantity
				$quantity->product_id = $this->product_id;
				$quantity->quantity = $product_quantity;
				$success = $quantity->save( );
				
				if ( !$success )
				{
					$this->setError( $quantity->getError( ) );
					return false;
				}
			}
			
			// now the categories
			if ( $product_categories )
			{
				foreach ( $product_categories as $product_category )
				{
					// This is probably not the best way to do it
					// Numeric = id, string = category name
					if ( !is_numeric( $product_category ) )
					{
						// check for existance
						JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
						$model = JModel::getInstance( 'Categories', 'TiendaModel' );
						$model->setState( 'filter_name', $product_category );
						$matches = $model->getList( );
						$matched = false;
						
						if ( $matches )
						{
							foreach ( $matches as $match )
							{
								// is a perfect match?
								if ( strtolower( $product_category ) == strtolower( $match->category_name ) )
								{
									$product_category = $match->category_id;
									$matched = true;
								}
							}
						}
						
						// Not matched, create category
						if ( !$matched )
						{
							Tienda::load( 'TiendaTableCategories', 'tables.categories' );
							$category = JTable::getInstance( 'Categories', 'TiendaTable' );
							$category->category_name = $product_category;
							$category->parent_id = 1;
							$category->category_enabled = 1;
							$category->save( );
							
							$product_category = $category->category_id;
						}
						
					}
					
					// save xref in every case
					Tienda::load( 'TiendaTableProductCategories', 'tables.productcategories' );
					$xref = JTable::getInstance( 'ProductCategories', 'TiendaTable' );
					$xref->product_id = $this->product_id;
					$xref->category_id = $product_category;
					$xref->save( );
				}
			}
		}
		else
		{
			return false;
		}
		
		return true;
	}
}
