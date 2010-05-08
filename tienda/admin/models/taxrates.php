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

class TiendaModelTaxrates extends TiendaModelBase 
{
	protected function _buildQueryWhere(&$query)
	{
		$filter               = $this->getState('filter');
		$filter_id            = $this->getState('filter_id');
		$filter_geozone       = $this->getState('filter_geozone');
		$filter_taxclassid	  = $this->getState('filter_taxclassid');
		
		if ($filter) 
		{
			$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');

			$where = array();
			$where[] = 'LOWER(tbl.tax_rate_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.geozone_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.tax_class_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.tax_rate) LIKE '.$key;
			$where[] = 'LOWER(tbl.tax_rate_description) LIKE '.$key;
				
			$query->where('('.implode(' OR ', $where).')');
		}
		
		if (strlen($filter_id))
		{
			$query->where('tbl.tax_rate_id = '.$filter_id);
		}
		
	    if (strlen($filter_taxclassid))
        {
            $query->where('tbl.tax_class_id = '.$filter_taxclassid);
        }
        
	    if (strlen($filter_geozone))
        {
            $query->where('tbl.geozone_id = '.$filter_geozone);
        }
	}
    
	protected function _buildQueryJoins(&$query)
	{
		$query->join('LEFT', '#__tienda_geozones AS g ON g.geozone_id = tbl.geozone_id');
		$query->join('LEFT', '#__tienda_taxclasses AS c ON c.tax_class_id = tbl.tax_class_id');
	}
	
	protected function _buildQueryFields(&$query)
	{
		$field = array();
		$field[] = " g.geozone_name AS geozone_name ";
		$field[] = " c.tax_class_name AS taxclass_name ";
		
		$query->select( $this->getState( 'select', 'tbl.*' ) );		
		$query->select( $field );
	}
	
	public function getList()
	{
		$list = parent::getList(); 
		
		// If no item in the list, return an array()
        if( empty( $list ) ){
        	return array();
        }
		
		foreach($list as $item)
		{
			$item->link = "index.php?option=com_tienda&controller=taxrates&view=taxrates&tmpl=component&task=edit&classid=$item->tax_class_id&id=$item->tax_rate_id";
		}
		
		return $list;
	}
}
