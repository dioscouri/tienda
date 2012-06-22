<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaModelBase', 'models._base' );

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
       	
       	$id = $this->getState( 'tax_class_id' );
      if( strlen( $id ) )
      	$where []= ' tbl.tax_class_id = '.( int )$id; 
    }
    
    protected function _buildQueryFields(&$query)
    {
        $field = array();
        
        $field[] = "
            (
            SELECT 
                COUNT(rates.tax_rate_id)
            FROM
                #__tienda_taxrates AS rates 
            WHERE 
                rates.tax_class_id = tbl.tax_class_id 
            ) 
        AS taxrates_assigned ";
        
        $query->select( $this->getState( 'select', 'tbl.*' ) );     
        $query->select( $field );
    }
        	
	public function getList($refresh = false)
	{
		$list = parent::getList(); 
		
		// If no item in the list, return an array()
        if( empty( $list ) ){
        	return array();
        }
		
		foreach($list as $item)
		{
			$item->link = 'index.php?option=com_tienda&controller=taxclasses&view=taxclasses&task=edit&id='.$item->tax_class_id;
			$item->link_taxrates = 'index.php?option=com_tienda&view=taxclasses&task=setrates&tmpl=component&id='.$item->tax_class_id;
		}
		return $list;
	}
}
