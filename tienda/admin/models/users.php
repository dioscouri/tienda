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

class TiendaModelUsers extends TiendaModelBase 
{	
    protected function _buildQueryWhere(&$query)
    {
       	$filter     = $this->getState('filter');
       	$block	 	= $this->getState('filter_block');
        $filter_id_from = $this->getState('filter_id_from');
        $filter_id_to   = $this->getState('filter_id_to');
        $filter_name    = $this->getState('filter_name');
        $filter_subnum    = $this->getState('filter_subnum');
        $filter_username    = $this->getState('filter_username');
        $filter_email    = $this->getState('filter_email');
        $filter_group    = $this->getState('filter_group');
        
       	if ($filter) 
       	{
			$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');
			$where = array();
			$where[] = 'LOWER(tbl.id) LIKE '.$key;
			$where[] = 'LOWER(tbl.name) LIKE '.$key;
			$where[] = 'LOWER(tbl.username) LIKE '.$key;
			$where[] = 'LOWER(tbl.email) LIKE '.$key;
			$query->where('('.implode(' OR ', $where).')');
       	}
        if (strlen($block))
        {
        	$query->where('tbl.block = '.$this->_db->Quote($block));
       	}
        if (strlen($filter_id_from))
        {
            if (strlen($filter_id_to))
            {
                $query->where('tbl.id >= '.(int) $filter_id_from);
            }
                else
            {
                $query->where('tbl.id = '.(int) $filter_id_from);
            }
        }
        if (strlen($filter_id_to))
        {
            $query->where('tbl.id <= '.(int) $filter_id_to);
        }
        if ($filter_name) 
        {
            $key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter_name ) ) ).'%');
            $where = array();
            $where[] = 'LOWER(tbl.name) LIKE '.$key;
            $query->where('('.implode(' OR ', $where).')');
        }
        if ($filter_username) 
        {
            $key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter_username ) ) ).'%');
            $where = array();
            $where[] = 'LOWER(tbl.username) LIKE '.$key;
            $query->where('('.implode(' OR ', $where).')');
        }
        if ($filter_email) 
        {
            $key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter_email ) ) ).'%');
            $where = array();
            $where[] = 'LOWER(tbl.email) LIKE '.$key;
            $query->where('('.implode(' OR ', $where).')');
        }
        if (strlen($filter_group))
        {
            $query->where('g.group_id = '.(int) $filter_group);
        }

       	if (strlen($filter_subnum))
        {
        	$query->where('ui.sub_number LIKE '.$this->_db->Quote('%'.$filter_subnum.'%'));
       	}
    }
    
	protected function _buildQueryJoins(&$query)
	{
    $filter_group    = $this->getState('filter_usergroup');
		$query->join('LEFT', '#__tienda_userinfo AS ui ON ui.user_id = tbl.id');
		if( strlen( $filter_group ) )
			$query->join('LEFT', '#__tienda_usergroupxref AS ug ON ( ug.user_id = tbl.id AND ug.group_id = '.( int )$filter_group.')');
		else
			$query->join('LEFT', '#__tienda_usergroupxref AS ug ON ug.user_id = tbl.id');
		$query->join('LEFT', '#__tienda_groups AS g ON ug.group_id = g.group_id');
	}    
	
	protected function _buildQueryFields(&$query)
	{
		$field = array();
		$field[] = " ui.user_info_id AS user_info_id ";		
		$field[] = " ui.company AS company ";
		$field[] = " ui.title AS title ";
		$field[] = " ui.last_name AS last_name ";
		$field[] = " ui.first_name AS first_name ";
		$field[] = " ui.middle_name AS middle_name ";
		$field[] = " ui.phone_1 AS phone_1 ";
		$field[] = " ui.phone_2 AS phone_2 ";
		$field[] = " ui.fax AS fax ";
		$field[] = " ui.sub_number AS sub_number ";
		$field[] = " g.group_id ";
		$field[] = " g.group_name ";
		$field[] = " g.group_description ";
		
		$query->select( $this->getState( 'select', 'tbl.*' ) );		
		$query->select( $field );
	}	
    	
	public function getList($refresh = false)
	{
		$list = parent::getList($refresh); 
		foreach($list as $item)
		{
			$item->link = 'index.php?option=com_tienda&controller=users&view=users&task=view&id='.$item->id;
			$item->link_createorder = 'index.php?option=com_tienda&view=orders&task=add&userid='.$item->id;
		}
		return $list;
	}
}
