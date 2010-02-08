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

class TiendaModelOrderPayments extends TiendaModelBase 
{
    protected function _buildQueryWhere(&$query)
    {
       	$filter             = $this->getState('filter');
       	$filter_orderid     = $this->getState('filter_orderid');

       	if ($filter) 
       	{
			$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');

			$where = array();
			$where[] = 'LOWER(tbl.orderpayment_id) LIKE '.$key;
			
			$query->where('('.implode(' OR ', $where).')');
       	}
       	
        if ($filter_orderid)
        {
            $query->where('tbl.order_id = '.$filter_orderid);
        }
    }
}