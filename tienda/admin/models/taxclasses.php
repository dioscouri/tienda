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

class TiendaModelTaxclasses extends TiendaModelBase 
{
    protected function _buildQueryWhere(&$query)
    {
       	$filter     = $this->getState('filter');

       	if ($filter) 
       	{
			$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');

			$where = array();
			$where[] = 'LOWER(tbl.tax_class_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.tax_class_name) LIKE '.$key;
			$where[] = 'LOWER(tbl.tax_class_description) LIKE '.$key;
			
			$query->where('('.implode(' OR ', $where).')');
       	}
    }
    
    protected function _buildQueryFields(&$query)
    {
        $field = array();
        
        // This subquery returns the default price for the product and allows for sorting by price
        $field[] = "
            (
            SELECT 
                COUNT(*)
            FROM
                #__tienda_taxrates AS rates 
            WHERE 
                rates.tax_class_id = tbl.tax_class_id 
            ) 
        AS taxrates_assigned ";
        
        $query->select( $this->getState( 'select', 'tbl.*' ) );     
        $query->select( $field );
    }
        	
	public function getList()
	{
		$list = parent::getList(); 
		foreach(@$list as $item)
		{
			$item->link = 'index.php?option=com_tienda&controller=taxclasses&view=taxclasses&task=edit&id='.$item->tax_class_id;
			$item->link_taxrates = 'index.php?option=com_tienda&view=taxclasses&task=setrates&tmpl=component&id='.$item->tax_class_id;
		}
		return $list;
	}
}
