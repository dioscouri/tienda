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

class TiendaModelZonerelations extends TiendaModelBase 
{
	protected function _buildQueryWhere(&$query)
	{
		$filter               = $this->getState('filter');
		$filter_geozoneid     = $this->getState('filter_geozoneid');
		$filter_zone          = $this->getState('filter_zone');
		$filter_geozonetype   = $this->getState('filter_geozonetype');
		$filter_countryid     = $this->getState('filter_countryid');
		
		if ($filter) 
		{
			$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');

			$where = array();
			$where[] = 'LOWER(tbl.zonerelation_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.zone_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.geozone_id) LIKE '.$key;
			
			$query->where('('.implode(' OR ', $where).')');
		}
		if (strlen($filter_geozoneid))
		{
			$query->where('tbl.geozone_id = '.$filter_geozoneid);
		}
	    if (strlen($filter_geozonetype))
        {
            $query->where('gz.geozonetype_id = '.$filter_geozonetype);
        }
	    if (strlen($filter_zone))
        {
            $query->where('tbl.zone_id = '.$filter_zone);
        }
	    if (strlen($filter_countryid))
        {
            $query->where('z.country_id = '.$filter_countryid);
        }
	}
    
	protected function _buildQueryJoins(&$query)
	{
		$query->join('LEFT', '#__tienda_geozones AS gz ON gz.geozone_id = tbl.geozone_id');
		$query->join('LEFT', '#__tienda_zones AS z ON z.zone_id = tbl.zone_id');
		$query->join('LEFT', '#__tienda_countries AS c ON z.country_id = c.country_id');
	}
	
	protected function _buildQueryFields(&$query)
	{
		$field = array();
		$field[] = " z.zone_name";
		$field[] = " z.code AS zone_code ";
		$field[] = " z.country_id";
		$field[] = " c.country_name";
		$field[] = " gz.geozone_name";
		
		$query->select( $this->getState( 'select', 'tbl.*' ) );		
		$query->select( $field );
	}
	
	public function getList()
	{
		$list = parent::getList(); 
		
		foreach(@$list as $item)
		{
            $item->link = "index.php?option=com_tienda&controller=zonerelations&view=zonerelations&tmpl=component&task=edit&geozoneid=$item->geozone_id&id=$item->zonerelation_id";
		}
		return $list;
	}
}
