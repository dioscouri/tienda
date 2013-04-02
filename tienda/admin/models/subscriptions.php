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

class TiendaModelSubscriptions extends TiendaModelBase 
{
    protected function _buildQueryWhere(&$query)
    {
       	$filter 			   = $this->getState('filter');
       	$filter_subscriptionid = $this->getState('filter_subscriptionid');
        $filter_userid 		   = $this->getState('filter_userid');
        $filter_user 		   = $this->getState('filter_user');
        $filter_orderid 	   = $this->getState('filter_orderid');
        $filter_orderitemid    = $this->getState('filter_orderitemid');
        $filter_enabled 	   = $this->getState('filter_enabled');
        $filter_productid 	   = $this->getState('filter_productid');
        $filter_productname    = $this->getState('filter_type');
        $filter_transactionid  = $this->getState('filter_transactionid');
        $filter_date_from      = $this->getState('filter_date_from');
        $filter_date_to        = $this->getState('filter_date_to');
        $filter_datetype       = $this->getState('filter_datetype');
        $filter_lifetime       = $this->getState('filter_lifetime');
        $filter_id_from        = $this->getState('filter_id_from');
        $filter_id_to          = $this->getState('filter_id_to');
        $filter_orderstate     = $this->getState('filter_orderstate');
        $filter_subnum         = $this->getState('filter_subnum');
        
       	if ($filter) 
       	{
       		$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');
					$where = array();
					$where[] = 'LOWER(tbl.subscription_id) LIKE '.$key;
					$query->where('('.implode(' OR ', $where).')');
       	}

        if (strlen($filter_id_from))
        {
        	if (strlen($filter_id_to))
        	{
        		$query->where('tbl.subscription_id >= '.(int) $filter_id_from);
        	}
        		else
        	{
        		$query->where('tbl.subscription_id = '.(int) $filter_id_from);
        	}
       	}
       	if (strlen($filter_id_to))
        {
        	$query->where('tbl.subscription_id <= '.(int) $filter_id_to);
       	}

        if (strlen($filter_subscriptionid))
        {
            $query->where('tbl.subscription_id = '.$this->_db->Quote($filter_subscriptionid));
        }
        
        if (strlen($filter_transactionid))
        {
            $query->where('tbl.transaction_id LIKE '.$this->_db->Quote('%'.$filter_transactionid.'%'));
        }

        if (strlen($filter_productname))
        {
            $query->where('p.product_name LIKE '.$this->_db->Quote('%'.$filter_productname.'%'));
        }
        
        
        if (strlen($filter_userid))
        {
            $query->where('tbl.user_id = '.$this->_db->Quote($filter_userid));
        }

        if (strlen($filter_user))
        {
        	if( strcmp((int)$filter_user,$filter_user ) )
            $query->where('u.username LIKE '.$this->_db->Quote('%'.$filter_user.'%'));
        	else
           	$query->where('tbl.user_id = '.$this->_db->Quote($filter_user));
        }

       	if (strlen($filter_subnum))
        {
        	$query->where('tbl.sub_number LIKE '.$this->_db->Quote('%'.$filter_subnum.'%'));
       	}
        
        if (strlen($filter_orderid))
        {
            $query->where('tbl.order_id = '.$this->_db->Quote($filter_orderid));
        }

        if (strlen($filter_orderitemid))
        {
            $query->where('tbl.orderitem_id = '.$this->_db->Quote($filter_orderitemid));
        }

        if (strlen($filter_enabled))
        {
            $query->where('tbl.subscription_enabled = '.$this->_db->Quote($filter_enabled));
        }
        
        if (strlen($filter_lifetime))
        {
            $query->where('tbl.lifetime_enabled = '.$this->_db->Quote($filter_lifetime));
        }

        if (strlen($filter_productid))
        {
            $query->where('tbl.product_id = '.$this->_db->Quote($filter_productid));
        }
        
        if (strlen($filter_date_from))
        {
            switch ($filter_datetype)
            {
                case "expires":
                    $query->where("tbl.expires_datetime >= '".$filter_date_from."'");
                  break;
                case "created":
                default:
                    $query->where("tbl.created_datetime >= '".$filter_date_from."'");
                  break;
            }
        }
        
        if (strlen($filter_date_to))
        {
            switch ($filter_datetype)
            {
                case "expires":
                    $query->where("tbl.expires_datetime <= '".$filter_date_to."'");
                  break;
                case "created":
                default:
                    $query->where("tbl.created_datetime <= '".$filter_date_to."'");
                  break;
            }
        }
        
    	if (strlen($filter_orderstate))
        {
            $query->where('o.order_state_id = '.$this->_db->Quote($filter_orderstate));
        }
    }
    
