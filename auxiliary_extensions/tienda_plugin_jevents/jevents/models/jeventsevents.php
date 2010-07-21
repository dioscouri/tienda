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
    protected function _buildQueryWhere(&$query)
    {
        $filter = $this->getState('filter');
        $filter_event =$this->getState('filter_event');
        
        if (strlen($filter_event))
        {
            $query->where('tbl.ev_id = '.(int) $filter_event);
        }

        if (strlen($filter))
        {
            $key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');
            $where = array();
            $where[] = 'LOWER(eventdetails.summary) LIKE '.$key;
            $where[] = 'LOWER(eventdetails.description) LIKE '.$key;
            $query->where('('.implode(' OR ', $where).')');
        }
    }
    
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
}
