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

class TiendaModelProductAttributeOptionValues extends TiendaModelBase
{
    protected function _buildQueryWhere(&$query)
    {
        $filter          	= $this->getState('filter');
        $filter_id      	= $this->getState('filter_id');
        $filter_option   = $this->getState('filter_option');

        if ($filter) 
        {
            $key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');
            $where = array();
            $where[] = 'LOWER(tbl.productattributeoption_id) LIKE '.$key;
            $where[] = 'LOWER(tbl.productattributeoptionvalue_id) LIKE '.$key;
            $where[] = 'LOWER(tbl.productattributeoptionvalue_value) LIKE '.$key;
            $query->where('('.implode(' OR ', $where).')');
        }
        if (strlen($filter_id))
        {
            $query->where('tbl.productattributeoptionvalue_id = '.(int) $filter_id);
        }
        if (strlen($filter_option))
        {
            $query->where('tbl.productattributeoption_id = '.(int) $filter_option);
        }
    }
}
