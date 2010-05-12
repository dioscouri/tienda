<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

JLoader::import( 'com_tienda.library.plugins.shipping', JPATH_ADMINISTRATOR.DS.'components' );

class plgTiendaShipping_Example extends TiendaShippingPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element   = 'shipping_example';
    
	
    /**
     * Overriding 
     * 
     * @param $options
     * @return unknown_type
     */
    function onGetShippingView( $row )
    {
        if (!$this->_isMe($row)) 
        {
            return null;
        }
        
        $html = $this->viewList();       

        return $html;
    }
    
    function onGetShippingRates($element, $values){
    	
    	// Check if this is the right plugin
    	if (!$this->_isMe($element)) 
        {
            return null;
        }
        
        $this->includeCustomModel('ShippingRates');
        $model = JModel::getInstance('ShippingRates', 'TiendaModel');
        
        $vars = new JObject();
        $vars->rates = $model->getList();
        $vars->order = $values;
        
		$html = $this->_renderView('rates', $vars);
		return $html;
        
    }
    
 	/**
     * Validates the data submitted based on the suffix provided
     * A controller for this plugin, you could say
     * 
     * @param $task
     * @return html
     */
    function viewList( )
    {
        $html = "";
        
        JLoader::import( 'com_tienda.library.button', JPATH_ADMINISTRATOR.DS.'components' );
		TiendaToolBarHelper::custom( 'newMethod', 'new', 'new', JText::_('New'), false, 'shippingTask' );
		TiendaToolBarHelper::custom( 'delete', 'delete', 'delete', JText::_('Delete'), false, 'shippingTask' );
		
        $vars = new JObject();
        $vars->state = $this->_getState();        
        
        $this->includeCustomModel('ShippingMethods');

        $model = JModel::getInstance('ShippingMethods', 'TiendaModel');
        $list = $model->getList();
        
		$vars->list = $list;
		
		$id = JRequest::getInt('id', '0');
		$form = array();
		$form['action'] = "index.php?option=com_tienda&view=shipping&task=view&id={$id}";
		
		$vars->form = $form;
		
        $html = $this->_getLayout('default', $vars);
		
        return $html;
    }   
    
   
}
