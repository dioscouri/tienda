<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaHelperBase', 'helpers._base' );

class TiendaHelperPlugin extends TiendaHelperBase
{
	/**
	 * Only returns plugins that have a specific event
	 * 
	 * @param $eventName
	 * @param $folder
	 * @return array of JTable objects
	 */
	public static function getPluginsWithEvent( $eventName, $folder='Tienda' )
	{
		$return = array();
		if ($plugins = TiendaHelperPlugin::getPlugins( $folder ))
		{
			foreach ($plugins as $plugin)
			{
				if (TiendaHelperPlugin::hasEvent( $plugin, $eventName ))
				{
					$return[] = $plugin;
				}
			}
		}
		return $return;
	}
	
	/**
	 * Returns Array of active Plugins
	 * @param mixed Boolean
	 * @param mixed Boolean
	 * @return array
	 */
	static function getPlugins( $folder='Tienda' )
	{
		$database = JFactory::getDBO();
		
		$order_query = " ORDER BY ordering ASC ";
		$folder = strtolower( $folder );
		
			if(version_compare(JVERSION,'1.6.0','ge')) {
	        // Joomla! 1.6+ code here
	      $query = "
			SELECT 
				* 
			FROM 
				#__extensions 
			WHERE  enabled = '1'
			AND 
				LOWER(`folder`) = '{$folder}'
		
		";
	    } else {
	        // Joomla! 1.5 code here
	      $query = "
			SELECT 
				* 
			FROM 
				#__plugins 
			WHERE  published = '1'
			AND 
				LOWER(`folder`) = '{$folder}'
			{$order_query}
		";
		}		
			
		$database->setQuery( $query );
		$data = $database->loadObjectList();
		return $data;
	}

	/**
	 * Returns HTML
	 * @param mixed Boolean
	 * @param mixed Boolean
	 * @return array
	 */
	function getPluginsContent( $event, $options, $method='vertical' ) 
	{
		$text = "";
        jimport('joomla.html.pane');
		
		if (!$event) {
			return $text;
		}
		
		$args = array();
		$dispatcher	 = JDispatcher::getInstance();
		$results = $dispatcher->trigger( $event, $options );
		
		if ( !count($results) > 0 ) {
			return $text;
		}
		
		// grab content
		switch( strtolower($method) ) {
			case "vertical":
				for ($i=0; $i<count($results); $i++) {
					$result = $results[$i];
					$title = $result[1] ? JText::_( $result[1] ) : JText::_('Info');
					$content = $result[0];
					
		            // Vertical
		            $text .= '<p>'.$content.'</p>';
				}
			  break;
			case "tabs":
			  break;
		}

		return $text;	
	}
	
	/**
	 * Checks if a plugin has an event
	 * 
	 * @param obj      $element    the plugin JTable object
	 * @param string   $eventName  the name of the event to test for
	 * @return unknown_type
	 */
	public static function hasEvent( $element, $eventName )
	{
		$success = false;
	   if (!$element || !is_object($element)) {
			return $success;
		}
		
		if (!$eventName || !is_string($eventName)) {
			return $success;
		}
		
		// Check if they have a particular event
		$import 	= JPluginHelper::importPlugin( strtolower('Tienda'), $element->element );
		$dispatcher	= JDispatcher::getInstance();
		$result 	= $dispatcher->trigger( $eventName, array( $element ) );
		if (in_array(true, $result, true)) 
		{
			$success = true;
		}		
		return $success;	
	}	

	/**
	 * Method to get the suffix  based on the geozonetype
	 * @param $geozonetype_id
	 * @return string
	 */
	public static function getSuffix($geozonetype_id)
	{
		switch($geozonetype_id)
		{
			case '2':
				$suffix = 'shipping';
				break;
			case '1':				
			default:
				$suffix = 'payment';
				break;
		}
		
		return $suffix;
	}	
	
	/**
	 * Method to count the number of plugin assigned to a geozone
	 * @param obj $geozone 
	 * @return int
	 */
	public static function countPlgtoGeozone($geozone)
	{		
		$count = 0;	
		if(!is_object($geozone)) return $count;
		
		static $plugins;
		static $geozones;

		if(empty($plugins[$geozone->geozonetype_id]))
		{
			$suffix = TiendaHelperPlugin::getSuffix($geozone->geozonetype_id);
			JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
			$model = JModel::getInstance( $suffix, 'TiendaModel' );
			$model->setState('filter_enabled', '1');
			$plugins[$geozone->geozonetype_id] = $model->getList( );
		}
			
		foreach( $plugins[$geozone->geozonetype_id] as $plugin)
		{
			if(isset($plugin->params))
			{
				if(empty($geozones[$plugin->id]))
				{
					$params = new JParameter($plugin->params);           
        			$geozones[$plugin->id] = explode(',',$params->get('geozones')); 
				}				
        		
        		if(in_array($geozone->geozone_id, $geozones[$plugin->id])) $count++;        		
			}
		}
		
		return $count;
	}
}

?>