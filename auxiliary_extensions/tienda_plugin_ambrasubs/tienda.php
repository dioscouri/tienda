<?php
/**
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

/** Import library dependencies */
jimport('joomla.plugin.plugin');

jimport('joomla.filesystem.file');
if (JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'defines.php')) 
{
    // Check the registry to see if our Tienda class has been overridden
    if ( !class_exists('Tienda') ) {
        JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );
    }
    
    Tienda::load( 'TiendaPluginBase', 'library.plugins._base' );

    class plgAmbrasubsTienda extends TiendaPluginBase 
    {
        /**
         * @var $_element  string  Should always correspond with the plugin's filename, 
         *                         forcing it to be unique 
         */
        var $_element    = 'tienda';
        
    	function plgAmbrasubsTienda(& $subject, $config)
    	{
    		parent::__construct($subject, $config);
    	}

    	/**
    	 * Displays the Tienda add to cart button
    	 * 
    	 * @param $row
    	 * @param $user
    	 * @return unknown_type
    	 */
    	function displayCartButton( $row, $user )
    	{
            $params = new DSCParameter( trim($row->params) );
            $product_id = $params->get( 'tienda_product_id' );
            
            JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
            $model = JModel::getInstance( 'Products', 'TiendaModel' );
            $model->setId( $product_id );
            $product = $model->getItem();
            
            if (!empty($product_id) && !empty($product->product_id))
            {
                Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
                
                if ($this->params->get('redirect_to_tienda') == '1')
                {
                    Tienda::load( "TiendaHelperRoute", 'helpers.route' );
                    $router = new TiendaHelperRoute();
                    $link = $router->product( $eventproduct->product_id );
                    $redirect = JRoute::_( $link, false );
                    
                    $app = JFactory::getApplication();
                    $app->redirect( $redirect );
                    return;
                }
                
                // set the redirect
                if ($this->params->get('redirect_back_to_ambrasubs') == '1')
                {
                    $uri = JURI::getInstance();
                    $redirect = $uri->toString();
                    $return = '';
                }
                    else
                {
                    Tienda::load( "TiendaHelperRoute", 'helpers.route' );
                    $router = new TiendaHelperRoute();
                    $itemid = $router->findItemid( array('view'=>'checkout') );
                    $redirect = JRoute::_( "index.php?option=com_tienda&view=carts&Itemid=".$itemid, false );
                    $uri = JURI::getInstance();
                    $return = $uri->toString();
                }
                
                $vars->redirect = $redirect;
                $vars->return = $return;
                $vars->ambrasubs_type = $row;
                $vars->product = $product;

                echo $this->_getLayout( 'product_buy', $vars, $this->_element, 'ambrasubs' );
            }
    	}
        
        /**
         * Event fired when viewing a subscription type, the right column of display
         * @param $row
         * @param $user
         * @return unknown_type
         */
        function onDisplaySubscriptionRightColumn( $row, $user ) 
        {
            $layout = $this->params->get('display_layout');
            if (empty($layout))
            {
                $this->displayCartButton($row, $user);
                return;
            }            
        }
        
        /**
         * Event fired after viewing a subscription type
         * 
         * @param $row
         * @param $user
         * @return unknown_type
         */
        function onAfterDisplaySubscription( $row, $user ) 
        {
            if ($this->params->get('display_layout') == '1')
            {
                $this->displayCartButton($row, $user);
                return;
            }
        }
        
    }
    
}