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

JLoader::import( 'com_tienda.tables._basexref', JPATH_ADMINISTRATOR.DS.'components' );

class TableCarts extends TiendaTableXref 
{
    /**
     * @param $db
     * @return unknown_type
     */
    function TableCarts ( &$db ) 
    {
        $keynames = array();
        $keynames['user_id']    = 'user_id';
        $keynames['product_id'] = 'product_id';
        $keynames['product_attributes'] = 'product_attributes';
        $this->setKeyNames( $keynames );
    	
        $tbl_key      = 'user_id';
        $tbl_suffix   = 'carts';
        $name         = 'tienda';
        
        $this->set( '_tbl_key', $tbl_key );
        $this->set( '_suffix', $tbl_suffix );
        
        parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );    
    }
    
    function check()
    {        
        if (empty($this->user_id))
        {
            $this->setError( JText::_( "User Required" ) );
            return false;
        }
        if (empty($this->product_id))
        {
            $this->setError( JText::_( "Product Required" ) );
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
}
