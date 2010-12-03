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

class TiendaModelEavAttributes extends TiendaModelBase 
{
    protected function _buildQueryWhere(&$query)
    {
       	$filter     = $this->getState('filter');
        $filter_id_from = $this->getState('filter_id_from');
        $filter_id_to   = $this->getState('filter_id_to');
        $filter_name    = $this->getState('filter_name');
        $filter_enabled  = $this->getState('filter_enabled');
        $filter_entitytype  = $this->getState('filter_entitytype');
       	
       	if ($filter) 
       	{
			$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');
			$where = array();
			$where[] = 'LOWER(tbl.eavattribute_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.eavattribute_label) LIKE '.$key;
			$query->where('('.implode(' OR ', $where).')');			
       	}
        if (strlen($filter_id_from))
        {
            if (strlen($filter_id_to))
            {
                $query->where('tbl.eavattribute_id >= '.(int) $filter_id_from);
            }
                else
            {
                $query->where('tbl.eavattribute_id = '.(int) $filter_id_from);
            }
        }
        if (strlen($filter_id_to))
        {
            $query->where('tbl.eavattribute_id <= '.(int) $filter_id_to);
        }
        if ($filter_name) 
        {
            $key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter_name ) ) ).'%');
            $where = array();
            $where[] = 'LOWER(tbl.eavattribute_label) LIKE '.$key;
            $query->where('('.implode(' OR ', $where).')');
        }
    	if ($filter_entitytype) 
        {
            $key    = $this->_db->Quote($this->_db->getEscaped( trim( strtolower( $filter_entitytype ) ) ));
            $query->where('LOWER(tbl.eaventity_type) LIKE '.$key);
        }
        if (strlen($filter_enabled))
        {
            $query->where('tbl.eavattribute_enabled = '.$this->_db->Quote($filter_enabled));
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
			$item->link = 'index.php?option=com_tienda&controller=eavattributes&view=eavattributes&task=edit&id='.$item->eavattribute_id;
		}
		return $list;
	}

}
