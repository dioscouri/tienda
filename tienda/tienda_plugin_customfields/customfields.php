<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Daniele Rosario
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

Tienda::load( 'TiendaPluginBase', 'library.plugins._base' );

class plgTiendaCustomFields extends TiendaPluginBase
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
	var $_element = 'customfields';
	
	function plgTiendaCustomFields( &$subject, $config )
	{
		parent::__construct( $subject, $config );
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}
	
	/**
	 * adds a tab with Extra Fields on products if needed
	 * Enter description here ...
	 * @param unknown_type $tabs
	 * @param unknown_type $row
	 */
	function onAfterDisplayProductFormTabs( $tabs, $row )
	{
		if(@$row->product_id)
		{
			$vars = new JObject( );
			$vars->tabs = $tabs;
			$vars->row = $row;
			
			// Get extra fields for products
			$fields = $this->getCustomFields( 'products', $row->product_id );
			
			// If there are any extra fields, show them as an extra tab
			if ( count( $fields ) )
			{
				$vars->fields = $fields;
				$html = $this->_getLayout( 'product', $vars );
				echo $html;
			}
		}
	}
	
	function onGetAdditionalOrderitemKeyValues( $item )
	{
		$fields = $this->getCustomFields('products', $item->product_id);
		
		$return = array();
		
		if(count($fields))
		{
			foreach($fields as $f)
			{
				$k = $f['attribute']->eavattribute_alias;
				$return[$k] = TiendaHelperEav::getAttributeValue($f['attribute'], 'carts', $item->cart_id);
			}
		}
		
		return $return;
	}
	
	/**
	 * Adds eav details if the user has entered them 
	 * in the order view
	 *
	 * @param unknown_type $i
	 * @param unknown_type $item
	 */
	function onDisplayOrderItem( $i, $item )
	{
		// Get extra fields for products
		$fields = $this->getCustomFields( 'products', $item->product_id );
		
		// If there are any extra fields, show them as an extra tab
		if ( count( $fields ) )
		{
			$field_show = array();
			Tienda::load( 'TiendaHelperEav', 'helpers.eav' );
			foreach ( $fields as $f )
			{
				// User editable?
				if ( $f['attribute']->editable_by == 2 )
				{
					$k = $f['attribute']->eavattribute_alias;
					$f['value'] = $item->$k;
					
					if(empty($f['value']))
					{
						$f['value'] = TiendaHelperEav::getAttributeValue($f['attribute'], 'orderitems', $item->orderitem_id);
					}
					
					$field_show[] = $f;
				}
			}
			
			if(count($field_show))
			{
				$vars->fields = $field_show;
				$html = $this->_getLayout( 'product_order', $vars );
				echo $html;
			}
		}
	}
	
	/**
	 * Validation
	 * 
	 * @param unknown_type $item
	 * @param unknown_type $values
	 */
	function onValidateAddToCart($item, $values)
	{
		// Get extra fields for products
		$fields = $this->getCustomFields( 'products', $item->product_id );
		
		// If there are any extra fields, show them as an extra tab
		if ( count( $fields ) )
		{
			foreach($fields as $f)
			{
				// User Editable
				if($f['attribute']->editable_by == 2)
				{
					$k = $f['attribute']->eavattribute_alias;
					if(empty($values[$k]) && $f['attribute']->eavattribute_required)
					{
						$error = new JObject();
						$error->error = '1';
						$error->message = JText::_($f['attribute']->eavattribute_label .' is required');
						return $error;
					}
					
				}
			}
		}
		
		return new JObject();
	}
	
	/**
	 * Adds eav details if the user has entered them 
	 * in the order view
	 *
	 * @param unknown_type $i
	 * @param unknown_type $item
	 */
	function onDisplayCartItem( $i, $item )
	{
		// Get extra fields for products
		$fields = $this->getCustomFields( 'products', $item->product_id );
		
		// If there are any extra fields, show them as an extra tab
		if ( count( $fields ) )
		{
			$field_show = array();
			Tienda::load( 'TiendaHelperEav', 'helpers.eav' );
			foreach ( $fields as $f )
			{
				// User editable?
				if ( $f['attribute']->editable_by == 2 )
				{
					$k = $f['attribute']->eavattribute_alias;
					$f['value'] = TiendaHelperEav::getAttributeValue($f['attribute'], 'carts', $item->cart_id);
					$field_show[] = $f;
				}
			}
			
			if(count($field_show))
			{
				$vars->fields = $field_show;
				$html = $this->_getLayout( 'product_cart', $vars );
				echo $html;
			}
		}
	}
	
	/**
	 * Event to allow plugins to add keys to the loading of cart items
	 * to make the cartitem also unique based on extra carts column(s).
	 */
    function onGetAdditionalCartKeyValues($item, $posted_values, $index)
    {
    	// Get extra fields for products
		$fields = $this->getCustomFields( 'products', $item->product_id );
		
		// If there are any extra fields, show them as an extra tab
		if ( count( $fields ) )
		{
			$field_show = array();
			Tienda::load( 'TiendaHelperEav', 'helpers.eav' );
			foreach ( $fields as $f )
			{
				// User editable?
				if ( $f['attribute']->editable_by == 2 )
				{
					$k = $f['attribute']->eavattribute_alias;
					$field_show[$k] = $item->$k;
				}
			}

			if(count($field_show))
			{
				return $field_show;
			}
		}
		
		return array();
    }
	
	/**
	 * Displays the custom fields on the site product view
	 * @param int $product_id
	 */
	function onAfterDisplayProduct( $product_id )
	{
		$vars = new JObject( );
		
		// Get extra fields for products
		$fields = $this->getCustomFields( 'products', $product_id );
		
		// If there are any extra fields, show them
		if ( count( $fields ) )
		{
			$field_show = array();
			foreach($fields as $f)
			{
				// Admin Editable => show only as info
				if($f['attribute']->editable_by == 1)
				{
					$field_show[] = $f;	
				}
				
			}
			
			if(count($field_show))
			{
				$vars->fields = $field_show;
				$html = $this->_getLayout( 'product_site', $vars );
				echo $html;
			}
		}
	}
	
	/**
	 * Displays the user editable custom fields on the site product view
	 * @param int $product_id
	 */
	function onDisplayProductAttributeOptions( $product_id )
	{
		$vars = new JObject( );
		
		// Get extra fields for products
		$fields = $this->getCustomFields( 'products', $product_id );
		
		// If there are any extra fields, show them
		if ( count( $fields ) )
		{
			$field_show = array();
			foreach($fields as $f)
			{
				// User Editable => show as field
				if($f['attribute']->editable_by == 2)
				{
					$field_show[] = $f;	
				}
				
			}
			
			if(count($field_show))
			{
				$vars->fields = $field_show;
				$html = $this->_getLayout( 'product_site_form', $vars );
				echo $html;
			}
		}
	}
	
	/**
	 * Saves the extra info in the cart item
	 * 
	 * @param unknown_type $item
	 * @param unknown_type $values
	 * @param unknown_type $files
	 */
	function onAfterCreateItemForAddToCart( $item, $values, $files )
	{
		// Get extra fields for products
		$fields = $this->getCustomFields( 'products', $item->product_id );
		
		$field_save = array();
		
		// If there are any extra fields, save them
		if ( count( $fields ) )
		{
			
			foreach($fields as $f)
			{
				// User Editable => show as field
				if($f['attribute']->editable_by == 2)
				{
					$key = $f['attribute']->eavattribute_alias;
					$field_save[$key] = $values[$key];	
				}
				
			}
		}
		
		return $field_save;
	}
	
	/**
	 * Get the custom fields for the given entity
	 * @param string $entity
	 * @param int $id
	 */
	function getCustomFields( $entity, $id )
	{
		Tienda::load( 'TiendaModelEavAttributes', 'models.eavattributes' );
		Tienda::load( 'TiendaHelperEav', 'helpers.eav' );
		
		$eavs = TiendaHelperEav::getAttributes( $entity, $id );
		
		$fields = array( );
		foreach ( @$eavs as $eav )
		{
			$key = $eav->eavattribute_alias;
			
			$value = TiendaHelperEav::getAttributeValue( $eav, $entity, $id );
			
			$fields[] = array(
				'attribute' => $eav, 'value' => $value
			);
		}
		
		return $fields;
	}
	
}
