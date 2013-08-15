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

class TiendaTableProductQuantities extends TiendaTable 
{
    function TiendaTableProductQuantities( &$db ) 
    {
        $tbl_key    = 'productquantity_id';
        $tbl_suffix = 'productquantities';
        $this->set( '_suffix', $tbl_suffix );
        $name       = 'tienda';
        
        parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );   
    }
	
	
	function check()
	{
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
}
