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

class TiendaModelProductComments extends TiendaModelBase
{
	protected function _buildQueryWhere(&$query)
	{
		$filter_product  = $this->getState('filter_product');
		$filter_id_from	= $this->getState('filter_id_from');
        $filter_id_to	= $this->getState('filter_id_to');
        $filter_name	= $this->getState('filter_name');
        $filter_enabled		= $this->getState('filter_enabled');
        $filter_reported     = $this->getState('filter_reported');
        
		if (strlen($filter_product))
        {
            $query->where('tbl.product_id = '.(int) $filter_product);
        }
		if (strlen($filter_id_from))
        {
        	if (strlen($filter_id_to))
        	{
        		$query->where('tbl.productcomment_id >= '.(int) $filter_id_from);
        	}
        		else
        	{
        		$query->where('tbl.productcomment_id = '.(int) $filter_id_from);
        	}
       	}
       	
		if (strlen($filter_id_to))
        {
        	$query->where('tbl.productcomment_id <= '.(int) $filter_id_to);
       	}
       	
        if(strlen($filter_name))
        {
        	$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter_name ) ) ).'%');
        	$query->where('LOWER(p.product_name) LIKE '.$key);
        }
        
		if (strlen($filter_enabled))
        {
        	$query->where('tbl.productcomment_enabled = '.$this->_db->Quote($filter_enabled));
       	}

       	if (strlen($filter_reported))
        {
            if ($filter_reported > 0)
            {
                $query->where('tbl.reported_count > 0');
            }
            else
            {
                $query->where('tbl.reported_count = 0');
            }
        }
	}
	
	/**
	 * for joining tables products and users
	 */
	protected function _buildQueryJoins(&$query)
	{
		$query->join('LEFT', '#__tienda_products AS p ON p.product_id = tbl.product_id');
		$query->join('LEFT', '#__users AS m ON m.id = tbl.user_id');
	}
	
	protected function _buildQueryFields(&$query)
	{
		$field = array();
		
		$field[] = " p.product_name AS product_name ";
		//$field[] = " m.name AS user_name ";
		
		$query->select( $this->getState( 'select', 'tbl.*' ) );		
		$query->select( $field );
		
	}
	
	public function getList()
	{
		$list = parent::getList();
		if(empty($list))
		{
			return $list;	
		}
		foreach($list as $item)
		{
		    
			$item->link = 'index.php?option=com_tienda&view=productcomments&task=edit&id='.$item->productcomment_id; 
		} 
        return $list;
	}
}