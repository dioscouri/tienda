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

class TiendaModelProductAttributes extends TiendaModelBase
{
    protected function _buildQueryWhere(&$query)
    {
    	$filter          		= $this->getState('filter');
        $filter_id	     		= $this->getState('filter_id');
        $filter_product  		= $this->getState('filter_product');
        $filter_parent_option	= $this->getState('filter_parent_option');

        if ($filter) 
        {
            $key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');
            $where = array();
            $where[] = 'LOWER(tbl.productattribute_id) LIKE '.$key;
            $where[] = 'LOWER(tbl.productattribute_name) LIKE '.$key;
            $where[] = 'LOWER(tbl.product_id) LIKE '.$key;
            $query->where('('.implode(' OR ', $where).')');
        }
		if(is_array($filter_id))
		{
			foreach($filter_id as &$fid){
				$fid = (int)$fid;
			}
			$query->where('tbl.productattribute_id IN ('. implode(',', $filter_id).')');
		} 
		else 
		{
			if (strlen($filter_id))
	        {
	            $query->where('tbl.productattribute_id = '.(int) $filter_id);
	       	}			
		}
		
        if (strlen($filter_product))
        {
            $query->where('tbl.product_id = '.(int) $filter_product);
        }
    	if (is_array($filter_parent_option))
        {
       		$filter_parent_option = implode(',', $filter_parent_option);
       		$query->where('tbl.parent_productattributeoption_id IN ('. $filter_parent_option.')');
       	}
       	else
       	{
       		if(strlen($filter_parent_option)) 
        	{
            	$query->where('tbl.parent_productattributeoption_id = '.(int) $filter_parent_option);
        	}
        }
    }
    
    protected function _buildQueryFields(&$query)
    {
        $field = array();
        $field[] = " 
        (
            SELECT GROUP_CONCAT(options.productattributeoption_name ORDER BY options.ordering ASC SEPARATOR ', ')
            FROM
                #__tienda_productattributeoptions AS options 
            WHERE 
                options.productattribute_id = tbl.productattribute_id
        )
        AS option_names_csv ";
        
        $query->select( $this->getState( 'select', 'tbl.*' ) );     
        $query->select( $field );
    }
    
//    public function getList()
//    {
//        Tienda::load( "TiendaHelperProduct", 'helpers.product' );
//        $list = parent::getList(); 
//        foreach(@$list as $item)
//        {
//            $model = DSCModel::getInstance( 'ProductAttributeOptions', 'TiendaModel' );
//            $model->setState( 'filter_attribute', $item->productattribute_id );
//            $model->setState('order', 'tbl.ordering');
//            $item->productattributeoptions = $model->getList(); 
//        }
//        return $list;
//    }
}
