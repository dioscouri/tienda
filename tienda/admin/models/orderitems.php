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

Tienda::load( 'TiendaModelBase', 'models._base' );

class TiendaModelOrderItems extends TiendaModelBase 
{
	
    protected function _buildQueryWhere(&$query)
    {
       	$filter     	= $this->getState('filter');
       	$filter_orderid	= $this->getState('filter_orderid');
        $filter_date_from   = $this->getState('filter_date_from');
        $filter_date_to     = $this->getState('filter_date_to');
        $filter_datetype    = $this->getState('filter_datetype');
        
       	if ($filter)
       	{
			$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');

			$where = array();
			$where[] = 'LOWER(tbl.orderitem_id) LIKE '.$key;
			
			$query->where('('.implode(' OR ', $where).')');
       	}

       	if ($filter_orderid)
       	{
        	$query->where('tbl.order_id = '.$this->_db->Quote($filter_orderid));
       	}
       	
            if (strlen($filter_date_from))
        {
            switch ($filter_datetype)
            {
                case "shipped":
                    $query->where("o.shipped_date >= '".$filter_date_from."'");
                  break;
                case "modified":
                    $query->where("o.modified_date >= '".$filter_date_from."'");
                  break;
                case "created":
                default:
                    $query->where("o.created_date >= '".$filter_date_from."'");
                  break;
            }
        }
        if (strlen($filter_date_to))
        {
            switch ($filter_datetype)
            {
                case "shipped":
                    $query->where("o.shipped_date <= '".$filter_date_to."'");
                  break;
                case "modified":
                    $query->where("o.modified_date <= '".$filter_date_to."'");
                  break;
                case "created":
                default:
                    $query->where("o.created_date <= '".$filter_date_to."'");
                  break;
            }
        }
    }

    protected function _buildQueryFields(&$query)
    {
        $field = array();

        $field[] = " tbl.* ";
        $field[] = " p.product_name ";
        $field[] = " p.product_sku ";
        $field[] = " p.product_model ";
        $field[] = " o.* ";
        $field[] = " s.* ";

        $query->select( $field );
    }
    
    protected function _buildQueryJoins(&$query)
    {
    	$query->join('LEFT', '#__tienda_products AS p ON tbl.product_id = p.product_id');
        $query->join('LEFT', '#__tienda_orders AS o ON tbl.order_id = o.order_id');
        $query->join('LEFT', '#__tienda_orderstates AS s ON s.order_state_id = o.order_state_id');
    }
    
	public function getList()
	{
		$list = parent::getList(); 
		
		// If no item in the list, return an array()
        if( empty( $list ) ){
        	return array();
        }
		
		foreach($list as $item)
		{
			$item->link = 'index.php?option=com_tienda&controller=orderitems&view=orderitems&task=edit&id='.$item->orderitem_id;
		}
		return $list;
	}    
}