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

class TiendaModelProductCompare extends TiendaModelBase
{
	protected function _buildQueryWhere(&$query)
    {
        $filter_user     = $this->getState('filter_user');
        $filter_session  = $this->getState('filter_session');
        $filter_product  = $this->getState('filter_product');
		$filter_date_from	= $this->getState('filter_date_from');
        $filter_date_to		= $this->getState('filter_date_to');
		$filter_name	= $this->getState('filter_name');

        if (strlen($filter_user))
        {
            $query->where('tbl.user_id = '.$this->_db->Quote($filter_user));
        }

        if (strlen($filter_session))
        {
            $query->where( "tbl.session_id = ".$this->_db->Quote($filter_session));
        }

        if (!empty($filter_product))
        {
            $query->where('tbl.product_id = '.(int) $filter_product);
            $this->setState('limit', 1);
       	}

       	if (strlen($filter_date_from))
        {
        	$query->where("tbl.last_updated >= '".$filter_date_from."'");
       	}

		if (strlen($filter_date_to))
        {
   			$query->where("tbl.last_updated <= '".$filter_date_to."'");
       	}

       	if (strlen($filter_name))
        {
        	$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter_name ) ) ).'%');
        	$query->where('LOWER(p.product_name) LIKE '.$key);
       	}
    }

    protected function _buildQueryJoins(&$query)
    {
        $query->join('LEFT', '#__tienda_products AS p ON tbl.product_id = p.product_id');
        $query->join( 'LEFT', '#__tienda_manufacturers AS m ON m.manufacturer_id = p.manufacturer_id' );
	}

	protected function _buildQueryFields(&$query)
	{
       	$field = array();
        $field[] = " p.product_name ";
        $field[] = " p.product_sku ";
        $field[] = " p.product_model ";
        $field[] = " p.product_full_image ";
        $field[] = " p.product_ships ";
        $field[] = " p.product_weight ";
        $field[] = " p.product_length ";
        $field[] = " p.product_width ";
        $field[] = " p.product_height ";
        $field[] = " p.product_recurs ";
        $field[] = " p.product_enabled ";
        $field[] = " p.product_notforsale ";   
        $field[] = " p.product_rating ";
        $field[] = " p.product_comments ";
        $field[] = " m.manufacturer_name AS manufacturer_name ";	

		// This subquery returns the default price for the product and allows for sorting by price
		$date = JFactory::getDate()->toMysql();
		
		$default_group = TiendaConfig::getInstance()->get('default_user_group', '1');
		$filter_group = (int) $this->getState('filter_group');
		
		if (empty($filter_group))
		{
		    $filter_group = $default_group;
		}
		
		$field[] = "
			(
			SELECT
				prices.product_price
			FROM
				#__tienda_productprices AS prices
			WHERE
				prices.product_id = tbl.product_id
				AND prices.group_id = '$filter_group'
				AND prices.product_price_startdate <= '$date'
				AND (prices.product_price_enddate >= '$date' OR prices.product_price_enddate = '0000-00-00 00:00:00' )
				ORDER BY prices.price_quantity_start ASC
			LIMIT 1
			)
		AS product_price ";

        $query->select( $this->getState( 'select', 'tbl.*' ) );
        $query->select( $field );
	}
		      
    /**
     * 
     * Enter description here ...
     * @return unknown_type
     */
    public function deleteExpiredSessionProductCompared()
    {
        $db = JFactory::getDBO();

        Tienda::load( 'TiendaQuery', 'library.query' );
        Tienda::load( "TiendaHelperBase", 'helpers._base' );
        $helper = new TiendaHelperBase();                
        $query = new TiendaQuery();
        
        $query->select( "tbl.session_id" );
        $query->from( "#__session AS tbl" );
        $db->setQuery( (string) $query );
        $results = $db->loadAssocList();
        $session_ids = $helper->getColumn($results, 'session_id');        
        
        $query = new TiendaQuery();        
        $query->delete();
        $query->from( "#__tienda_productcompare" );
        $query->where( "`user_id` = '0'" );
        $query->where( "`session_id` NOT IN('" . implode( "', '", $session_ids) . "')" );

        $db->setQuery( (string) $query );
        if (!$db->query())
        {
            $this->setError( $db->getErrorMsg() );
            return false;
        }
        
        $date = JFactory::getDate();
        $now = $date->toMySQL();
        
        // Update config to say this has been done already
        JTable::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_tienda/tables' );
        $config = JTable::getInstance( 'Config', 'TiendaTable' );
        $config->load( array( 'config_name'=>'last_deleted_expired_sessionproductscompared') );
        $config->config_name = 'last_deleted_expired_sessionproductscompared';
        $config->value = $now;
        $config->save();
        return true;
    }
}