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

Tienda::load( 'TiendaPluginBase', 'library.plugins._base' );

class plgTiendaJEvents extends TiendaPluginBase
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'jevents';
    
	function plgTiendaJEvents(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
		
		//Check the installation integrity
        $helper = Tienda::getClass( 'TiendaHelperDiagnosticsJEvents', 'jevents.diagnostic', array( 'site'=>'site', 'type'=>'plugins', 'ext'=>'tienda' ) );
        $helper->checkInstallation();
        
        // load files
        Tienda::load( 'TiendaHelperJEvents', 'jevents.helper', array( 'site'=>'site', 'type'=>'plugins', 'ext'=>'tienda' ) );
        JTable::addIncludePath( JPATH_SITE.DS.'plugins'.DS.'tienda'.DS.'jevents'.DS.'tables' );
	}
	
    /**
     *  
     * @return unknown_type
     */
	function doCompletedOrderTasks( $order_id )
	{
        // check the connection
        $jevents = new TiendaHelperJEvents();
        if (!$db = $jevents->getDBO())
        {
            JFactory::getApplication()->enqueueMessage( $jevents->getError(), 'notice' );
            return null; 
        }
        
        // now that order is completed, 
        // create new records in the LS database tables
            // create customer first
            // then create cart (order)
            // then create cart items
             
        
        return null;
	}
	
    /**
     *  
     * @return unknown_type
     */
    function onBeforeDisplayProductForm( $item )
    {
        $vars = new JObject();
        
        //        $table = JTable::getInstance( 'LSProductsXref', 'TiendaTable' );
        //        $table->load( array( 'product_id'=>$item->product_id ) );
        //        if (empty($table->rowid))
        //        {
        //            $vars->message = JText::_( "No LSProduct exists for this TiendaProduct" );
        //        }
        //            else
        //        {
        //            $product = JTable::getInstance( 'LSProducts', 'TiendaTable' );
        //            $product->load( array( 'rowid'=>$table->rowid ) );
        //            $vars->product = $product;
        //        } 
        
        echo $this->_getLayout( 'product_form', $vars );
        return null;
    }
    
    /**
     * 
     * Enter description here ...
     * @param unknown_type $product
     */
    function onBeforeStoreProducts( $product )
    {
        // do something
        
    }
    
    /**
     * 
     * Enter description here ...
     * @param unknown_type $price
     */
    function onBeforeStoreProductPrices( $price )
    {
        // do something
    }
    
    /**
     * 
     * Enter description here ...
     * @param unknown_type $price
     */
    function onBeforeDeleteProductPrices( $price )
    {
        // do something
    }
    
    /**
     * Do something after the product is saved
     */
    function onAfterSaveProducts( $product )
    {

    }
    
    /**
     * 
     * @param $object The current product
     * @return unknown_type
     */
    function onDisplayProductAttributeOptions( $product )
    {
        // $vars = new JObject();
        // $vars->message = "Inside: onDisplayProductAttributeOptions"; 
        // echo $this->_getLayout( 'message', $vars );
        return null;
    }
    
    /**
     * 
     * Enter description here ...
     * @param $row
     */
    function onListConfigTienda( $row )
    {
        if ($this->_isMe( $row ))
        {
            return true;
        }
        return null;
    }
    
    /**
     * 
     * Enter description here ...
     * @param $row
     * @param $config
     */
    function onDisplayConfigFormSliders( $row, $config )
    {
        $vars = new JObject();
        $vars->config = $config;
        
        echo $this->_getLayout( 'config', $vars );
        return null;        
    }

    /**
     * 
     * Enter description here ...
     * @param $row
     */
    function onAfterSaveConfig( $row )
    {
        // get each of the jevents_* post variables
        // save them to the __tienda_config table
        //        $fields = array( 
        //            'jevents_host',
        //            'jevents_user',
        //            'jevents_password',
        //            'jevents_database',
        //            'jevents_prefix',
        //            'jevents_driver',
        //            'jevents_port'
        //        );
        //
        //        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        //        foreach ($fields as $field)
        //        {
        //            $config = JTable::getInstance( 'Config', 'TiendaTable' );
        //            $config->load( array( 'config_name'=>$field ) );
        //            $config->config_name = $field;
        //            $config->value = JRequest::getVar( $field );
        //            
        //            if (!$config->save())
        //            {
        //                JFactory::getApplication()->enqueueMessage( $config->getError(), 'notice' );
        //            }
        //        }
    }
}
