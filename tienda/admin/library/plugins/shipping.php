<?php
/**
 * @version 1.5
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

JLoader::import( 'com_tienda.library.plugins._base', JPATH_ADMINISTRATOR.DS.'components' );
JLoader::import( 'com_tienda.models._base', JPATH_ADMINISTRATOR.DS.'components' );

class TiendaShippingPlugin extends TiendaPluginBase
{
    /**
     * @var $_element  string  Should always correspond with the plugin's filename, 
     *                         forcing it to be unique 
     */
    var $_element    = '';
    
    /**
     * Wrapper for the internal _renderView method
     * Generally you won't have to override this, 
     * but you can if you want to
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
        
        $html = "";
        $html .= $this->_renderForm();
        $html .= $this->_renderView();

        return $html;
    }
    
	/**
     * If you want to show something on the product admin page
     * 
     * @param $product the product row
     * @return html
     */
    function onGetProductView( $product )
    {
        
       // $html = Tienda::dump($product);
		$html = "";
        return $html;
    }
    
	/**
     * If you have to save some of the information from the product to you 
     * plugin database, do it here!
     * 
     * @param $product the product row
     * @return html
     */
    function onAfterSaveProducts( $product )
    {
        // Do Something here with the product data
    }
    
	/**
     * If you want to show something on the category admin page
     * 
     * @param $category the product row
     * @return html
     */
    function onGetCategoryView( $category )
    {
        
        //$html = Tienda::dump($category);
		$html = "";
        return $html;
    }
    
	/**
     * If you have to save some of the information from the category to you 
     * plugin database, do it here!
     * 
     * @param $category the product row
     * @return html
     */
    function onAfterSaveCategories( $category )
    {
        // Do Something here with the category data
    }

    /**
     * Gets the reports namespace for state variables
     * @return string
     */
    function _getNamespace()
    {
        $app = JFactory::getApplication();
        $ns = $app->getName().'::'.'com.tienda.shipping.'.$this->get('_element');
    }
    
    /**
     * Make the standard Tienda Table avaiable in the plugin
     */
    function includeTiendaTables(){
    	// Include Tienda Tables Classes
    	JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
    }
    
    /**
     * Include a particular Tienda Model
     * @param $name the name of the mode (ex: products)
     */
    function includeTiendaModel($name){
    	JLoader::import( 'com_tienda.models.'.strtolower($name), JPATH_ADMINISTRATOR.DS.'components' );
    }
    
	/**
     * Include a particular Custom Model
     * @param $name the name of the model
     * @param $plugin the name of the plugin in which the model is stored
     * @param $group the group of the plugin
     */
    function includeCustomModel($name, $plugin = '', $group = 'tienda'){
    	
    	if (empty($plugin)) 
        {
            $plugin = $this->_element;
        }
        
    	JLoader::import( 'com_tienda.models._base', JPATH_ADMINISTRATOR.DS.'components' );
    	JLoader::import( 'plugins.'.$group.'.'.$plugin.'.models.'.strtolower($name), JPATH_SITE );
    }
   
    /**
     * add a user-defined table to list of available tables (including the Tienda tables
     * @param $plugin the name of the plugin in which the table is stored
     * @param $group the group of the plugin
     */
    function includeCustomTables($plugin = '', $group = 'tienda'){
    	
    	if (empty($plugin)) 
        {
            $plugin = $this->_element;
        }
    	
    	$this->includeTiendaTables();
    	$customPath = JPATH_SITE.DS.'plugins'.DS.$group.DS.$plugin.DS.'tables';
    	JTable::addIncludePath( $customPath );
    }
    
    function getShippingTask(){
    	$task = JRequest::getVar('shippingTask', '');
    	return $task;
    }
    
    function getShippingId(){
    	$sid = JRequest::getVar('sid', '');
    	return $sid;
    }
    
    function getShippingVar($name){
    	$var = JRequest::getVar($name, '');
    	return $var;
    }

    /************************************
     * Note to 3pd: 
     * 
     * The methods between here
     * and the next comment block are 
     * yours to modify by overrriding them in your report plugin
     * 
     ************************************/
            
    /**
     * Prepares the 'view' tmpl layout
     * when viewing a report
     *  
     * @return unknown_type
     */
    function _renderView()
    {
        // TODO Load the report, get the data, and render the report html using the form inputs & data
        
        $vars = new JObject();
        $html = $this->_getLayout('view', $vars);
        
        return $html;
    }
    
    /**
     * Prepares variables for the report form
     * 
     * @return unknown_type
     */
    function _renderForm()
    {
        $vars = new JObject();
        $html = $this->_getLayout('form', $vars);
        
        return $html;
    }
    
}