    protected function _buildQueryJoins(&$query)
    {
        $query->join('LEFT', '#__tienda_orderitems AS oi ON oi.orderitem_id = tbl.orderitem_id');
        $query->join('LEFT', '#__tienda_products AS p ON oi.product_id = p.product_id');
        $query->join('LEFT', '#__tienda_orders AS o ON tbl.order_id = o.order_id');
        $query->join('LEFT', '#__users AS u ON u.id = tbl.user_id');
    }
    
    protected function _buildQueryFields(&$query)
    {
        $field = array();

        $field[] = " p.product_name ";
        $field[] = " p.product_sku ";
        $field[] = " p.product_model ";
        $field[] = " o.* ";
        $field[] = " tbl.* ";
        $field[] = " oi.* ";
        $field[] = " u.name AS user_name ";
        $field[] = " u.username AS user_username "; 
        $field[] = " u.email ";

        $query->select( $field );
    }
    
   public function getList($refresh = false)
    {
        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $list = parent::getList($refresh);
        
        // If no item in the list, return an array()
        if( empty( $list ) ){
            return array();
        }
        $db = JFactory::getDbo();
        Tienda::load( 'DSCQuery', 'library.query' );
        $q = new DSCQuery();
        $q->select( 'order_hash' );
        $q->from( '#__tienda_orders' );
        
        Tienda::load( 'TiendaHelperSubscription', 'helpers.subscription' );
        foreach ($list as $item)
        {
            $item->link = 'index.php?option=com_tienda&view=subscriptions&task=edit&id='.$item->subscription_id;
            $item->link_view = 'index.php?option=com_tienda&view=subscriptions&task=view&id='.$item->subscription_id;
            $item->history = TiendaHelperSubscription::getHistory( $item->subscription_id );
            $q->_where = null;
            $q->where( 'order_id = '.$item->order_id );
            $db->setQuery( $q );
            $item->order_hash = $db->loadResult();
        }
        
        return $list;
    }
    
   	public function getItem( $pk=null, $refresh=false, $emptyState=true )
    {
        Tienda::load( 'TiendaHelperSubscription', 'helpers.subscription' );
        if ($item = parent::getItem($pk, $refresh, $emptyState))
        {
            $item->link = 'index.php?option=com_tienda&view=subscriptions&task=edit&id='.$item->subscription_id;
            $item->link_view = 'index.php?option=com_tienda&view=subscriptions&task=view&id='.$item->subscription_id;
            $item->history = TiendaHelperSubscription::getHistory( $item->subscription_id );           

            Tienda::load( 'DSCQuery', 'library.query' );
            $q = new DSCQuery();
            $q->select( 'order_hash' );
            $q->from( '#__tienda_orders' );
            $q->where( 'order_id = '.$item->order_id );
            $db = JFactory::getDbo();
            $db->setQuery( $q );
            $item->order_hash = $db->loadResult();
        }
        
        $dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger( 'onPrepare'.$this->getTable()->get('_suffix'), array( &$item ) );
        
        return $item;
    }

	/**
	 * Gets list of all subscriptions by issue x-days before expiring
	 *
	 * @$days Number of days before expiring (0 stands for expired now)
	 *
	 * @return List of subscriptions by issue
	 */
	public function getListByIssues( $days = 0 )
	{
		$db = $this->getDBO();
		$date = JFactory::getDate();
		$date->setOffset( -JFactory::getConfig()->getValue( 'config.offset' ) );
		$today = $date->toFormat( "%Y-%m-%d" );
		
		Tienda::load( 'DSCQuery', 'library.query' );
		$q = new DSCQuery();
		$q->select( 's.*' );
		$q->from( '`#__tienda_productissues` tbl' );
		$q->join( 'left', '`#__tienda_subscriptions` s ON s.`product_id` = tbl.`product_id`' );
		$q->join( 'left', '`#__tienda_orderitems` oi ON s.`orderitem_id` = oi.`orderitem_id`' );
		$q->where( 's.`subscription_enabled` = 1' );
		$q->where( 'oi.`subscription_period_unit` = \'I\'' );
		
		if( $days ) // x-days before expiring
		{
			$query = " SELECT DATE_ADD('".$today."', INTERVAL '.$days.' DAY) ";
			$db->setQuery( $query );
			$date = Date( 'Y-m-d', strtotime( $db->loadResult() ) );
		}
		else // just expired
		{
			$date = $today;
		}
		$q->where( 'DATE_FORMAT( tbl.`publishing_date`, \'%Y-%m-%d\' ) = \''.$date.'\'' );
		$db->setQuery( (string)$q );
		return $db->loadObjectList();
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
	
	    $model = DSCModel::getInstance('SubscriptionHistory', 'TiendaModel');
	    $model->clearCache();
	}
}