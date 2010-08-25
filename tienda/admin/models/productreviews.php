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

class TiendaModelProductReviews extends TiendaModelBase
{
	protected function _buildQueryWhere(&$query)
	{
		$filter_product  = $this->getState('filter_product');
		$filter_name	= $this->getState('filter_name');
		if (strlen($filter_product))
        {
            $query->where('tbl.product_id = '.(int) $filter_product);
        }
        if(strlen($filter_name))
        {
        	$query->where('m.name = '.(int) $filter_name);
        }
	}
	protected function _buildQueryJoins(&$query)
	{
		
		$query->join('LEFT', '#__tienda_products AS p ON p.product_id = tbl.product_id');
		$query->join('LEFT', '#__users AS m ON m.id = tbl.userid');		
	}
	
	protected function _buildQueryFields(&$query)
	{
		$field = array();
		
		$field[] = " p.product_name AS product_name ";
		
		$field[] = " m.name AS user_name ";
		
		
		// This subquery returns the default price for the product and allows for sorting by price
		
		
		$query->select( $this->getState( 'select', 'tbl.*' ) );		
		$query->select( $field );
	}
	
	public function getList()
	{
		
		$list = parent::getList(); 
        return $list;
	}
}