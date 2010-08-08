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
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaModelBase', 'models._base' );

class TiendaModelProductRelations extends TiendaModelBase
{
    protected function _buildQueryWhere(&$query)
    {
        $filter_id = $this->getState('filter_id');
        $filter_product = $this->getState('filter_product');
        $filter_relation = $this->getState('filter_relation');
       	$filter_product_from = $this->getState('filter_product_from');       	
        $filter_product_to = $this->getState('filter_product_to');
        
		if (strlen($filter_id))
        {
            $query->where('tbl.productrelation_id = '.(int) $filter_id);
       	}

        if (strlen($filter_product))
        {
            $query->where(
                '(tbl.product_id_from = '.(int) $filter_product .' OR tbl.product_id_to = '.(int) $filter_product .' )'
            );
        }
       	
       	if (strlen($filter_product_from))
        {
            $query->where('tbl.product_id_from = '.(int) $filter_product_from);
       	}
        
       	if (strlen($filter_product_to))
        {
            $query->where('tbl.product_id_to = '.(int) $filter_product_to);
        }
        
        if (strlen($filter_relation))
        {
            $query->where("tbl.relation_type = '$filter_relation'");
        }
    }

    protected function _buildQueryJoins(&$query)
    {
        $query->join('LEFT', '#__tienda_products AS p_from ON p_from.product_id = tbl.product_id_from');
        $query->join('LEFT', '#__tienda_products AS p_to ON p_to.product_id = tbl.product_id_to');   
    }
    
    protected function _buildQueryFields(&$query)
    {
        $date = JFactory::getDate()->toMysql();
        $default_group = '0'; // TODO Use default user_group_id
        
        $fields = array();
        $fields[] = " p_from.product_name as product_name_from ";
        $fields[] = " p_from.product_sku as product_sku_from ";
        $fields[] = " p_from.product_model as product_model_from ";
        $fields[] = "
            (
            SELECT 
                prices.product_price
            FROM
                #__tienda_productprices AS prices 
            WHERE 
                prices.product_id = tbl.product_id_from 
                AND prices.user_group_id = '$default_group'
                AND prices.product_price_startdate <= '$date' 
                AND (prices.product_price_enddate >= '$date' OR prices.product_price_enddate = '0000-00-00 00:00:00' )
                ORDER BY prices.price_quantity_start ASC
            LIMIT 1
            ) 
        AS product_price_from ";
        
        $fields[] = " p_to.product_name as product_name_to ";
        $fields[] = " p_to.product_sku as product_sku_to ";
        $fields[] = " p_to.product_model as product_model_to ";
        $fields[] = "
            (
            SELECT 
                prices.product_price
            FROM
                #__tienda_productprices AS prices 
            WHERE 
                prices.product_id = tbl.product_id_to 
                AND prices.user_group_id = '$default_group'
                AND prices.product_price_startdate <= '$date' 
                AND (prices.product_price_enddate >= '$date' OR prices.product_price_enddate = '0000-00-00 00:00:00' )
                ORDER BY prices.price_quantity_start ASC
            LIMIT 1
            ) 
        AS product_price_to ";

        $query->select( $this->getState( 'select', 'tbl.*' ) );
        $query->select( $fields );
    }
    
	public function getList()
	{
		$list = parent::getList(); 
		
		// If no item in the list, return an array()
        if( empty( $list ) ){
        	return array();
        }
        
		return $list;
	}
}
