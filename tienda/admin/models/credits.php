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

class TiendaModelCredits extends TiendaModelBase 
{
    protected function _buildQueryWhere(&$query)
    {
       	$filter     = $this->getState('filter');
        $filter_id_from = $this->getState('filter_id_from');
        $filter_id_to   = $this->getState('filter_id_to');
        $filter_type    = $this->getState('filter_type');
        $filter_user    = $this->getState('filter_user');
        $filter_userid  = $this->getState('filter_userid');
        $filter_enabled  = $this->getState('filter_enabled');
        $filter_orderid = $this->getState('filter_orderid');
        $filter_withdraw = $this->getState('filter_withdraw');
        $filter_date_from   = $this->getState('filter_date_from');
        $filter_date_to     = $this->getState('filter_date_to');
        $filter_datetype    = $this->getState('filter_datetype');
        $filter_amount_from  = $this->getState('filter_amount_from');
        $filter_amount_to    = $this->getState('filter_amount_to');
        
       	if ($filter) 
       	{
			$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');
			$where = array();
			$where[] = 'LOWER(tbl.credit_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.credit_comments) LIKE '.$key;
			$where[] = 'LOWER(tbl.credit_code) LIKE '.$key;
			$where[] = 'LOWER(tbl.credit_type) LIKE '.$key;
			$query->where('('.implode(' OR ', $where).')');			
       	}
       	
        if (strlen($filter_id_from))
        {
            if (strlen($filter_id_to))
            {
                $query->where('tbl.credit_id >= '.(int) $filter_id_from);
            }
                else
            {
                $query->where('tbl.credit_id = '.(int) $filter_id_from);
            }
        }
        
        if (strlen($filter_id_to))
        {
            $query->where('tbl.credit_id <= '.(int) $filter_id_to);
        }
        
        if ($filter_type) 
        {
            $key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter_type ) ) ).'%');
            $where = array();
            $where[] = 'LOWER(tbl.credittype_code) LIKE '.$key;
            $query->where('('.implode(' OR ', $where).')');
        }

        if ($filter_user) 
        {
            $key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');
            $where = array();
            $where[] = 'LOWER(tbl.user_id) LIKE '.$key;
            $where[] = 'LOWER(u.name) LIKE '.$key;
            $where[] = 'LOWER(u.email) LIKE '.$key;
            $where[] = 'LOWER(u.username) LIKE '.$key;
            $query->where('('.implode(' OR ', $where).')');         
        }
        
        if ($filter_userid) 
        {
            $query->where('tbl.user_id = '.$filter_userid);
        }

        if (strlen($filter_orderid))
        {
            $query->where('tbl.order_id = '.$this->_db->Quote($filter_orderid));
        }

        if (strlen($filter_withdraw))
        {
            $query->where('tbl.credit_withdrawable = '.$this->_db->Quote($filter_withdraw));
        }
        
        if (strlen($filter_enabled))
        {
            $query->where('tbl.credit_enabled = '.$this->_db->Quote($filter_enabled));
        }
        
        if (strlen($filter_date_from))
        {
            switch ($filter_datetype)
            {
                case "created":
                default:
                    $query->where("tbl.created_date >= '".$filter_date_from."'");
                  break;
            }
        }
        
        if (strlen($filter_date_to))
        {
            switch ($filter_datetype)
            {
                case "created":
                default:
                    $query->where("tbl.created_date <= '".$filter_date_to."'");
                  break;
            }
        }
        
        if (strlen($filter_amount_from))
        {
            $query->having("tbl.credit_amount >= '". $filter_amount_from ."'");
        }
        
        if (strlen($filter_amount_to))
        {
            $query->having("tbl.credit_amount <= '". $filter_amount_to ."'");
        }
    }
    
    protected function _buildQueryJoins(&$query)
    {
        $query->join('LEFT', '#__users AS u ON u.id = tbl.user_id');
        $query->join('LEFT', '#__tienda_credittypes AS ct ON ct.credittype_code = tbl.credittype_code');
    }
    
    protected function _buildQueryFields(&$query)
    {
        $field = array();

        $field[] = " tbl.* ";
        $field[] = " u.name AS user_name ";
        $field[] = " u.username AS user_username "; 
        $field[] = " u.email ";
        $field[] = " ct.credittype_name";

        $query->select( $field );
    }
    
	public function getList($refresh = false)
	{
		$list = parent::getList($refresh); 
		
		// If no item in the list, return an array()
        if( empty( $list ) ){
        	return array();
        }
		
		foreach($list as $item)
		{
			$item->link = 'index.php?option=com_tienda&controller=credits&view=credits&task=edit&id='.$item->credit_id;
		}
		return $list;
	}
}
