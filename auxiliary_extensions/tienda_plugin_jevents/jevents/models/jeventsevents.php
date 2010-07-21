<?php
/**
 * @version 1.5
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaModelBase', 'models._base' );

class TiendaModelJEventsEvents extends TiendaModelBase
{
	protected function _buildQueryJoins(&$query)
	{
		$query->join('LEFT', '#__jevents_vevdetail AS eventdetails ON eventdetails.evdet_id = tbl.detail_id');
	}

	protected function _buildQueryFields(&$query)
	{
		$fields = array();
		$fields[] = " tbl.* ";
		$fields[] = " eventdetails.* ";

		$query->select( $fields );
	}

	protected function _buildQueryWhere(&$query)
	{
		$filter_event =$this->getState('filter_event');
		$filter_eventsummary =$this->getState('filter');
		if (strlen($filter_event))
		{
			$query->where('tbl.ev_id = '.(int) $filter_event);
		}

		if (strlen($filter_eventsummary))
		{
			$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter_eventsummary ) ) ).'%');
			$query->where('LOWER(eventdetails.summary) LIKE '.$key);
		}

	}

}
