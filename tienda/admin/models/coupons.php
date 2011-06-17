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

class TiendaModelCoupons extends TiendaModelBase 
{
    protected function _buildQueryWhere(&$query)
    {
       	$filter     = $this->getState('filter');
        $filter_id_from	= $this->getState('filter_id_from');
        $filter_id_to	= $this->getState('filter_id_to');
        $filter_name	= $this->getState('filter_name');
        $filter_code    = $this->getState('filter_code');
       	$filter_enabled		= $this->getState('filter_enabled');
        $filter_date_from   = $this->getState('filter_date_from');
        $filter_date_to     = $this->getState('filter_date_to');
        $filter_datetype    = $this->getState('filter_datetype');
        $filter_type    = $this->getState('filter_type');
       	$filter_group    = $this->getState('filter_group');
       	$filter_automatic    = $this->getState('filter_automatic');
       	$filter_ids = $this->getState('filter_ids');
        
       	if ($filter) 
       	{
			$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');

			$where = array();
			$where[] = 'LOWER(tbl.coupon_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.coupon_name) LIKE '.$key;
			$where[] = 'LOWER(tbl.coupon_code) LIKE '.$key;
			
			$query->where('('.implode(' OR ', $where).')');
       	}
       	
    	if (strlen($filter_enabled))
        {
        	$query->where('tbl.coupon_enabled = '.$filter_enabled);
       	}
       	
		if (strlen($filter_id_from))
        {
            if (strlen($filter_id_to))
        	{
        		$query->where('tbl.coupon_id >= '.(int) $filter_id_from);	
        	}
        		else
        	{
        		$query->where('tbl.coupon_id = '.(int) $filter_id_from);
        	}
       	}
       	
		if (strlen($filter_id_to))
        {
        	$query->where('tbl.coupon_id <= '.(int) $filter_id_to);
       	}
       	
    	if (strlen($filter_name))
        {
        	$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter_name ) ) ).'%');
        	$query->where('LOWER(tbl.coupon_name) LIKE '.$key);
       	}
       	
        if (strlen($filter_code))
        {
            $key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter_code ) ) ).'%');
            $query->where('LOWER(tbl.coupon_code) LIKE '.$key);
        }
        
        if (strlen($filter_type))
        {
            $query->where('tbl.coupon_type = '.$filter_type);
        }

        if (strlen($filter_group))
        {
            $query->where('tbl.coupon_group = '.$filter_group);
        }

        if (strlen($filter_automatic))
        {
            $query->where('tbl.coupon_automatic = '.$filter_automatic);
        }
        
        if(is_array($filter_ids) && !empty($filter_ids))
        {
        	$query->where('tbl.coupon_id IN('.implode(",", $filter_ids).')' );
        }
    }
 	
	public function getList($reload = false)
	{
		if (empty( $this->_list ) || $reload)
		{
			$query = $this->getQuery($reload);
			$this->_list = $this->_getList( (string) $query, $this->getState('limitstart'), $this->getState('limit') );
		}
		$list = $this->_list;
		
		// If no item in the list, return an array()
        if( empty( $list ) ){
        	return array();
        }
		
		foreach($list as $item)
		{
			$item->link = 'index.php?option=com_tienda&view=coupons&task=edit&id='.$item->coupon_id;
		}
		return $list;
	}
	
	/**
	 * Gets the model's query, building it if it doesn't exist
	 * @return valid query object
	 * 
	 * @enterprise
	 */
	public function getQuery($reload = false)
	{
		if (empty( $this->_query ) || $reload )
		{
			$this->_query = $this->_buildQuery($reload);
		}
		return $this->_query;
	}
}
