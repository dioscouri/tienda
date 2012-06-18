<?php
/**
 * @version    1.5
 * @package    Tienda
 * @author     Gerald Zalsos
 * @link     http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.application.component.model' );

class modTiendaComparedProductsHelper extends JObject
{		
	/**
	 * Method to get the compared product of the current user
	 * @return array
	 */
    function getComparedProducts()
    {
    	JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        JModel::addIncludePath( JPATH_SITE.DS.'components'.DS.'com_tienda'.DS.'models' );
		$user_id =& JFactory::getUser()->id;	
		$session = & JFactory::getSession();
		
    	$model  = JModel::getInstance( 'ProductCompare', 'TiendaModel' );
     	$model->setState('filter_user', $user_id );
        
     	if (empty($this->user_id))
        {
            $model->setState('filter_session', $session->getId() );
        }
        
		$items = $model->getList();
		
		foreach($items as $item)
		{		
			$table = JTable::getInstance('Products', 'TiendaTable');
			$table->load(array('product_id'=> $item->product_id));
		
			$item->product_name = $table->product_name;
			$item->link = 'index.php?option=com_tienda&view=products&task=view&id='.$item->product_id;
		}		
		
		return $items;
    }   
}
?>