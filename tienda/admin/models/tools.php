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

class TiendaModelTools extends TiendaModelBase 
{	
    protected function _buildQueryWhere(&$query)
    {
       	$filter     = $this->getState('filter');
        $filter_id_from = $this->getState('filter_id_from');
        $filter_id_to   = $this->getState('filter_id_to');
        $filter_name    = $this->getState('filter_name');
        
       	if ($filter) 
       	{
			$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');
			$where = array();
			$where[] = 'LOWER(tbl.id) LIKE '.$key;
			$where[] = 'LOWER(tbl.name) LIKE '.$key;
			$query->where('('.implode(' OR ', $where).')');
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
        
        // force returned records to only be tienda tools 
        $query->where("tbl.folder = 'tienda'");
        $query->where("tbl.element LIKE 'tool_%'");
    }
    	
	public function getList($refresh = false)
	{
		$list = parent::getList($refresh);
		foreach($list as $item)
		{
			if(version_compare(JVERSION,'1.6.0','ge')) {$item->id = $item->extension_id; }
			$item->link = 'index.php?option=com_tienda&view=tools&task=view&id='.$item->id;
		}
		return $list;
	}
	
	/**
	 * Gets an item for displaying (as opposed to saving, which requires a JTable object)
	 * using the query from the model and the tbl's unique identifier
	 *
	 * @return database->loadObject() record
	 */
	public function getItem( $emptyState=true )
	{
		parent::getItem( $emptyState );
		// adding this in the model so we don't have to have if statemnets all over the views and controllers'
		if(version_compare(JVERSION,'1.6.0','ge')) {
			if(!empty($this->_item->extension_id)) {
			$this->_item->id =	$this->_item->extension_id;
			}
		}
		
		return $this->_item;
	}
}
