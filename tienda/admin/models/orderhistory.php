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

JLoader::import( 'com_tienda.models._base', JPATH_ADMINISTRATOR.DS.'components' );

class TiendaModelOrderHistory extends TiendaModelBase 
{
    protected function _buildQueryWhere(&$query)
    {
       	$filter             = $this->getState('filter');
       	$filter_orderid     = $this->getState('filter_orderid');
       	$filter_notified    = $this->getState('filter_notified');

       	if ($filter) 
       	{
			$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');

			$where = array();
			$where[] = 'LOWER(tbl.order_history_id) LIKE '.$key;
			
			$query->where('('.implode(' OR ', $where).')');
       	}
       	
        if ($filter_orderid)
        {
            $query->where('tbl.order_id = '.$filter_orderid);
        }
        
        if ($filter_notified)
        {
            $query->where('tbl.customer_notified = '.$filter_notified);
        }
        
    }
    
    protected function _buildQueryJoins(&$query)
    {
        $query->join('LEFT', '#__tienda_orderstates AS orderstates ON orderstates.order_state_id = tbl.order_state_id');   
    }
    
    protected function _buildQueryFields(&$query)
    {
        $field = array();

        $field[] = " tbl.* ";
        $field[] = " orderstates.* ";

        $query->select( $field );
    }
}