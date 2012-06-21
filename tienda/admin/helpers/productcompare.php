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

jimport( 'joomla.application.component.model' );
Tienda::load( 'TiendaHelperBase', 'helpers._base' );

class TiendaHelperProductCompare extends TiendaHelperBase
{	
	/**
	 * Method to check if we can still add a product to compare 
	 * @return boolean
	 */
	public function checkLimit()
	{
		$canAdd = true;
		$model = JModel::getInstance( 'ProductCompare', 'TiendaModel');
		$user = JFactory::getUser();
        $model->setState( 'filter_user', $user->id ); 
        if (empty($user->id))
        {
        	$session = JFactory::getSession();
            $model->setState( 'filter_session', $session->getId() ); 
        }
       
        $total = $model->getTotal();
        $limit = Tienda::getInstance()->get('compared_products', '5');
        
        if($total >= $limit)
        {
        	$canAdd = false;
        }        
        
        return $canAdd;
	}	
	
	public function getComparedProducts()
	{
		$model = JModel::getInstance( 'ProductCompare', 'TiendaModel');
		
	 	$user = JFactory::getUser();
        $model->setState( 'filter_user', $user->id ); 
        if (empty($user->id))
        {
        	$session = JFactory::getSession();
            $model->setState( 'filter_session', $session->getId() ); 
        }
             
       	$items = $model->getList();
       	
       	$itemsA = array();       	
       	foreach($items as $item)
       	{
       		$itemsA[] = $item->product_id;
       	}
       	
       	return $itemsA;	
	}	
	
    /**
     * Adds an item to the product compare
     * 
     * @param $item
     * @return unknown_type
     */
    public function addItem( $item )
    {
       	$session = JFactory::getSession();
        $user = JFactory::getUser();
        
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $table = JTable::getInstance( 'ProductCompare', 'TiendaTable' );
        
        $keynames = array();
        $item->user_id = (empty($item->user_id)) ? $user->id : $item->user_id;
        $keynames['user_id'] = $item->user_id;
        if (empty($item->user_id))
        {
            $keynames['session_id'] = $session->getId();
        }
        $keynames['product_id'] = $item->product_id;
              
        if (!$table->load($keynames))
        {
        	foreach($item as $key=>$value)
            {
                if(property_exists($table, $key))
                {
                    $table->set($key, $value);
                }
            }
        }
              
        $date = JFactory::getDate();
        $table->last_updated = $date->toMysql();
        $table->session_id = $session->getId();
        
        if (!$table->save())
        {
            JError::raiseNotice('updateProductCompare', $table->getError());
        }

        return $table;
    }
    	
	/**
	 * 
	 * @param unknown_type $user_id
	 * @param unknown_type $session_id
	 * @return unknown_type
	 */
	function updateUserProductComparedItemsSessionId( $user_id, $session_id )
	{
        $db = JFactory::getDBO();

        Tienda::load( 'TiendaQuery', 'library.query' );
        $query = new TiendaQuery();
        
        $query->update( "#__tienda_productcompare" );
        $query->set( "`session_id` = '$session_id' " );
        $query->where( "`user_id` = '$user_id'" );
        $db->setQuery( (string) $query );
        if (!$db->query())
        {
            $this->setError( $db->getErrorMsg() );
            return false;
        }
        return true;
	}
	
	/**
	 * 
	 * @param $session_id
	 * @return unknown_type
	 */
	function deleteSessionProductComparedItems( $session_id )
	{
        $db = JFactory::getDBO();

        Tienda::load( 'TiendaQuery', 'library.query' );
        $query = new TiendaQuery();
        
        $query->delete();
        $query->from( "#__tienda_productcompare" );
        $query->where( "`session_id` = '$session_id' " );
        $query->where( "`user_id` = '0'" );
        $db->setQuery( (string) $query );
        if (!$db->query())
        {
            $this->setError( $db->getErrorMsg() );
            return false;
        }
        return true;
	}
	
	/**
	 * 
	 * @param $session_id
	 * @param $user_id
	 * @return unknown_type
	 */
	function mergeSessionProductComparedWithUserProductCompared( $session_id, $user_id )
	{
	 	$date = JFactory::getDate();
	    $session = JFactory::getSession();
	    
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'ProductCompare', 'TiendaModel' );
        $model->setState( 'filter_user', '0' );
        $model->setState( 'filter_session', $session_id );
        $session_compareditems = $model->getList();

		$this->deleteSessionProductComparedItems( $session_id );
        if (!empty($session_compareditems))
        {
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
           
            foreach ($session_compareditems as $session_compareditem)
            {      
            	$table = JTable::getInstance( 'ProductCompare', 'TiendaTable' );
            	$keynames = array();
                $keynames['user_id'] = $user_id;
                $keynames['product_id'] = $session_compareditem->product_id;
                
            	if (!$table->load($keynames))
                {
                	$table->productcompare_id = '0';  
	                      
                }
                
                 $table->user_id = $user_id;
	             $table->product_id = $session_compareditem->product_id;
	             $table->session_id = $session->getId();
	             $table->last_updated = $date->toMysql();
	                
	             if (!$table->save())
	             {
	             	JError::raiseNotice('updateCart', $table->getError());
	             } 
               
            }
        }        
	}	

	/**
	 * Remove the Item from product compare  
	 *
	 * @param  session id
	 * @param  user id
	 * @param  product id
	 * @return null
	 */
	function removeComparedItem( $session_id, $user_id=0, $product_id )
	{
		$db = JFactory::getDBO();

		Tienda::load( 'TiendaQuery', 'library.query' );
		$query = new TiendaQuery();
		$query->delete();
		$query->from( "#__tienda_productcompare" );
		if (empty($user_id)) 
		{
			$query->where( "`session_id` = '$session_id' " );
		}
		$query->where( "`user_id` = '".$user_id."'" );
		
		$query->where( "`product_id` = '".$product_id."'" );
		
		$db->setQuery( (string) $query );

		// TODO Make this report errors and return boolean
		$db->query();

		return null;
	}
}