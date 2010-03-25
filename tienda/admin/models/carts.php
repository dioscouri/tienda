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
defined('_JEXEC') or die('Restricted access');

JLoader::import( 'com_tienda.models._base', JPATH_ADMINISTRATOR.DS.'components' );

class TiendaModelCarts extends TiendaModelBase
{
    protected function _buildQueryWhere(&$query)
    {
        $user =& JFactory::getUser();
        $query->where('tbl.user_id = '.(int) $user->id);

        $productid  = $this->getState('filter_product_id');

        if (!empty($productid)) 
        {
            $query->where('tbl.product_id = '.(int) $productid);
            $this->setState('limit', 1);
       	}
    }
    
    protected function _buildQueryJoins(&$query)
    {
        $query->join('LEFT', '#__tienda_products AS p ON tbl.product_id = p.product_id');	
	}
	
	protected function _buildQueryFields(&$query)
	{
       	$field = array();
        $field[] = " p.product_name as product_name ";

		// This subquery returns the default price for the product and allows for sorting by price
		$date = JFactory::getDate()->toMysql();
		$default_group = '0'; // TODO Use default user_group_id
		$field[] = "
			(
			SELECT 
				prices.product_price
			FROM
				#__tienda_productprices AS prices 
			WHERE 
				prices.product_id = tbl.product_id 
				AND prices.user_group_id = '$default_group'
				AND prices.product_price_startdate <= '$date' 
				AND (prices.product_price_enddate >= '$date' OR prices.product_price_enddate = '0000-00-00 00:00:00' )
				ORDER BY prices.price_quantity_start ASC
			LIMIT 1
			) 
		AS product_price ";
		
        $query->select( $this->getState( 'select', 'tbl.*' ) );
        $query->select( $field );
	}
	
    public function getList()
    {
    	JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $list = parent::getList();
        
    	// If no item in the list, return an array()
        if( empty( $list ) ){
        	return array();
        }
        
        foreach($list as $item)
        {
        	$item->orderitem_attributes_price = '0.00000';
            $item->attributes = array(); // array of each selected attribute's object
            $attributes_names = array();
            $attibutes_array = explode(',', $item->product_attributes);
            foreach ($attibutes_array as $attrib_id)
            {
            	// load the attrib's object
            	$table = JTable::getInstance('ProductAttributeOptions', 'TiendaTable');
            	$table->load( $attrib_id );
            	// update the price
            	$item->product_price = $item->product_price + floatval( "$table->productattributeoption_prefix"."$table->productattributeoption_price");
            	// store the attribute's price impact
            	$item->orderitem_attributes_price = $item->orderitem_attributes_price + floatval( "$table->productattributeoption_prefix"."$table->productattributeoption_price");
            	$item->orderitem_attributes_price = number_format($item->orderitem_attributes_price, '5', '.', '');
            	// store a csv of the attrib names
                $attributes_names[] = JText::_( $table->productattributeoption_name ); 
            }

            // Could someone explain to me why this is necessary?
            if ($item->orderitem_attributes_price >= 0)
            {
            	// formatted for storage in the DB
                $item->orderitem_attributes_price = "+$item->orderitem_attributes_price";	
            }
            
            $item->attributes_names = implode(', ', $attributes_names);
        }
        return $list;
    }
}