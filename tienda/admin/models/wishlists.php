<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaModelEav', 'models._baseeav' );

class TiendaModelWishlists extends DSCModel
{
	public $cache_enabled = false;

	protected function _buildQueryWhere(&$query)
	{
		$filter_user     = $this->getState('filter_user');
		$filter_session  = $this->getState('filter_session');
		$filter_product  = $this->getState('filter_product');
		$filter_date_from	= $this->getState('filter_date_from');
		$filter_date_to		= $this->getState('filter_date_to');
		$filter_name	= $this->getState('filter_name');
        $filter_ids	= $this->getState('filter_ids');

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

		if (!empty($filter_ids) && is_array($filter_ids))
        {
        	$query->where('tbl.wishlist_id IN('.implode(",", $filter_ids).')' );
        }
	}

	protected function _buildQueryJoins(&$query)
	{
		
	}

	protected function _buildQueryFields(&$query)
	{
		$field = array();
		

		$query->select( $this->getState( 'select', 'tbl.*' ) );
		$query->select( $field );
	}

	protected function prepareItem( &$item, $key=0, $refresh=false )
    {	
    	 parent::prepareItem( $item, $key, $refresh );
    	 DSCModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
     	 DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
    	 $model = DSCModel::getInstance('WishlistsItems', 'TiendaModel');
    	 $model->setState('wishlist_id', $item->wishlist_id);
    	 $item->items = $model->getList();
    	 $router = new TiendaHelperRoute();
    	 $item->link = $url = "index.php?option=com_tienda&view=wishlists&task=view&id=".$item->wishlist_id."&Itemid=".$router->findItemid( array('view'=>'wishlists') );
    
    }


	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $old_sessionid
	 * @param unknown_type $user_id
	 * @return return_type
	 */
	public function setUserForSessionItems( $old_sessionid, $user_id )
	{
	    $query = new TiendaQuery();
	    $query->update( '#__tienda_wishlists' );
	    $query->set( "user_id = '" . $user_id . "'" );
	    $query->where( "session_id = '" . $old_sessionid . "'" );
	    $query->where( "user_id = '0'" );
	    
	    $db = $this->getDBO();
	    $db->setQuery( (string) $query );
	    if ($db->query())
	    {
	        $affected = $db->getAffectedRows();
	        if ($affected > 0)
	        {
	            $lang = JFactory::getLanguage();
	            $lang->load( 'com_tienda' );
                Tienda::load( "TiendaHelperRoute", 'helpers.route' );
                $router = new TiendaHelperRoute();
    		    $url = "index.php?option=com_tienda&view=wishlists&Itemid=".$router->findItemid( array('view'=>'wishlists') );
        		$message = JText::sprintf( JText::_('COM_TIENDA_ADDED_TO_WISHLIST'), $url );
	            JFactory::getApplication()->enqueueMessage( $message );
	        }
	    }

	    $this->clearSessionIds();
	    $this->mergeUserItems( $user_id );
	}
	
	public function clearSessionIds() 
	{
	    $query = new TiendaQuery();
	    $query->update( '#__tienda_wishlists' );
	    $query->set( "session_id = ''" );
	    $query->where( "user_id > '0'" );
	    $db = $this->getDBO();
	    $db->setQuery( (string) $query );
	    if (!$db->query()) {
	    }	    
	}


	
	public function mergeUserItems( $user_id )
	{
	    $table = $this->getTable();
	    
	    $this->emptyState();
	    $this->setState('filter_user', $user_id );
	    if ($items = $this->getList(true)) 
	    {
	        $done = array();
	        foreach ($items as $item) 
	        {
	            if (empty($done[$item->product_id])) 
	            {
	                $done[$item->product_id] = $item;
	            } 
	            else 
	            {
	                $to_delete = $item->wishlist_id;
	                if ($done[$item->product_id]->last_updated < $item->last_updated) 
	                {
	                    $done[$item->product_id] = $item;
	                    $to_delete = $done[$item->product_id]->wishlist_id;
	                }
                    $table->delete($to_delete);
	            }
	        }
	    }
	    
	}

	public function checkItem($product_id) {


	}



}