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

class plgTiendaGoogleAnalytics extends TiendaPluginBase
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    public $_element    	= 'googleanalytics';    
    private $webid 			= '';        
    private $inccategory 	= '0';    
    private $embed 			= false;    
    private $order 			= null;
    
	function plgTiendaGoogleAnalytics(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );	
		$this->webid = $this->params->get('webid', '');
		$this->inccategory = $this->params->get('inccategory', '');
	}
    
	/**
	 *  This event is triggered after the prepayment (during checkout) is displayed
	 * @param $order
	 */
	function onAfterDisplayPrePayment($order)
	{	
		$this->embed = true;
		$this->order = $order;		
	}
	
	/**
	 * This event is triggered after the framework has rendered the application. 
	 */
 	function onAfterRender ()
    {   
    	if($this->embed)
    	{        
        	JResponse::setBody(str_replace("</body>", $this->_buildScript() . "\r\n</body>", JResponse::getBody()));
    	}
    }
	
	
	/**
	 * Method to build the script to be appended to the body	
	 * @return string
	 */
	function _buildScript()
	{
		$order = $this->order;
		$storeName = TiendaConfig::getInstance()->get('shop_name');
		$tax = $order->order_tax + $order->order_shipping_tax;
		
		$script = "";
		$script .= "<script type=\"text/javascript\">\r\n";
		$script .= "var gaJsHost = ((\"https:\" == document.location.protocol ) ? \"https://ssl.\" : \"http://www.\");\r\n";
		$script .= "document.write(unescape(\"%3Cscript src='\" + gaJsHost + \"google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E\"));\r\n";
		$script .= "</script>\r\n";  
		$script .= "<script type=\"text/javascript\">\r\n";
		$script .= "try{\r\n";
		$script .= "var pageTracker = _gat._getTracker(\"{$this->webid}\");\r\n";
		$script .= "pageTracker._trackPageview();\r\n";
		$script .= "pageTracker._addTrans(\r\n";
		$script .= "		{$order->order_id}\r\n";
		$script .= "		{$storeName}\r\n";
		$script .= "		{$order->order_total}\r\n";
		$script .= "		{$tax}\r\n";
		$script .= "		{$order->orderinfo->billing_city}\r\n";
		$script .= "		{$order->orderinfo->billing_zone_name}\r\n";
		$script .= "		{$order->orderinfo->billing_country_name}\r\n";
		$script .= "		);\r\n\r\n\r\n";
				
		Tienda::load( "TiendaHelperProduct", 'helpers.product' );
		$items = $order->getItems();		 
		foreach($items as $item)
		{
			$catName = "";
		 	//get category 
		 	if($this->inccategory)
		 	{			 	
				$catName = $this->_getCategoryName($item->product_id);
		 	}
		 	
		 	$script .= "pageTracker._addItem(\r\n";
		 	$script .= "		{$order->order_id}\r\n";
		 	$script .= "		{$item->product_id}\r\n";
		 	$script .= "		{$item->orderitem_name}\r\n";
		 	$script .= "		{$catName}\r\n";
		 	$script .= "		{$item->orderitem_final_price}\r\n";
		 	$script .= "		{$item->orderitem_quantity}\r\n";
		 	$script .= ");\r\n";
		 }
		 		 
		$script .= "pageTracker._trackTrans();\r\n";
		$script .= "} catch(err) {}\r\n";
		$script .= "</script>\r\n"; 
			 		 		
		return $script;
	}
	
	/**
	 * 
	 * Method to get product categories.
	 * @param int $id - product id
	 * @return string
	 */
	function _getCategoryName($id)
	{
		Tienda::load( 'TiendaQuery', 'library.query' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $table = JTable::getInstance( 'ProductCategories', 'TiendaTable' );
        
        $query = new TiendaQuery();      
        $query->select( "c.category_name" );
        $query->from( $table->getTableName()." AS tbl" );
        $query->where( "tbl.product_id = ".(int) $id );
        $query->join('LEFT', '#__tienda_categories AS c ON tbl.category_id = c.category_id');
        $db = JFactory::getDBO();
        $db->setQuery( (string) $query );
        $items = $db->loadResultArray();
                
        $name = "";
        if(!empty($items))
        {
        	$name = implode(" | ", $items);
        }
     
        return $name;	
	}		
}
