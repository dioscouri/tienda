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
    
	function __construct(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$language = JFactory::getLanguage();
		$language -> load('plg_tienda_'.$this->_element, JPATH_ADMINISTRATOR, 'en-GB', true);
		$language -> load('plg_tienda_'.$this->_element, JPATH_ADMINISTRATOR, null, true);
		
		$this->webid = $this->params->get('webid', '');
		$this->inccategory = $this->params->get('inccategory', '');
	}
    
	/**
	 *  This event is triggered after the prepayment (during checkout) is displayed
	 * @param $order
	 */
	function onAfterDisplayPrePayment($order)
	{			
			$doc = JFactory::getDocument();		
			$script = $this->_buildScript($order);	
			$doc->addScriptDeclaration($script);
	}
		
	/**
	 * Method to build the script to be appended to the body	
	 * @param object $order - order object 
	 * @return string
	 */
	function _buildScript($order)
	{		
		$storeName = Tienda::getInstance()->get('shop_name');
		$tax = $order->order_tax + $order->order_shipping_tax;
		
		$script = "";		
		$script .= "var _gaq = _gaq || [];\r\n";
		$script .= "_gaq.push(['_setAccount', '{$this->webid}']);\r\n";
		$script .= "_gaq.push(['_trackPageview']);\r\n";
		$script .= "_gaq.push(['_addTrans',\r\n";
		$script .= "	'{$order->order_id}',\r\n";
		$script .= "	'{$storeName}',\r\n";
		$script .= "	'{$order->order_total}',\r\n";
		$script .= "	'{$tax}',\r\n";
		$script .= "	'{$order->orderinfo->billing_city}',\r\n";
		$script .= "	'{$order->orderinfo->billing_zone_name}',\r\n";
		$script .= "	'{$order->orderinfo->billing_country_name}'\r\n";
		$script .= "]);\r\n\r\n\r\n";	
				
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
		 	
		 	$script .= "_gaq.push(['_addItem',\r\n";
		 	$script .= "	'{$order->order_id}',\r\n";
		 	$script .= "	'{$item->product_id}',\r\n";
		 	$script .= "	'{$item->orderitem_name}',\r\n";
		 	$script .= "	'{$catName}',\r\n";
		 	$script .= "	'{$item->orderitem_final_price}',\r\n";
		 	$script .= "	'{$item->orderitem_quantity}',\r\n";
		 	$script .= "]);\r\n";
		 }
		 		 
		$script .= "_gaq.push(['_trackTrans']);\r\n";
		$script .= "(function() {\r\n";
		$script .= "var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;\r\n";
		$script .= "ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';\r\n";
		$script .= "var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);\r\n";
		$script .= "})();\r\n";
					 		 		
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
