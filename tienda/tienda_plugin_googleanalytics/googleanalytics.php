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
    var $_element    = 'googleanalytics';
    
    var $webid = '';    
    
    var $inccategory = '0';
    
	function plgTiendaGoogleAnalytics(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
		
		$this->webid = $this->params->get('webid', '');
		$this->inccategory = $this->params->get('inccategory', '');
	}
    
	/**
	 *  Method that will be triggered after the prepayment is displayed
	 * @param $order
	 */
	function onAfterDisplayPrePayment($order)
	{		
		if(!empty($this->webid))
		{
			$doc =& JFactory::getDocument();	
			$doc->addScriptDeclaration($this->_buildScript($order));		
		}	
	}
	
	/**
	 * Method to build the script to be put to the header
	 * Enter description here ...
	 * @param object $order
	 * @return string
	 */
	function _buildScript($order)
	{
		$storeName = TiendaConfig::getInstance()->get('shop_name');
		$tax = $order->order_tax + $order->order_shipping_tax;
		
		$script = "";
		$script .= "var _gaq = _gaq || [];\r\n";
		$script .= "_gaq.push(['_setAccount', '{$this->webid}']);\r\n";
		$script .= " _gaq.push(['_trackPageview']);\r\n";
		$script .= "_gaq.push(['_addTrans',
		    '{$order->order_id}',           // order ID - required
		    '{$storeName}',  				// affiliation or store name
		    '{$order->order_total}',          // total - required
		    '{$tax}',           // tax
		    '{$order->order_shipping}',              // shipping
		    '{$order->orderinfo->billing_city}',       // city
			'{$order->orderinfo->billing_zone_name}',     // state or province
			'{$order->orderinfo->billing_country_name}'             // country
			]);\r\n\r\n";
		
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
		 	
		 	$script .= " _gaq.push(['_addItem',
				    '{$order->order_id}',           // order ID - required
				    '{$item->product_id}',           // SKU/code - required
				    '{$item->orderitem_name}',        // product name
				    '{$catName}',   // category or variation
				    '{$item->orderitem_final_price}',          // unit price - required
				    '{$item->orderitem_quantity}'               // quantity - required
				  ]);\r\n";		 		
		 }
		 
		 $script .= "_gaq.push(['_trackTrans']); //submits transaction to the Analytics servers\r\n";
		 $script .= "(function() {
							    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
							    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
							    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
							  })();\r\n\r\n";	
 	 		 		
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
