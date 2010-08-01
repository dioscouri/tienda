<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaModelBase', 'models._base' );

class TiendaModelSubscriptions extends TiendaModelBase 
{
    protected function _buildQueryWhere(&$query)
    {
       	$filter = $this->getState('filter');
       	$filter_subscriptionid = $this->getState('filter_subscriptionid');
        $filter_userid = $this->getState('filter_userid');
        $filter_orderid = $this->getState('filter_orderid');
        $filter_orderitemid = $this->getState('filter_orderitemid');
        $filter_status = $this->getState('filter_status');
        
       	if ($filter) 
       	{
			$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');
			$where = array();
			$where[] = 'LOWER(tbl.subscription_id) LIKE '.$key;
			$query->where('('.implode(' OR ', $where).')');
       	}
       	
        if (strlen($filter_subscriptionid))
        {
            $query->where('tbl.subscription_id = '.$this->_db->Quote($filter_subscriptionid));
        }
        
        if (strlen($filter_userid))
        {
            $query->where('tbl.user_id = '.$this->_db->Quote($filter_userid));
        }

        if (strlen($filter_orderid))
        {
            $query->where('tbl.order_id = '.$this->_db->Quote($filter_orderid));
        }

        if (strlen($filter_orderitemid))
        {
            $query->where('tbl.orderitem_id = '.$this->_db->Quote($filter_orderitemid));
        }

        if (strlen($filter_status))
        {
            $query->where('tbl.status = '.$this->_db->Quote($filter_status));
        }
    }
    
    protected function _buildQueryJoins(&$query)
    {
        $query->join('LEFT', '#__tienda_orderitems AS oi ON oi.orderitem_id = tbl.orderitem_id');
        $query->join('LEFT', '#__tienda_products AS p ON oi.product_id = p.product_id');
        $query->join('LEFT', '#__tienda_orders AS o ON tbl.order_id = o.order_id');
    }
    
    protected function _buildQueryFields(&$query)
    {
        $field = array();

        $field[] = " tbl.* ";
        $field[] = " p.product_name ";
        $field[] = " p.product_sku ";
        $field[] = " p.product_model ";
        $field[] = " o.* ";
        $field[] = " oi.* ";

        $query->select( $field );
    }
}