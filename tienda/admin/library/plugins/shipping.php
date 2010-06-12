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

Tienda::load( 'TiendaPluginBase', 'library.plugins._base' );
Tienda::load( 'TiendaModelBase', 'models._base' );

if(!class_exists('TiendaShippingPlugin'))
{

class TiendaShippingPlugin extends TiendaPluginBase 
{
    /**
     * @var $_element  string  Should always correspond with the plugin's filename, 
     *                         forcing it to be unique 
     */
    var $_element    = '';
    
	function __construct(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
		$this->loadLanguage( '', JPATH_SITE );
		$this->getShopAddress();
	}
    
    /************************************
     * Note to 3pd: 
     * 
     * The methods between here
     * and the next comment block are 
     * yours to modify by overrriding them in your shipping plugin
     * 
     ************************************/
    
	/**
     * Returns the Shipping Rates.
     * @param $element the shipping element name
     * @param $product the product row
     * @return array
     */
    public function onGetShippingRates( $element, $values )
    {
    	if (!$this->_isMe($element)) 
        {
            return null;
        }
    }
    
	/**
     * Here you will have to save the shipping rate information
     * 
     * @param $element the shipping element name
     * @param $order the order object
     * @return html
     */
    public function onPostSaveShipping( $element, $order )
    {
    	if (!$this->_isMe($element)) 
        {
            return null;
        }
    }
    
    /**
     * Get a particular shipping rate
     * @param unknown_type $rate_id
     */
    public function getShippingRate( $rate_id )
    {
    
    }
    
    /** 
     * Shows the shipping view
     * 
     * @param $row	the shipping data
     * @return unknown_type
     */
    public function onGetShippingView( $row )
    {
        if (!$this->_isMe($row)) 
        {
            return null;
        }       
    }
    
	/**
     * If you want to show something on the product admin page, 
     * override this function
     * 
     * @param $product the product row
     * @return html
     */
    public function onGetProductView( $product )
    {
    	// show something on the product admin page
    }
    
	/**
     * If you have to deal with the product data after the save
     * 
     * @param $product the product row
     * @return html
     */
    protected function onAfterSaveProducts( $product )
    {
    	// Do Something here with the product data
    }
    
	/**
     * If you want to show something on the category admin page
     * 
     * @param $category the product row
     * @return html
     */
    public function onGetCategoryView( $category )
    {
		// show something on the category admin page
    }
    
	/**
     * If you have to deal with the category data after the save
     * 
     * @param $category the product row
     * @return html
     */
    protected function onAfterSaveCategories( $category )
    {
        // Do Something here with the category data
    }
    
    
    /************************************
     * Note to 3pd: 
     * 
     * DO NOT MODIFY ANYTHING AFTER THIS
     * TEXT BLOCK UNLESS YOU KNOW WHAT YOU 
     * ARE DOING!!!!!
     * 
     ************************************/
    
    
	/**
     * Tells extension that this is a shipping plugin
     * 
     * @param $element  string      a valid shipping plugin element 
     * @return boolean	true if it is this particular shipping plugin
     */
    public function onGetShippingPlugins( $element )
    {
        $success = false;
        if ($this->_isMe($element)) 
        {
            $success = true;
        }
        return $success;    
    }

    /**
     * Gets the reports namespace for state variables
     * @return string
     */
    protected function _getNamespace()
    {
        $app = JFactory::getApplication();
        $ns = $app->getName().'::'.'com.tienda.shipping.'.$this->get('_element');
    }
    
    /**
     * Make the standard Tienda Table avaiable in the plugin
     */
    protected function includeTiendaTables(){
    	// Include Tienda Tables Classes
    	JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
    }
    
    /**
     * Include a particular Tienda Model
     * @param $name the name of the mode (ex: products)
     */
    protected function includeTiendaModel($name){
    	
    	if (strtolower($name) != 'base')
    		Tienda::load( 'TiendaModel'.ucfirst(strtolower($name)), 'models.'.strtolower($name) );
    	else
    		Tienda::load( 'TiendaModelBase', 'models._base' );
    }
    
	/**
     * Include a particular Custom Model
     * @param $name the name of the model
     * @param $plugin the name of the plugin in which the model is stored
     * @param $group the group of the plugin
     */
    protected function includeCustomModel($name, $plugin = '', $group = 'tienda')
    {
    	if (empty($plugin)) 
        {
            $plugin = $this->_element;
        }

        if(!class_exists('TiendaModel'.$name))
    		JLoader::import( 'plugins.'.$group.'.'.$plugin.'.models.'.strtolower($name), JPATH_SITE );
    }
   
    /**
     * add a user-defined table to list of available tables (including the Tienda tables
     * @param $plugin the name of the plugin in which the table is stored
     * @param $group the group of the plugin
     */
    protected function includeCustomTables($plugin = '', $group = 'tienda'){
    	
    	if (empty($plugin)) 
        {
            $plugin = $this->_element;
        }
    	
    	$this->includeTiendaTables();
    	$customPath = JPATH_SITE.DS.'plugins'.DS.$group.DS.$plugin.DS.'tables';
    	JTable::addIncludePath( $customPath );
    }
    
    /**
     * Get the task for the shipping plugin controller
     */
    public function getShippingTask(){
    	$task = JRequest::getVar('shippingTask', '');
    	return $task;
    }
    
    /**
     * Get the id of the current shipping plugin
     */
    public function getShippingId(){
    	$sid = JRequest::getVar('sid', '');
    	return $sid;
    }
    
    /**
     * Get a variable from the JRequest object
     * @param unknown_type $name
     */
    public function getShippingVar($name){
    	$var = JRequest::getVar($name, '');
    	return $var;
    }
    
    /**
     * Prepares the 'view' tmpl layout
     * when viewing a report
     *  
     * @return unknown_type
     */
    function _renderView($view = 'view', $vars = null)
    {
        if($vars == null)
        	$vars = new JObject();
        $html = $this->_getLayout($view, $vars);
        
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
    
 	/**
     * Gets the appropriate values from the request
     * 
     * @return unknown_type
     */
    function _getState()
    {
        $state = new JObject();
        
        foreach ($state->getProperties() as $key => $value)
        {
            $new_value = JRequest::getVar( $key );
            $value_exists = array_key_exists( $key, JRequest::get( 'post' ) );
            if ( $value_exists && !empty($key) )
            {
                $state->$key = $new_value;
            }
        }
        return $state;
    }
    
    function getShopAddress()
    {
        if (empty($this->shopAddress))
        {
            $this->shopAddress = new JObject();
            
            $config = TiendaConfig::getInstance();
            $this->shopAddress->address_1 = $config->get('shop_address_1');
            $this->shopAddress->address_2 = $config->get('shop_address_2');
            $this->shopAddress->city = $config->get('shop_city');
            $this->shopAddress->country = $config->get('shop_country');
            
            $this->includeTiendaTables();
            $table = JTable::getInstance('Countries', 'TiendaTable');
            $table->load($this->shopAddress->country);
            $this->shopAddress->country_isocode_2 = $table->country_isocode_2;
            
            $this->shopAddress->zone = $config->get('shop_zone');
            
            $table = JTable::getInstance('Zones', 'TiendaTable');
            $table->load($this->shopAddress->zone);
            $this->shopAddress->zone_code = $table->code;
            
            $this->shopAddress->zip = $config->get('shop_zip');            
        }
        return $this->shopAddress;

    }
                
}

}