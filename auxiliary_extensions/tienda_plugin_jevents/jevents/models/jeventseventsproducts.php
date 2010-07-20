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
