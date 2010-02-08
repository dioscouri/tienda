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

class TiendaModelAddresses extends TiendaModelBase 
{	
    protected function _buildQueryWhere(&$query)
    {
       	$filter   			= $this->getState('filter');
       	$filter_deleted		= $this->getState('filter_deleted');
       	$filter_userid		= $this->getState('filter_userid');
		$filter_addressid	= $this->getState('filter_addressid');
		$filter_shippingid  = $this->getState('filter_shippingid');
		$filter_isdefaultbilling  = $this->getState('filter_isdefaultbilling');
		$filter_isdefaultshipping = $this->getState('filter_isdefaultshipping');

       	if ($filter) 
       	{
			$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');
			$where = array();
			$where[] = 'LOWER(tbl.address_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.address_1) LIKE '.$key;
			$where[] = 'LOWER(tbl.address_2) LIKE '.$key;
			$where[] = 'LOWER(tbl.address_zip) LIKE '.$key;
			$where[] = 'LOWER(c.country_name) LIKE '.$key;
			$where[] = 'LOWER(z.zone_name) LIKE '.$key;
			
			$query->where('('.implode(' OR ', $where).')');
       	}
        if (strlen($filter_deleted))
        {
        	$query->where('tbl.is_deleted = '.$filter_deleted);
       	}
		
		if ($filter_addressid){
			$query->where('tbl.address_id = '.$filter_addressid);
			echo 'tbl.address_id = '.$filter_addressid;
		}
       	if ($filter_userid)
       	{
        	$query->where('tbl.user_id = '.$filter_userid);
       	}    
       	if ($filter_shippingid)
       	{
        	$query->where('tbl.is_default_shipping = 1');
       	}
       	
        if ($filter_isdefaultbilling)
        {
            $query->where('tbl.is_default_billing = 1');
        }
        if ($filter_isdefaultshipping)
        {
            $query->where('tbl.is_default_shipping = 1');
        }
    }
    
	protected function _buildQueryFields(&$query)
	{
		$field = array();
		$field[] = " tbl.* ";		
		$field[] = " c.country_name as country_name ";
		$field[] = " z.zone_name as zone_name ";
		
		$query->select( $field );
	}    

	protected function _buildQueryJoins(&$query)
	{
		$query->join('LEFT', '#__tienda_countries c ON c.country_id = tbl.country_id');
		$query->join('LEFT', '#__tienda_zones AS z ON z.zone_id = tbl.zone_id');
	}
	
    public function getList()
    {
        $list = parent::getList();
        foreach(@$list as $item)
        {
            $item->link = 'index.php?option=com_tienda&view=addresses&task=edit&id='.$item->address_id;
        }
        return $list;
    }
}