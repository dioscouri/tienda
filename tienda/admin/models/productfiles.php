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

class TiendaModelProductFiles extends TiendaModelBase
{
    protected function _buildQueryWhere(&$query)
    {
    	$filter          = $this->getState('filter');
        $filter_id	     = $this->getState('filter_id');
        $filter_enabled  = $this->getState('filter_enabled');
        $filter_product  = $this->getState('filter_product');
        $filter_purchaserequired  = $this->getState('filter_purchaserequired');

        if ($filter) 
        {
            $key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');
            $where = array();
            $where[] = 'LOWER(tbl.productfile_id) LIKE '.$key;
            $where[] = 'LOWER(tbl.productfile_name) LIKE '.$key;
            $where[] = 'LOWER(tbl.product_id) LIKE '.$key;
            $query->where('('.implode(' OR ', $where).')');
        }
		if (strlen($filter_id))
        {
            $query->where('tbl.productfile_id = '.(int) $filter_id);
       	}
        if (strlen($filter_product))
        {
            $query->where('tbl.product_id = '.(int) $filter_product);
        }
        if (strlen($filter_enabled))
        {
            $query->where('tbl.productfile_enabled = '.(int) $filter_enabled);
        }
        if (strlen($filter_purchaserequired))
        {
            $query->where('tbl.purchase_required = '.(int) $filter_purchaserequired);
        }
    }
}
