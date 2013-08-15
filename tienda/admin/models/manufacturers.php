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

class TiendaModelManufacturers extends TiendaModelBase 
{
    protected function _buildQueryWhere(&$query)
    {
       	$filter     = $this->getState('filter');
        $filter_id_from	= $this->getState('filter_id_from');
        $filter_id_to	= $this->getState('filter_id_to');
        $filter_name	= $this->getState('filter_name');
       	$enabled		= $this->getState('filter_enabled');

       	if ($filter) 
       	{
			$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');

			$where = array();
			$where[] = 'LOWER(tbl.manufacturer_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.manufacturer_name) LIKE '.$key;
			$where[] = 'LOWER(tbl.manufacturer_description) LIKE '.$key;
			
			$query->where('('.implode(' OR ', $where).')');
       	}
    	if (strlen($enabled))
        {
        	$query->where('tbl.manufacturer_enabled = '.$enabled);
       	}
		if (strlen($filter_id_from))
        {
            if (strlen($filter_id_to))
        	{
        		$query->where('tbl.manufacturer_id >= '.(int) $filter_id_from);	
        	}
        		else
        	{
        		$query->where('tbl.manufacturer_id = '.(int) $filter_id_from);
        	}
       	}
		if (strlen($filter_id_to))
        {
        	$query->where('tbl.manufacturer_id <= '.(int) $filter_id_to);
       	}
    	if (strlen($filter_name))
        {
        	$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter_name ) ) ).'%');
        	$query->where('LOWER(tbl.manufacturer_name) LIKE '.$key);
       	}
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
			$item->link = 'index.php?option=com_tienda&controller=manufacturers&view=manufacturers&task=edit&id='.$item->manufacturer_id;
		}
		return $list;
	}
}
