<?php
/**
 * @version    1.5
 * @package    Tienda
 * @author     Dioscouri Design
 * @link     http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

Tienda::load( 'TiendaTableEav', 'tables._baseeav' );

class TiendaTableWishlists extends TiendaTableEav 
{
    /**
     * @param $db
     * @return unknown_type
     */
    function TiendaTableWishlists ( &$db ) 
    {
        $keynames = array();
        $keynames['user_id']    = 'user_id';
        $keynames['product_id'] = 'product_id';
        $keynames['product_attributes'] = 'product_attributes';

        // load the plugins (when loading this table outside of tienda, this is necessary)
        JPluginHelper::importPlugin( 'tienda' );
        
        //trigger: onGetAdditionalCartKeys
        $dispatcher = JDispatcher::getInstance();
        $results = $dispatcher->trigger( "onGetAdditionalCartKeys");
        if (!empty($results))
        {
        	foreach($results as $additionalKeys)
        	{
	        	foreach($additionalKeys as $key=>$value)
	        	{
					$keynames[$key] = $value;
		        }
        	}
		}
        
        $this->setKeyNames( $keynames );
    	
        $tbl_key      = 'wishlist_id';
        $tbl_suffix   = 'wishlists';
        $name         = 'tienda';
        
        $this->set( '_tbl_key', $tbl_key );
        $this->set( '_suffix', $tbl_suffix );
        
        $this->_linked_table = 'products';
        $this->_linked_table_key_name = 'product_id';
        
        parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );    
    }
    
    function check()
    {        
        if (empty($this->user_id))
        {
            $this->setError( JText::_('COM_TIENDA_USER_REQUIRED') );
            return false;
        }
        if (empty($this->product_id))
        {
            $this->setError( JText::_('COM_TIENDA_PRODUCT_REQUIRED') );
            return false;
        }
        
        // be sure that product_attributes is sorted numerically
        if ($product_attributes = explode( ',', $this->product_attributes ))
        {
            sort($product_attributes);
            $this->product_attributes = implode(',', $product_attributes);
        }
        
        return true;
    }
    
		/**
		 * Loads a row from the database and binds the fields to the object properties
		 * If $load_eav is true, binds also the eav fields linked to this entity
		 *
		 * @access	public
		 * @param	mixed	Optional primary key.  If not specifed, the value of current key is used
		 * @param	bool	reset the object values?
		 * @param	bool	load the eav values for this object
		 *
		 * @return	boolean	True if successful
		 */
		function load( $oid=null, $reset=true, $load_eav = true )
		{
  		$this->_linked_table_key = $this->product_id;
  		return parent::load( $oid, $reset, $load_eav );
    }
    
    /**
     * (non-PHPdoc)
     * @see tienda/admin/tables/TiendaTable#delete($oid)
     */
    function delete( $oid='' )
    {
        if (empty($oid))
        {
            // if empty, use the values of the current keys
            $keynames = $this->getKeyNames();
            foreach ($keynames as $key=>$value)
            {
                $oid[$key] = $this->$key; 
            }
            if (empty($oid))
            {
                // if still empty, fail
                $this->setError( JText::_('COM_TIENDA_CANNOT_DELETE_WITH_EMPTY_KEY') );
                return false;
            }
        }
        
        if (!is_array($oid))
        {
            $keyName = $this->getKeyName();
            $arr = array();
            $arr[$keyName] = $oid; 
            $oid = $arr;
        }

        $dispatcher = JDispatcher::getInstance();
        $before = $dispatcher->trigger( 'onBeforeDelete'.$this->get('_suffix'), array( $this, $oid ) );
        if (in_array(false, $before, true))
        {
            return false;
        }
        
        $db = $this->getDBO();
        
        // initialize the query
        $query = new TiendaQuery();
        $query->delete();
        $query->from( $this->getTableName() );
        
        foreach ($oid as $key=>$value)
        {
            // Check that $key is field in table
            if ( !in_array( $key, array_keys( $this->getProperties() ) ) )
            {
                $this->setError( get_class( $this ).' does not have the field '.$key );
                return false;
            }
            // add the key=>value pair to the query
            $value = $db->Quote( $db->getEscaped( trim( strtolower( $value ) ) ) );
            $query->where( $key.' = '.$value);
        }

        $db->setQuery( (string) $query );

        if ($db->query())
        {
            $dispatcher = JDispatcher::getInstance();
            $dispatcher->trigger( 'onAfterDelete'.$this->get('_suffix'), array( $this, $oid ) );
            return true;
        }
        else
        {
            $this->setError($db->getErrorMsg());
            return false;
        }
    }
    
	function store($updateNulls = false)
	{
		$this->_linked_table_key = $this->product_id;
		return parent::store($updateNulls);
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $values
	 * @param unknown_type $files
	 * @return return_type
	 */
	function addtocart( $values=array(), $files=array() )
	{
		// create cart object out of item properties
		$item = new JObject;
		$item->user_id = $this->user_id;
		$item->product_id = ( int ) $this->product_id;
		$item->product_qty = !empty($this->product_quantity) ? $this->product_quantity : '1';
		$item->product_attributes = $this->product_attributes;
		$item->vendor_id = $this->vendor_id;
		$item->cartitem_params = $this->wishlistitem_params;
		
		// onAfterCreateItemForAddToCart: plugin can add values to the item before it is being validated /added
		// once the extra field(s) have been set, they will get automatically saved
		$dispatcher = &JDispatcher::getInstance( );
		$results = $dispatcher->trigger( "onAfterCreateItemForAddToCart", array( $item, $values, $files ) );
		foreach ( $results as $result )
		{
			foreach ( $result as $key => $value )
			{
				$item->set( $key, $value );
			}
		}

		if (!$this->isAvailable())
		{
		    return false;
		}
		
		Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
		$product_helper = new TiendaHelperProduct();
		$availableQuantity = $product_helper->getAvailableQuantity( $this->product_id, $this->product_attributes );
		if ( $availableQuantity->product_check_inventory && $item->product_qty > $availableQuantity->quantity )
		{
			$this->setError( JText::_( JText::sprintf("COM_TIENDA_NOT_AVAILABLE_QUANTITY", $availableQuantity->product_name, $item->product_qty ) ) );
			return false;
		}
		
		Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
		$carthelper = new TiendaHelperCarts( );
		
		// does the user/cart match all dependencies?
		$canAddToCart = $carthelper->canAddItem( $item, $this->user_id, 'user_id' );
		if ( !$canAddToCart )
		{
			$this->setError( JText::_('COM_TIENDA_CANNOT_ADD_ITEM_TO_CART') . " - " . $carthelper->getError( ) );
			return false;
		}
		
		// no matter what, fire this validation plugin event for plugins that extend the checkout workflow
		$results = array( );
		$dispatcher = &JDispatcher::getInstance( );
		$results = $dispatcher->trigger( "onBeforeAddToCart", array( &$item, $values ) );
		for ( $i = 0; $i < count( $results ); $i++ )
		{
			$result = $results[$i];
			if ( !empty( $result->error ) )
			{
    			$this->setError( JText::_('COM_TIENDA_CANNOT_ADD_ITEM_TO_CART') . " - " . $result->message );
    			return false;
			}
		}
		
		// if here, add to cart
		
		// After login, session_id is changed by Joomla, so store this for reference
		$session = &JFactory::getSession( );
		$session->set( 'old_sessionid', $session->getId( ) );
		
		// add the item to the cart
		$cartitem = $carthelper->addItem( $item );
		
		// fire plugin event
		$dispatcher = JDispatcher::getInstance( );
		$dispatcher->trigger( 'onAfterAddToCart', array( $cartitem, $values ) );
		
		return $cartitem;
	}
	
	public function isAvailable()
	{
		// create cart object out of item properties
		$item = new JObject;
		$item->user_id = $this->user_id;
		$item->product_id = ( int ) $this->product_id;
		$item->product_qty = !empty($this->product_quantity) ? $this->product_quantity : '1';
		$item->product_attributes = $this->product_attributes;
		$item->vendor_id = $this->vendor_id;
		$item->cartitem_params = $this->wishlistitem_params;
		
		JTable::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_tienda/tables' );
		$product = JTable::getInstance( 'Products', 'TiendaTable' );
		$product->load( array( 'product_id' => $this->product_id ) );
		
		if ( empty( $product->product_enabled ) || empty( $product->product_id ) )
		{
			$this->setError( JText::_('COM_TIENDA_INVALID_PRODUCT') );
			return false;
		}
		
		if ( $product->product_notforsale )
		{
			$this->setError( JText::_('COM_TIENDA_PRODUCT_NOT_FOR_SALE') );
			return false;
		}
		
        Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
	    $product_helper = new TiendaHelperProduct();
		$availableQuantity = $product_helper->getAvailableQuantity( $item->product_id, $item->product_attributes );
		if ( $availableQuantity->product_check_inventory && $item->product_qty > $availableQuantity->quantity )
		{
		    $this->setError( JText::sprintf("COM_TIENDA_NOT_AVAILABLE_QUANTITY", $availableQuantity->product_name, $item->product_qty ) );
			return false;
		}
		
		$results = array( );
		$dispatcher = JDispatcher::getInstance( );
		$results = $dispatcher->trigger( "onIsWishlistItemAvailable", array( &$item ) );
		for ( $i = 0; $i < count( $results ); $i++ )
		{
			$result = $results[$i];
			if ( !empty( $result->error ) )
			{
    			$this->setError( $result->message );
    			return false;
			}
		}
		
		return true;
	}
}
