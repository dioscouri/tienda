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
defined( '_JEXEC' ) or die( 'Restricted access' );

Tienda::load( 'TiendaModelEav', 'models._baseeav' );

class TiendaModelProducts extends TiendaModelEav
{
    protected function _buildQueryWhere( &$query )
    {
        $filter = $this->getState( 'filter' );
        $filter_id_from = $this->getState( 'filter_id_from' );
        $filter_id_to = $this->getState( 'filter_id_to' );
        $filter_id_set = $this->getState( 'filter_id_set' );
        $filter_name = $this->getState( 'filter_name', '', 'string' );
        $filter_enabled = $this->getState( 'filter_enabled' );
        $filter_quantity_from = $this->getState( 'filter_quantity_from' );
        $filter_quantity_to = $this->getState( 'filter_quantity_to' );
        $filter_category = $this->getState( 'filter_category' );
        $filter_sku = $this->getState( 'filter_sku', '', 'string' );
        $filter_price_from = $this->getState( 'filter_price_from' );
        $filter_price_to = $this->getState( 'filter_price_to' );
        $filter_taxclass = $this->getState( 'filter_taxclass' );
        $filter_ships = $this->getState( 'filter_ships' );
        $filter_date_from = $this->getState( 'filter_date_from' );
        $filter_date_to = $this->getState( 'filter_date_to' );
        $filter_datetype = $this->getState( 'filter_datetype', '', 'word' );
        $filter_published = $this->getState( 'filter_published' );
        $filter_published_date = $this->getState( 'filter_published_date' );
        $filter_manufacturer = $this->getState( 'filter_manufacturer' );
        $filter_manufacturer_set = $this->getState( 'filter_manufacturer_set' );
        $filter_multicategory = $this->getState( 'filter_multicategory' );
        $filter_description = $this->getState( 'filter_description' );
        $filter_description_short = $this->getState( 'filter_description_short' );
        $filter_namedescription = $this->getState( 'filter_namedescription' );
        $filter_attribute_set = $this->getState( 'filter_attribute_set' );
        $filter_rating = $this->getState( 'filter_rating' );

        if ( $filter )
        {
            $key = $this->_db->Quote( '%' . $this->_db->getEscaped( trim( strtolower( $filter ) ) ) . '%' );
            	
            $where = array( );
            $where[] = 'LOWER(tbl.product_id) LIKE ' . $key;
            $where[] = 'LOWER(tbl.product_name) LIKE ' . $key;
            $where[] = 'LOWER(tbl.product_description) LIKE ' . $key;
            $where[] = 'LOWER(tbl.product_description_short) LIKE ' . $key;
            $where[] = 'LOWER(tbl.product_sku) LIKE ' . $key;
            $where[] = 'LOWER(tbl.product_model) LIKE ' . $key;
            $where[] = 'LOWER(m.manufacturer_name) LIKE ' . $key;
            $where[] = 'LOWER(c.category_name) LIKE ' . $key;
            	
            $query->where( '(' . implode( ' OR ', $where ) . ')' );
        }

        if ( $filter_namedescription )
        {
            $key = $this->_db->Quote( '%' . $this->_db->getEscaped( trim( strtolower( $filter_namedescription ) ) ) . '%' );
            $where = array( );
            $where[] = 'LOWER(tbl.product_name) LIKE ' . $key;
            $where[] = 'LOWER(tbl.product_description) LIKE ' . $key;
            $where[] = 'LOWER(tbl.product_description_short) LIKE ' . $key;
            $query->where( '(' . implode( ' OR ', $where ) . ')' );
        }

        if ( strlen( $filter_enabled ) )
        {
            $query->where( 'tbl.product_enabled = ' . $this->_db->Quote( $filter_enabled ) );
        }
        if ( strlen( $filter_id_from ) )
        {
            if ( strlen( $filter_id_to ) )
            {
                $query->where( 'tbl.product_id >= ' . ( int ) $filter_id_from );
            }
            else
            {
                $query->where( 'tbl.product_id = ' . ( int ) $filter_id_from );
            }
        }

        if ( $filter_rating )
        {
            $query->where( 'tbl.product_rating >= ' . ( float ) $filter_rating );
        }

        if ( strlen( $filter_id_to ) )
        {
            $query->where( 'tbl.product_id <= ' . ( int ) $filter_id_to );
        }
        if ( strlen( $filter_id_set ) )
        {
            $query->where( 'tbl.product_id IN (' . $filter_id_set . ')' );
        }
        if ( strlen( $filter_name ) )
        {
            $key = $this->_db->Quote( '%' . $this->_db->getEscaped( trim( strtolower( $filter_name ) ) ) . '%' );
            $query->where( 'LOWER(tbl.product_name) LIKE ' . $key );
        }
        if ( strlen( $filter_quantity_from ) )
        {
            $query
            ->where(
                    "
                    (
                    tbl.product_check_inventory = '0' OR
                    (
                    (
                    SELECT
                    SUM(quantities.quantity)
                    FROM
                    #__tienda_productquantities AS quantities
                    WHERE
                    quantities.product_id = tbl.product_id
                    AND quantities.vendor_id = 0
            ) >= '" . ( int ) $filter_quantity_from
                    . "'
                    AND
                    tbl.product_check_inventory = '1'
            )
            )
                    " );
        }
        if ( strlen( $filter_quantity_to ) )
        {
            $query
            ->where(
                    '(
                    SELECT
                    SUM(quantities.quantity)
                    FROM
                    #__tienda_productquantities AS quantities
                    WHERE
                    quantities.product_id = tbl.product_id
                    AND quantities.vendor_id = 0
            ) <= ' . ( int ) $filter_quantity_to );
        }
        if ( strlen( $filter_sku ) )
        {
            $key = $this->_db->Quote( '%' . $this->_db->getEscaped( trim( strtolower( $filter_sku ) ) ) . '%' );
            	
            // Check also the pao for codes
            $where = array( );
            $where[] = 'LOWER(tbl.product_sku) LIKE ' . $key;
            $where[] = 'LOWER(pao.productattributeoption_code) LIKE ' . $key;
            $query->where( '(' . implode( ' OR ', $where ) . ')' );
        }

        if ( strlen( $filter_attribute_set ) )
        {
            $query->where( 'pa.productattribute_id IN(' . $filter_attribute_set . ')' );
        }

        if ( $filter_category == 'none' )
        {
            $query->where( "NOT EXISTS (SELECT * FROM #__tienda_productcategoryxref AS p2c WHERE tbl.product_id = p2c.product_id)" );
        }
        elseif ( strlen( $filter_category ) )
        {
            $query->where( 'p2c.category_id = ' . ( int ) $filter_category );
        }

        if ( strlen( $filter_price_from ) )
        {
            $query->having( "price >= '" . ( int ) $filter_price_from . "'" );
        }
        if ( strlen( $filter_price_to ) )
        {
            $query->having( "price <= '" . ( int ) $filter_price_to . "'" );
        }
        if ( strlen( $filter_taxclass ) )
        {
            $query->where( 'tbl.tax_class_id = ' . ( int ) $filter_taxclass );
        }
        if ( strlen( $filter_ships ) )
        {
            $query->where( 'tbl.product_ships = ' . ( int ) $filter_ships );
        }
        if ( strlen( $filter_date_from ) )
        {
            switch ( $filter_datetype )
            {
                case "modified":
                    $query->where( "tbl.modified_date >= '" . $filter_date_from . "'" );
                    break;
                case "created":
                default:
                    $query->where( "tbl.created_date >= '" . $filter_date_from . "'" );
                    break;
            }
        }

        if ( strlen( $filter_date_to ) )
        {
            switch ( $filter_datetype )
            {
                case "modified":
                    $query->where( "tbl.modified_date <= '" . $filter_date_to . "'" );
                    break;
                case "created":
                default:
                    $query->where( "tbl.created_date <= '" . $filter_date_to . "'" );
                    break;
            }
        }

        if ( $filter_manufacturer )
        {
            $query->where( 'tbl.manufacturer_id = ' . ( int ) $filter_manufacturer );
        }

        if ( strlen( $filter_manufacturer_set ) )
        {
            $query->where( 'tbl.manufacturer_id IN(' . $filter_manufacturer_set . ')' );
        }


        if ( strlen( $filter_published ) )
        {
            // TODO Add this after updating the products form to add publish/unpublish date fields
            $query
            ->where(
                    "(tbl.publish_date <= '" . $filter_published_date . "' AND (tbl.unpublish_date > '" . $filter_published_date
                    . "' OR tbl.unpublish_date = '0000-00-00' ) )", 'AND' );
        }

        // Creating the in clause for the case of the multiple category filter
        $in_category_clause = "";
        foreach ( ( ( array ) $filter_multicategory ) as $category )
        {
            if ( strlen( $category ) )
            {
                $in_category_clause = $in_category_clause . $category . ",";

            }
        }
        if ( $in_category_clause != "" )
        {
            $in_category_clause = substr( $in_category_clause, 0, -1 );
            $query->where( 'p2c.category_id IN(' . $in_category_clause . ')' );
        }
        if ( strlen( $filter_description ) )
        {
            $key = $this->_db->Quote( '%' . $this->_db->getEscaped( trim( strtolower( $filter_description ) ) ) . '%' );
            $query->where( 'LOWER(tbl.product_description) LIKE ' . $key );
        }

    }

    protected function _buildQueryJoins( &$query )
    {
        $query->join( 'LEFT', '#__tienda_productcategoryxref AS p2c ON p2c.product_id = tbl.product_id' );
        $query->join( 'LEFT', '#__tienda_categories AS c ON p2c.category_id = c.category_id' );
        $query->join( 'LEFT', '#__tienda_manufacturers AS m ON m.manufacturer_id = tbl.manufacturer_id' );

        $filter_sku = $this->getState( 'filter_sku', '', 'string' );
        $filter_attribute_set = $this->getState( 'filter_attribute_set' );
        if ( strlen( $filter_sku ) || strlen($filter_attribute_set) )
        {
            // Check also the pao for codes
            $query->join( 'LEFT', '#__tienda_productattributes AS pa ON pa.product_id = tbl.product_id' );
            	
            if(strlen( $filter_sku ))
            {
                $query->join( 'LEFT', '#__tienda_productattributeoptions AS pao ON pa.productattribute_id = pao.productattribute_id' );
            }
        }
    }

    protected function _buildQueryFields( &$query )
    {
        $field = array( );
        if ( $this->getState( 'filter_category' ) )
        {
            $field[] = " c.category_name AS category_name ";
        }

        $field[] = " m.manufacturer_name AS manufacturer_name ";

        // This subquery returns the default price for the product and allows for sorting by price
        $date = JFactory::getDate( )->toMysql( );

        $default_group = Tienda::getInstance( )->get( 'default_user_group', '1' );
        $filter_group = ( int ) $this->getState( 'filter_group' );
        if ( empty( $filter_group ) )
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

    protected function _buildQueryGroup( &$query )
    {
        $query->group( 'tbl.product_id' );
    }

    /**
     * Remove the normal order clause since on large db it makes it heavierfor nothing
     */
    protected function _buildQueryOrder(&$query)
    {
        $order      = $this->_db->getEscaped( $this->getState('order') );
       	$direction  = $this->_db->getEscaped( strtoupper( $this->getState('direction') ) );

        if ($order)
        {
            $query->order("$order $direction");
        }
    }

    /**
     * Builds a generic SELECT COUNT(*) query that takes group by into account
     */
    protected function _buildResultQuery( )
    {
        $grouped_query = new TiendaQuery( );
        $grouped_query->select( $this->getState( 'select', 'COUNT(tbl.product_id)' ) );

        $field = array( );
        $filter_quantity_from = $this->getState( 'filter_quantity_from' );
        $filter_quantity_to = $this->getState( 'filter_quantity_to' );
        $filter_price_from = $this->getState( 'filter_price_from' );
        $filter_price_to = $this->getState( 'filter_price_to' );

        if ( strlen( $filter_price_from ) || strlen( $filter_price_to ) )
        {
            // This subquery returns the default price for the product and allows for sorting by price
            $date = JFactory::getDate( )->toMysql( );
            	
            $default_group = Tienda::getInstance( )->get( 'default_user_group', '1' );
            $filter_group = ( int ) $this->getState( 'filter_group' );
            if ( empty( $filter_group ) )
            {
                $filter_group = $default_group;
            }
            	
            $field[] = "(
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
            AS price";
        }

        if ( strlen( $filter_quantity_from ) || strlen( $filter_quantity_to ) )
        {
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
        }

        if ( count( $field ) )
        {
            $grouped_query->select( $field );
        }

        $this->_buildQueryFrom( $grouped_query );
        $this->_buildQueryJoins( $grouped_query );
        $this->_buildQueryWhere( $grouped_query );
        $this->_buildQueryGroup( $grouped_query );
        $this->_buildQueryHaving( $grouped_query );

        $query = new TiendaQuery( );
        $query->select( 'COUNT(*)' );
        $query->from( '(' . $grouped_query . ') as grouped_count' );

        // Allow plugins to edit the query object
        $suffix = ucfirst( $this->getName( ) );
        $dispatcher = JDispatcher::getInstance( );
        $dispatcher->trigger( 'onAfterBuildResultQuery' . $suffix, array(
                &$query
        ) );

        return $query;
    }
    
    /**
     * Set basic properties for the item, whether in a list or a singleton
     *
     * @param unknown_type $item
     * @param unknown_type $key
     * @param unknown_type $refresh
     */
    protected function prepareItem( &$item, $key=0, $refresh=false )
    {
        Tienda::load( "TiendaHelperProduct", 'helpers.product' );
        Tienda::load( 'TiendaHelperSubscription', 'helpers.subscription' );
        
        if ( !empty( $item->product_recurs ) )
        {
            $item->recurring_price = $item->price;
            if( $item->subscription_prorated )
            {
                Tienda::load( 'TiendaHelperSubscription', 'helpers.subscription' );
                $result = TiendaHelperSubscription::calculateProRatedTrial( $item->subscription_prorated_date,
                        $item->subscription_prorated_term,
                        $item->recurring_period_unit,
                        $item->recurring_trial_price,
                        $item->subscription_prorated_charge
                );
                $item->price = $result['price'];
                $item->prorated_price = $result['price'];
                $item->prorated_interval = $result['interval'];
                $item->prorated_unit = $result['unit'];
                // $item->recurring_trial = $result['trial'];
            }
                else 
            {
                if ( !empty( $item->recurring_trial ) )
                {
                    $item->price = $item->recurring_trial_price;
                }
            }
        }
         
        $item->product_parameters = new DSCParameter( $item->product_params );
        $item->slug = $item->product_alias ? ":$item->product_alias" : "";
        $item->link = 'index.php?option=com_tienda&view=products&task=view&id=' . $item->product_id;
        $item->link_edit = 'index.php?option=com_tienda&view=products&task=edit&id=' . $item->product_id;

        parent::prepareItem( $item, $key, $refresh );
    }
    
	/**
	 * Clean the cache
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function clearCache()
	{
	    parent::clearCache();
	    self::clearCacheAuxiliary();
	}
	
	/**
	 * Clean the cache
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function clearCacheAuxiliary()
	{
	    DSCModel::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_tienda/models' );
	
	    $model = DSCModel::getInstance('ProductAttributeOptions', 'TiendaModel');
	    $model->clearCache();
	     
	    $model = DSCModel::getInstance('ProductAttributeOptionValues', 'TiendaModel');
	    $model->clearCache();
	     
	    $model = DSCModel::getInstance('ProductAttributes', 'TiendaModel');
	    $model->clearCache();
	     
	    $model = DSCModel::getInstance('ProductCompare', 'TiendaModel');
	    $model->clearCache();
	     
	    $model = DSCModel::getInstance('ProductFiles', 'TiendaModel');
	    $model->clearCache();
	     
	    $model = DSCModel::getInstance('ProductPrices', 'TiendaModel');
	    $model->clearCache();
	     
	    $model = DSCModel::getInstance('ProductRelations', 'TiendaModel');
	    $model->clearCache();
	}

}
