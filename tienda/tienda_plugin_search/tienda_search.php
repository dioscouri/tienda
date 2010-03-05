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
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' ) ;


jimport( 'joomla.plugin' ) ;

//Now define the registerEvent and the language file. Replace 'nameofplugin' with the name of your plugin.
$mainframe->registerEvent( 'onSearch', 'plgSearchTienda' );
$mainframe->registerEvent( 'onSearchAreas', 'plgSearchTiendaAreas' );
 
JPlugin::loadLanguage( 'tienda_search' );
 
//Then define a function to return an array of search areas. Replace 'nameofplugin' with the name of your plugin.
function &plgSearchTiendaAreas()
{
        static $areas = array(
                'tienda_search' => 'tienda_search'
        );
        return $areas;
}
 
//Then the real function has to be created. The database connection should be made. 
//The function will be closed with an } at the end of the file.
function plgSearchTienda( $text, $phrase='', $ordering='', $areas=null )
{
        $db            =& JFactory::getDBO();        
 
		//If the array is not correct, return it:
        if (is_array( $areas )) {
                if (!array_intersect( $areas, array_keys( plgSearchTiendaAreas() ) )) {
                        return array();
                }
        }
 
		//It is time to define the parameters! First get the right plugin; 'search' (the group), 'nameofplugin'. 
		$plugin =& JPluginHelper::getPlugin('search', 'tienda_plugin');
		 
		//Then load the parameters of the plugin..
		$pluginParams = new JParameter( $plugin->params );
		 
		//Use the function trim to delete spaces in front of or at the back of the searching terms
		$text = trim( $text );
		 
		//Return Array when nothing was filled in
		if ($text == '') {
             return array();
		}
 
		//After this, you have to add the database part. This will be the most difficult part, because this changes per situation.
		//In the coding examples later on you will find some of the examples used by Joomla! 1.5 core Search Plugins.
		//It will look something like this.
        $wheres = array();
        switch ($phrase) {
 
				//search exact
                case 'exact':
                        $text          = $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false );
                        $wheres2       = array();
                        $wheres2[]   = 'LOWER(a.product_name) LIKE '.$text;
                        $where                 = '(' . implode( ') OR (', $wheres2 ) . ')';
                        break;
 
				//search all or any
                case 'all':
                case 'any':
 
				//set default
                default:
                        $words         = explode( ' ', $text );
                        $wheres = array();
                        foreach ($words as $word)
                        {
                                $word          = $db->Quote( '%'.$db->getEscaped( $word, true ).'%', false );
                                $wheres2       = array();
                                $wheres2[]   = 'LOWER(a.product_name) LIKE '.$word;
                                $wheres[]    = implode( ' OR ', $wheres2 );
                        }
                        $where = '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres ) . ')';
                        break;
        }
 
		//ordering of the results
        switch ( $ordering ) {
 
				//alphabetic, ascending
                case 'alpha':
                        $order = 'a.product_name ASC';
                        break;
 
				//oldest first
                case 'oldest':
 
				//popular first
                case 'popular':
 
				//newest first
                case 'newest':
 
				//default setting: alphabetic, ascending
                default:
                        $order = 'a.product_name ASC';
        }
 
		//replace nameofplugin
        $searchtienda_search = JText::_( 'Tienda' );
 
		//the database query; differs per situation! It will look something like this:
        $query = 'SELECT a.product_name AS title'
        . ' FROM #__tienda_products AS a'
        . ' WHERE ( '. $where .' )'
        . ' AND a.product_enabled = 1'
        . ' ORDER BY '. $order
        ;
 
		//Set query
        $db->setQuery( $query, 0, $limit );
        $rows = $db->loadObjectList();
 
		//The 'output' of the displayed link
        foreach($rows as $key => $row) {
                $rows[$key]->href = "index.php?option=com_tienda&controller=products&view=products&task=view&id=".$row->slug;
        }
 
		//Return the search results in an array
		return $rows;
}
?>