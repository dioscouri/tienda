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

jimport('joomla.plugin.plugin');
jimport( 'joomla.application.component.model' );

class plgContentTienda_Product extends JPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'tienda_content_product';
    
	function plgContentTienda_Product(& $subject, $config) 
	{
		die();
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}
	
/**
     * Search for the tag and replace it with the product view {tiendaproduct}
     * 
     * @param $article
     * @param $params
     * @param $limitstart
     */
   	function onPrepareContent( &$row, &$params, $page=0 )
   	{
	   	// simple performance check to determine whether bot should process further
		if ( JString::strpos( $row->text, 'tiendaproduct' ) === false ) {
			return true;
		}
	
		// Get plugin info
		$plugin =& JPluginHelper::getPlugin('content', 'tienda_content_product');
	
	 	// expression to search for
	 	$regex = '/{tiendaproduct\s*.*?}/i';
	
	 	$pluginParams = new JParameter( $plugin->params );
	
		// check whether plugin has been unpublished
		if ( !$pluginParams->get( 'enabled', 1 ) ) {
			$row->text = preg_replace( $regex, '', $row->text );
			return true;
		}
	
	 	// find all instances of plugin and put in $matches
		preg_match_all( $regex, $row->text, $matches );
	
		// Number of plugins
	 	$count = count( $matches[0] );
	
	 	// plugin only processes if there are any instances of the plugin in the text
	 	if ( $count ) {
	 		foreach($matches as $match)
	 			$this->showProducts(&$row, &$matches, $count, $regex );
		}
   	}
   	
   	function showProducts( &$row, &$matches, $count, $regex )
   	{
   		for ( $i=0; $i < $count; $i++ )
		{
	 		$load = str_replace( 'tiendaproduct', '', $matches[0][$i] );
	 		$load = str_replace( '{', '', $load );
	 		$load = str_replace( '}', '', $load );
	 		$load = trim( $load );
	
			$product	= $this->showProduct( $load );
			$row->text 	= str_replace($matches[0][$i], $product, $row->text );
	 	}
	
	  	// removes tags without matching
		$row->text = preg_replace( $regex, '', $row->text );
	}
	
	
	function showProduct( $load )
	{
		return $load;
	}
    
}
