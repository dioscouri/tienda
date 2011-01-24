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
       	$filter_userid  = $this->getState('filter_userid');
        $filter_date_from   = $this->getState('filter_date_from');
        $filter_date_to     = $this->getState('filter_date_to');
        $filter_datetype    = $this->getState('filter_datetype');
        $filter_recurs  = $this->getState('filter_recurs');
        $filter_productid  = $this->getState('filter_productid');
        $filter_productname  = $this->getState('filter_product_name');
        $filter_manufacturer_name  = $this->getState('filter_manufacturer_name');
        $filter_subscriptions_date_from = $this->getState('filter_subscriptions_date_from');
        $filter_subscriptions_date_to = $this->getState('filter_subscriptions_date_to');
        $filter_subscriptions_datetype = $this->getState('filter_subscriptions_datetype');
        $filter_orderstates = $this->getState('filter_orderstates');

        if ($filter)
       	{
			$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');

			$where = array();
			$where[] = 'LOWER(tbl.orderitem_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.orderitem_name) LIKE '.$key;
			
			$query->where('('.implode(' OR ', $where).')');
       	}

       	if ($filter_productname)
        {
            $key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter_productname ) ) ).'%');
            $where = array();
            $where[] = 'LOWER(tbl.orderitem_name) LIKE '.$key;
            $query->where('('.implode(' OR ', $where).')');
        }
        
    	if ($filter_manufacturer_name)
        {
            $key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter_manufacturer_name ) ) ).'%');
            $where = array();
            $where[] = 'LOWER(m.manufacturer_name) LIKE '.$key;
            $query->where('('.implode(' OR ', $where).')');
        }

       	if ($filter_orderid)
       	{
        	$query->where('tbl.order_id = '.$this->_db->Quote($filter_orderid));
       	}

       	if (strlen($filter_recurs))
        {
            $query->where('tbl.orderitem_recurs = 1');
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
        
    	if (strlen($filter_subscriptions_date_from))
        {
            switch ($filter_subscriptions_datetype)
            {               
                case "expires":
                    $query->where("sb.expires_datetime >= '".$filter_subscriptions_date_from."'");
                  break;
                case "created":
                default:
                    $query->where("sb.created_datetime >= '".$filter_subscriptions_date_from."'");
                  break;
            }
        }
        if (strlen($filter_subscriptions_date_to))
        {
            switch ($filter_subscriptions_datetype)
            {
                case "expires":
                    $query->where("sb.expires_datetime <= '".$filter_subscriptions_date_to."'");
                  break;
                case "created":
                default:
                    $query->where("sb.created_datetime <= '".$filter_subscriptions_date_to."'");
                  break;
            }
        }
        
        if (strlen($filter_userid))
        {
            $query->where('o.user_id = '.$this->_db->Quote($filter_userid));
        }

        if (strlen($filter_productid))
        {
            $query->where('tbl.product_id = '.$this->_db->Quote($filter_productid));
        }
        
    	if (is_array($filter_orderstates) && !empty($filter_orderstates))
        {
            $query->where('s.order_state_id IN('.implode(",", $filter_orderstates).')' );
        }
    }

    protected function _buildQueryFields(&$query)
    {
        $field = array();

        $field[] = " tbl.* ";
        $field[] = " p.product_name ";
        $field[] = " p.product_sku ";
        $field[] = " p.product_model ";
        $field[] = " p.product_params ";
        $field[] = " p.product_article ";
        $field[] = " o.* ";
        $field[] = " s.* ";        
        $field[] = " m.manufacturer_name ";
        $field[] = " sb.created_datetime AS subscription_created_datetime";
        $field[] = " sb.expires_datetime AS subscription_expires_datetime";

        $query->select( $field );
    }
    
    protected function _buildQueryJoins(&$query)
    {
    	$query->join('LEFT', '#__tienda_products AS p ON tbl.product_id = p.product_id');
        $query->join('LEFT', '#__tienda_orders AS o ON tbl.order_id = o.order_id');
        $query->join('LEFT', '#__tienda_orderstates AS s ON s.order_state_id = o.order_state_id');
        $query->join('LEFT', '#__tienda_manufacturers AS m ON m.manufacturer_id = p.manufacturer_id');
        $query->join('LEFT', '#__tienda_subscriptions AS sb ON sb.orderitem_id = tbl.orderitem_id');
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
			$item->link = 'index.php?option=com_tienda&view=orderitems&task=edit&id='.$item->orderitem_id;
		}
		return $list;
	}    
}