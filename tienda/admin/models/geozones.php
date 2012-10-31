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

class TiendaModelGeozones extends TiendaModelBase
{
	protected function _buildQueryWhere(&$query)
	{
		$filter = $this->getState('filter');
		$filter_id_from = $this->getState('filter_id_from');
		$filter_id_to   = $this->getState('filter_id_to');
		$filter_name    = $this->getState('filter_name');
		$filter_geozonetype    = $this->getState('filter_geozonetype');

		if ($filter)
		{
			$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');
			$where = array();
			$where[] = 'LOWER(tbl.geozone_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.geozone_name) LIKE '.$key;
			$where[] = 'LOWER(tbl.geozone_description) LIKE '.$key;
			$where[] = 'LOWER(tbl.geozonetype_id) LIKE '.$key;
			$query->where('('.implode(' OR ', $where).')');
		}
		if (strlen($filter_id_from))
		{
			if (strlen($filter_id_to))
			{
				$query->where('tbl.geozone_id >= '.(int) $filter_id_from);
			}
			else
			{
				$query->where('tbl.geozone_id = '.(int) $filter_id_from);
			}
		}
		if (strlen($filter_id_to))
		{
			$query->where('tbl.geozone_id <= '.(int) $filter_id_to);
		}
		if ($filter_name)
		{
			$key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter_name ) ) ).'%');
			$where = array();
			$where[] = 'LOWER(tbl.geozone_name) LIKE '.$key;
			$query->where('('.implode(' OR ', $where).')');
		}
		if (strlen($filter_geozonetype))
		{
			$query->where('tbl.geozonetype_id = '.$this->_db->Quote($filter_geozonetype));
		}

	}

	protected function _buildQueryJoins(&$query)
	{
		$query->join('LEFT', '#__tienda_geozonetypes AS t ON t.geozonetype_id = tbl.geozonetype_id');
	}

	protected function _buildQueryFields(&$query)
	{
		$field = array();
		$field[] = " t.geozonetype_name";

		$query->select( $this->getState( 'select', 'tbl.*' ) );
		$query->select( $field );
	}

	public function getList($refresh = false)
	{
		$list = parent::getList($refresh);

		// If no item in the list, return an array()
		if( empty( $list ) ){
			return array();
		}

		foreach(@$list as $item)
		{
			$item->link = 'index.php?option=com_tienda&controller=geozones&view=geozones&task=edit&id='.$item->geozone_id;
			$item->link_zones = 'index.php?option=com_tienda&view=geozones&task=selectzones&tmpl=component&id='.$item->geozone_id;
			$item->link_plugins = 'index.php?option=com_tienda&view=geozones&task=selectplugins&type='.$item->geozonetype_id.'&tmpl=component&id='.$item->geozone_id;
		}
		return $list;
	}
	
	/**
	 * Clean the cache
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function clearCache()
	{
	    parent::clearCache();
	    self::clearCacheAuxiliary();
	}
	
	/**
	 * Clean the cache
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function clearCacheAuxiliary()
	{
	    DSCModel::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_tienda/models' );
	
	    $model = DSCModel::getInstance('ZoneRelations', 'TiendaModel');
	    $model->clearCache();
	
	    $model = DSCModel::getInstance('Zones', 'TiendaModel');
	    $model->clearCache();
	}
}
