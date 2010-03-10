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

class TiendaModelProducts extends TiendaModelBase 
{
    protected function _buildQueryWhere(&$query)
    {
       	$filter     = $this->getState('filter');
        $filter_id_from	= $this->getState('filter_id_from');
        $filter_id_to	= $this->getState('filter_id_to');
        $filter_name	= $this->getState('filter_name');
       	$enabled		= $this->getState('filter_enabled');
        $filter_quantity_from	= $this->getState('filter_quantity_from');
        $filter_quantity_to	= $this->getState('filter_quantity_to');
        $filter_category	= $this->getState('filter_category');
        $filter_sku	= $this->getState('filter_sku');
		$filter_price_from	= $this->getState('filter_price_from');
        $filter_price_to	= $this->getState('filter_price_to');
        $filter_taxclass = $this->getState('filter_taxclass');
        $filter_ships = $this->getState('filter_ships');
        $filter_date_from   = $this->getState('filter_date_from');
        $filter_date_to     = $this->getState('filter_date_to');
        $filter_datetype    = $this->getState('filter_datetype');
        $filter_published   = $this->getState('filter_published');
        $filter_published_date  = $this->getState('filter_published_date');
        
       	if ($filter) 
       	{
			$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');

			$where = array();
			$where[] = 'LOWER(tbl.product_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.product_name) LIKE '.$key;
			$where[] = 'LOWER(tbl.product_description) LIKE '.$key;
			$where[] = 'LOWER(tbl.product_sku) LIKE '.$key;
			$where[] = 'LOWER(tbl.product_model) LIKE '.$key;
			// TODO Add more filters
			// add maufacturer name
			$where[] = 'LOWER(m.manufacturer_name) LIKE '.$key;
			// add category name
			$where[] = 'LOWER(c.category_name) LIKE '.$key;
			
			$query->where('('.implode(' OR ', $where).')');
       	}
		if (strlen($enabled))
        {
        	$query->where('tbl.product_enabled = '.$enabled);
       	}
		if (strlen($filter_id_from))
        {
            if (strlen($filter_id_to))
        	{
        		$query->where('tbl.product_id >= '.(int) $filter_id_from);	
        	}
        		else
        	{
        		$query->where('tbl.product_id = '.(int) $filter_id_from);
        	}
       	}
		if (strlen($filter_id_to))
        {
        	$query->where('tbl.product_id <= '.(int) $filter_id_to);
       	}
    	if (strlen($filter_name))
        {
        	$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter_name ) ) ).'%');
        	$query->where('LOWER(tbl.product_name) LIKE '.$key);
       	}
		if (strlen($filter_quantity_from))
        {
        	$query->where('(
            SELECT 
                SUM(quantities.quantity)
            FROM
                #__tienda_productquantities AS quantities
            WHERE 
                quantities.product_id = tbl.product_id 
                AND quantities.vendor_id = 0
            ) >= '.(int) $filter_quantity_from);
       	}
		if (strlen($filter_quantity_to))
        {
        	$query->where('(
            SELECT 
                SUM(quantities.quantity)
            FROM
                #__tienda_productquantities AS quantities
            WHERE 
                quantities.product_id = tbl.product_id 
                AND quantities.vendor_id = 0
            ) <= '.(int) $filter_quantity_to);
       	}
        if (strlen($filter_sku))
        {
        	$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter_sku ) ) ).'%');
        	$query->where('LOWER(tbl.product_sku) LIKE '.$key);
       	}
       	if (strlen($filter_category))
       	{
       		$query->where('p2c.category_id = '.(int) $filter_category);
       	}
       	       	
    	if (strlen($filter_price_from))
        {
            $query->having("price >= '". $filter_price_from ."'");
       	}
		if (strlen($filter_price_to))
        {
        	$query->having("price <= '". $filter_price_to ."'");
       	}
        if (strlen($filter_taxclass))
        {
            $query->where('tbl.tax_class_id = '.(int) $filter_taxclass);
        }
        if (strlen($filter_ships))
        {
            $query->where('tbl.product_ships = '.(int) $filter_ships);
        }
        if (strlen($filter_date_from))
        {
            switch ($filter_datetype)
            {
                case "modified":
                    $query->where("tbl.modified_date >= '".$filter_date_from."'");
                  break;
                case "created":
                default:
                    $query->where("tbl.created_date >= '".$filter_date_from."'");
                  break;
            }
        }
        if (strlen($filter_date_to))
        {
            switch ($filter_datetype)
            {
                case "modified":
                    $query->where("tbl.modified_date <= '".$filter_date_to."'");
                  break;
                case "created":
                default:
                    $query->where("tbl.created_date <= '".$filter_date_to."'");
                  break;
            }
        }
        if (strlen($filter_published))
        {
        	// TODO Add this after updating the products form to add publish/unpublish date fields
        	//$query->where("(tbl.publish_date <= '".$filter_published_date."' AND (tbl.unpublish_date > '".$filter_published_date."' OR tbl.unpublish_date = '0000-00-00' ) )", 'AND' );
        }
        
    }
    
	protected function _buildQueryJoins(&$query)
	{
		$query->join('LEFT', '#__tienda_productcategoryxref AS p2c ON tbl.product_id = p2c.product_id');	
		$query->join('LEFT', '#__tienda_categories AS c ON p2c.category_id = c.category_id');
		$query->join('LEFT', '#__tienda_manufacturers AS m ON m.manufacturer_id = tbl.manufacturer_id');
	}

	protected function _buildQueryFields(&$query)
	{
		$field = array();
		if ($this->getState('filter_category'))
		{
			$field[] = " c.category_name AS category_name ";
		}
		
		// This subquery returns the default price for the product and allows for sorting by price
		$date = JFactory::getDate()->toMysql();
		$default_group = '0'; // TODO Use default user_group_id
		$field[] = "
			(
			SELECT 
				prices.product_price
			FROM
				#__tienda_productprices AS prices 
			WHERE 
				prices.product_id = tbl.product_id 
				AND prices.user_group_id = '$default_group'
				AND prices.product_price_startdate <= '$date' 
				AND (prices.product_price_enddate >= '$date' OR prices.product_price_enddate = '0000-00-00 00:00:00' )
				ORDER BY prices.price_quantity_start ASC
			LIMIT 1
			) 
		AS price ";
		
        $field[] = "
            (
            SELECT 
                SUM(quantities.quantity)
            FROM
                #__tienda_productquantities AS quantities
            WHERE 
                quantities.product_id = tbl.product_id 
                AND quantities.vendor_id = '0'
            ) 
        AS product_quantity ";
		
		$query->select( $this->getState( 'select', 'tbl.*' ) );		
		$query->select( $field );
	}
	
    protected function _buildQueryGroup(&$query)
    {
    	$query->group('tbl.product_id');
    }
        	
	public function getList()
	{
		JLoader::import( 'com_tienda.helpers.product', JPATH_ADMINISTRATOR.DS.'components' );
		$list = parent::getList(); 
		
		// If no item in the list, return an array()
        if( empty( $list ) ){
        	return array();
        }
		
		foreach($list as $item)
		{
			$item->link = 'index.php?option=com_tienda&controller=products&view=products&task=view&id='.$item->product_id;
			$item->link_edit = 'index.php?option=com_tienda&controller=products&view=products&task=edit&id='.$item->product_id;
		}
		return $list;
	}
}
