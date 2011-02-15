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

class TiendaModelUserGroups extends TiendaModelBase
{
    protected function _buildQueryWhere(&$query)
    {
        $filter_user = $this->getState('filter_user');
        $filter_group = $this->getState('filter_group');
        
		if (strlen($filter_user))
        {
            $query->where('tbl.user_id = '.(int) $filter_user);
       	}

        if (strlen($filter_group))
        {
            $query->where('tbl.group_id = '.(int)$filter_group);
        }
    }
    
	protected function _buildQueryFields(&$query)
	{
		$field = array();
		$field[] = " tbl.* ";		
		$field[] = " g.ordering as ordering ";
		
		$query->select( $field );
	}    
    
	protected function _buildQueryJoins(&$query)
	{		
		$query->join('LEFT', '#__tienda_groups AS g ON g.group_id = tbl.group_id');    		
	}
    
	public function getList($refresh = false)
	{
		$list = parent::getList($refresh); 
		
		// If no item in the list, return an array()
        if( empty( $list ) ){
        	return array();
        }
        
		return $list;
	}
}
