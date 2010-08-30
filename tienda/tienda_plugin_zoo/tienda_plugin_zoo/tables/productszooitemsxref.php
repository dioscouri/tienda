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

Tienda::load( 'TiendaTableXref', 'tables._basexref' );

class TiendaTableProductsZooItemsXref extends TiendaTableXref
{
	function TiendaTableProductsZooItemsXref ( &$db ) 
	{
        $keynames = array();
		$keynames['item_id']  = 'item_id';
        $keynames['product_id'] = 'product_id';
        $this->setKeyNames( $keynames );
                
		$tbl_key 	= 'item_id';
		$tbl_suffix = 'productszooitemsxref';
		$name 		= 'tienda';
		
		$this->set( '_tbl_key', $tbl_key );
		$this->set( '_suffix', $tbl_suffix );
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );	
	}
	
	/**
	 * Checks row for data integrity.
	 * Assumes working dates have been converted to local time for display, 
	 * so will always convert working dates to GMT
	 *  
	 * @return unknown_type
	 */
	function check()
	{
        if (empty($this->item_id))
        {
            $this->setError( JText::_( "Item Required" ) );
            return false;
        }
        if (empty($this->product_id))
        {
            $this->setError( JText::_( "Product Required" ) );
            return false;
        }

		return true;
	}
}