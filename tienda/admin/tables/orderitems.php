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

Tienda::load( 'TiendaTableEav', 'tables._baseeav' );

class TiendaTableOrderItems extends TiendaTableEav 
{
	function TiendaTableOrderItems ( &$db ) 
	{
		$tbl_key 	= 'orderitem_id';
		$tbl_suffix = 'orderitems';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'tienda';
		
		$this->_linked_table = 'products';
		$this->_linked_table_key_name = 'product_id';
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );	
	}
	
	public function check()
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
	
	public function store( $updateNulls=false )
	{
		$this->_linked_table_key = $this->product_id;
		return parent::store($updateNulls);
	}
	
	public function delete( $oid=null )
	{
	    if ($attributes = $this->getAttributes( $oid )) 
	    {
	        DSCTable::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_tienda/tables' );
	        $table = DSCTable::getInstance('OrderItemAttributes', 'TiendaTable');
	        foreach ($attributes as $attribute) 
	        {
	            if (!$table->delete( $attribute->orderitemattribute_id )) 
	            {
	                $this->setError( $table->getError() );
	            }
	        }
	    }
	    
	    $deleteItem = parent::delete( $oid );
	    
	    return parent::check();
	}
	
	public function getAttributes( $oid=null )
	{
	    $k = $this->_tbl_key;
	    if ($oid) {
	        $this->$k = intval( $oid );
	    }
	    
	    if (empty($this->$k)) 
	    {
	        return array();
	    }
	    
	    DSCModel::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_tienda/models' );
	    $model = DSCModel::getInstance( 'OrderitemAttributes', 'TiendaModel' );
	    $model->setState('filter_orderitemid', $this->$k );
	    return $model->getList();
	}
}
