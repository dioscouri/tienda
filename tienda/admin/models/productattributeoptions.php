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

class TiendaModelProductAttributeOptions extends TiendaModelBase
{
    protected function _buildQueryWhere(&$query)
    {
        $filter          	= $this->getState('filter');
        $filter_id      	= $this->getState('filter_id');
        $filter_attribute   = $this->getState('filter_attribute');
        $filter_parent		= $this->getState('filter_parent');

        if ($filter) 
        {
            $key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');
            $where = array();
            $where[] = 'LOWER(tbl.productattributeoption_id) LIKE '.$key;
            $where[] = 'LOWER(tbl.productattributeoption_name) LIKE '.$key;
            $where[] = 'LOWER(tbl.productattribute_id) LIKE '.$key;
            $query->where('('.implode(' OR ', $where).')');
        }
        if (strlen($filter_id))
        {
            $query->where('tbl.productattributeoption_id = '.(int) $filter_id);
        }
        if (strlen($filter_attribute))
        {
            $query->where('tbl.productattribute_id = '.(int) $filter_attribute);
        }
   		if (is_array($filter_parent))
        {
       		$filter_parent = implode(',', $filter_parent);
       		$query->where('tbl.parent_productattributeoption_id IN ('. $filter_parent.')');
       	}
       	else
       	{
       		if(strlen($filter_parent)) 
        	{
            	$query->where('tbl.parent_productattributeoption_id = '.(int) $filter_parent);
        	}
        }
    }

		protected function _buildQueryJoins(&$query)
		{
			$query->join('LEFT', '#__tienda_productattributes AS pa ON pa.productattribute_id = tbl.productattribute_id');
			$query->join('LEFT', '#__tienda_products AS p ON pa.product_id = p.product_id');
		}	
    
    protected function _buildQueryFields( &$query )
    {
    		$fields = array();
       	$fields[] = "tbl.*";
       	$fields[] = "p.product_id, p.product_ships";
        $query->select( $fields );
    }

    public function getNames( $ids )
    {
        $return = array();
         
        $query = $this->getDBO()->getQuery(true);
    
        $ids = (array) $ids;
        $filter_id_set = implode("', '", $ids);
    
        $query->select( "DISTINCT(pao.productattributeoption_name)" );
        $query->from( "#__tienda_productattributeoptions AS pao" );
        $query->where( "pao.productattributeoption_id IN ('" . $filter_id_set . "')" );
    
        $db = $this->getDBO();
        $db->setQuery((string) $query);
    
        $return = $db->loadColumn();
        sort($return);
         
        return $return;
    }
}
