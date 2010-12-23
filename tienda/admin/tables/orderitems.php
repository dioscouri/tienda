<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

Tienda::load( 'TiendaTable', 'tables._base' );

class TiendaTableOrderItems extends TiendaTable 
{
	function TiendaTableOrderItems ( &$db ) 
	{
		
		$tbl_key 	= 'orderitem_id';
		$tbl_suffix = 'orderitems';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'tienda';
		
		$this->linked_table = 'products';
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );	
	}
	
	function check()
	{
        $nullDate	= $this->_db->getNullDate();
		if (empty($this->modified_date) || $this->modified_date == $nullDate)
		{
			$date = JFactory::getDate();
			$this->modified_date = $date->toMysql();
		}
		
	    // be sure that product_attributes is sorted numerically
        if ($product_attributes = explode( ',', $this->orderitem_attributes ))
        {
            sort($product_attributes);
            $this->orderitem_attributes = implode(',', $product_attributes);
        }
        
		return true;
	}
	
	function store()
	{
		$this->linked_table_key = $this->product_id;
		return parent::store();
	}
}
