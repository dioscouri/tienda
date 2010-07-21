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

class TiendaModelJEventsEventsProducts extends TiendaModelBase
{
    protected function _buildQueryWhere(&$query)
    {
        $filter_product = $this->getState('filter_product');
        $filter_productname = $this->getState('filter_productname');
        $filter_event = $this->getState('filter_event');
        $filter_eventsummary = $this->getState('filter_eventsummary');


        if (strlen($filter_product))
        {
            $query->where('product.product_id = '.(int) $filter_product);
        }

        if (strlen($filter_productname))
        {
            $key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter_productname ) ) ).'%');
            $query->where('LOWER(product.product_name) LIKE '.$key);
        }

            
        if (strlen($filter_event))
        {
            $query->where('event.ev_id = '.(int) $filter_event);
        }

        if (strlen($filter_eventsummary))
        {
            $key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter_productname ) ) ).'%');
            $query->where('LOWER(eventdetails.summary) LIKE '.$key);
        }

    }
    
	protected function _buildQueryJoins(&$query)
	{
		$query->join('LEFT', '#__tienda_products AS product ON product.product_id = tbl.product_id');
		$query->join('LEFT', '#__jevents_vevent AS event ON event.ev_id = tbl.event_id');
		$query->join('LEFT', '#__jevents_vevdetail AS eventdetails ON eventdetails.evdet_id = event.detail_id');
	}

	protected function _buildQueryFields(&$query)
	{
		$fields = array();
		$fields[] = " tbl.* ";
		$fields[] = " product.* ";
		$fields[] = " event.* ";
		$fields[] = " eventdetails.* ";
		$query->select( $fields );
	}
}
