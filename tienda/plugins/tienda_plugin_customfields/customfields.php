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
	
	function __construct( &$subject, $config )
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
			$fields = $this->getCustomFields( 'products', $row->product_id, true, array( 0, 1 ) );
			
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
		$type = 'orderitems';
		$id = @$item->orderitem_id;
		if( isset( $item->cart_id) )
		{
			$type = 'carts';
			$id = $item->cart_id;
		}
			
		$fields = $this->getCustomFields('products', $item->product_id, true, 2 );
		$return = array();
		
		if(count($fields))
		{
			foreach($fields as $f)
			{
				$k = $f['attribute']->eavattribute_alias;
				$return[$k] = TiendaHelperEav::getAttributeValue($f['attribute'], $type, $id );
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
		$fields = $this->getCustomFields( 'products', $item->product_id, true, 2 );
		
		// If there are any extra fields, show them as an extra tab
		if ( count( $fields ) )
		{
			$field_show = array();
			Tienda::load( 'TiendaHelperEav', 'helpers.eav' );
			foreach ( $fields as $f )
			{
				$k = $f['attribute']->eavattribute_alias;
				$f['value'] = $item->$k;
					
				if(empty($f['value']))
				{
					$f['value'] = TiendaHelperEav::getAttributeValue($f['attribute'], 'orderitems', $item->orderitem_id );
				}
				$f['value'] = TiendaHelperEav::showValue( $f['attribute'], $f['value'] );
				
				$field_show[] = $f;
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
		$fields = $this->getCustomFields( 'products', $item->product_id, true, 2 );
		
		// If there are any extra fields, show them as an extra tab
		if ( count( $fields ) )
		{
			foreach($fields as $f)
			{
				$k = $f['attribute']->eavattribute_alias;
				if(empty($values[$k]) && $f['attribute']->eavattribute_required)
				{
					$error = new JObject();
					$error->error = '1';
					$error->message = JText::sprintf("COM_TIENDA_ATTRIBUTE_IS_REQUIRED", $f['attribute']->eavattribute_label);
					return $error;
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
		$fields = $this->getCustomFields( 'products', $item->product_id, true, 2 );
		
		// If there are any extra fields, show them as an extra tab
		if ( count( $fields ) )
		{
			$field_show = array();
			Tienda::load( 'TiendaHelperEav', 'helpers.eav' );
			foreach ( $fields as $f )
			{
				$k = $f['attribute']->eavattribute_alias;
				$f['value'] = TiendaHelperEav::getAttributeValue($f['attribute'], 'carts', $item->cart_id);
				$field_show[] = $f;
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
		$fields = $this->getCustomFields( 'products', $item->product_id, false, 2 );
		
		// If there are any extra fields, show them as an extra tab
		if ( count( $fields ) )
		{
			$field_show = array();
			Tienda::load( 'TiendaHelperEav', 'helpers.eav' );
			foreach ( $fields as $f )
			{
				// User editable?
					$k = $f['attribute']->eavattribute_alias;
					if( $f['attribute']->eavattribute_type == 'datetime' && !strlen( $item->$k ) )
						$item->$k = JFactory::getDbo()->getNullDate();
					$field_show[$k] = $item->$k;
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
		$field_show = $this->getCustomFields( 'products', $product_id, true, 1 );
		
		if(count($field_show))
		{
			$vars->fields = $field_show;
			$html = $this->_getLayout( 'product_site', $vars );
			echo $html;
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
		$field_show = $this->getCustomFields( 'products', $product_id, true, 2 );

		if(count($field_show))
		{
			$vars->fields = $field_show;
			$html = $this->_getLayout( 'product_site_form', $vars );
			echo $html;
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
		$fields = $this->getCustomFields( 'products', $item->product_id, false, 2 );
		
		$field_save = array();
		
		// If there are any extra fields, save them
		if ( count( $fields ) )
		{
			
			foreach($fields as $f)
			{
				$key = $f['attribute']->eavattribute_alias;
				$field_save[$key] = $values[$key];	
			}
		}
		return $field_save;
	}
	
	/**
	 * Get the custom fields for the given entity
	 * @param string $entity
	 * @param int $id
	 * @param bool $cache_values If the values should be cached in RAV helper
	 */
	function getCustomFields( $entity, $id, $cache_values = true, $editable_by = '' )
	{
		Tienda::load( 'TiendaModelEavAttributes', 'models.eavattributes' );
		Tienda::load( 'TiendaHelperEav', 'helpers.eav' );
		
		$eavs = TiendaHelperEav::getAttributes( $entity, $id, false, $editable_by );
		
		$fields = array( );
		foreach ( @$eavs as $eav )
		{
			$key = $eav->eavattribute_alias;

			$value = TiendaHelperEav::getAttributeValue( $eav, $entity, $id, false, $cache_values );
			
			$fields[] = array(
				'attribute' => $eav, 'value' => $value
			);
		}
		
		return $fields;
	}

	function onRemoveFromCart( $item )
	{
		Tienda::load( 'TiendaHelperEav', 'helpers.eav' );
		TiendaHelperEav::deleteEavValuesFromEntity( 'products', $item->product_id, 'carts', $item->cart_id );
	}
}
