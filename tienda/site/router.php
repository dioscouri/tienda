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

class TiendaRoute
{
    static $itemids = null;
    
    /**
     * Finds the itemid for the set of variables provided in $needles
     * 
     * @param array $needles
     * @return unknown_type
     */
    public static function findItemid($needles=array('view'=>'products', 'task'=>'', 'filter_category'=>'', 'id'=>''))
    {
        // populate the array of menu items for the extension
        if (self::$itemids === null)
        {
            self::$itemids = array();

            $component  = &JComponentHelper::getComponent('com_tienda');
            $menus      = &JApplication::getMenu('site', array());
            $items      = $menus->getItems('component_id', $component->id);

            foreach ($items as $item)
            {
                if (isset($item->query) && isset($item->query['view']))
                {
                    // TODO Complete this
                    // Need to make self::$itemids a multi-index array of all the existing menu items
                    // accounting for view, task, filter_category, and id
                }
            }
        }

        $match = null;

        // TODO Complete this 
        // Make this search the array of self::$itemids, matching with the properties of the $needles array
        // return null if nothing found

        return null;
    }
    
    /**
     * Generates the routed url for a product
     * and attaches the itemid if possible
     * 
     * @param   int $product_id     The id of the product
     * @param   int $category_id    An optional category_id
     *
     * @return  string  The routed link
     */
    public static function product($product_id, $category_id = null)
    {
        $needles = array(
            'view' => 'products' ,
            'id' => (int) $product_id ,
            'filter_category' => (int) $category_id
        );

        // create the link
        $link = 'index.php?option=com_tienda&view=products&task=view&id=' . $product_id;

        if ($category_id) 
        {
            $link .= '&filter_category=' . $category_id;
        }

        if ($itemid = self::findItemid($needles)) 
        {
            $link .= '&Itemid=' . $itemid;
        }

        return $link;
    }
    
    /**
     * Generates the routed url for a category 
     * and attaches the itemid if possible
     *  
     * @param   int $category_id    The id of the category
     *
     * @return  string  The routed link
     */
    public static function category($category_id)
    {
        $needles = array(
            'view' => 'products' ,
            'filter_category' => (int) $category_id
        );

        // create the link
        $link = 'index.php?option=com_tienda&view=products&filter_category='. $category_id;

        if ($itemid = self::findItemid($needles)) 
        {
            $link .= '&Itemid=' . $itemid;
        }

        return $link;
    }
    
    /**
     * Build the route
     *
     * @param   array   An array of URL arguments
     * @return  array   The URL arguments to use to assemble the URL
     */
    function build( &$query )
    {
        $segments = array();
    
        // get a menu item based on the Itemid or the currently active item
        $menu = &JSite::getMenu();
    
        if (empty($query['Itemid'])) 
        {
            $item = &$menu->getActive();
        }
            else 
        {
            $item = &$menu->getItem( $query['Itemid'] );
        }
        
        $menuView = (empty($item->query['view'])) ? null : $item->query['view'];
        $menuTask = (empty($item->query['task'])) ? null : $item->query['task'];
        $menuFilterCategory = (empty($item->query['filter_category'])) ? null : $item->query['filter_category'];
        $menuId = (empty($item->query['id'])) ? null : $item->query['id'];

        // if the menu item and the query match...
        if ($menuView == @$query['view'] && 
            $menuTask == @$query['task'] && 
            $menuFilterCategory == @$query['filter_category'] && 
            $menuId == @$query['id']
        ) {
            // unset all variables and use the menu item's alias set by user 
            unset ($query['view']);
            unset ($query['task']);
            unset ($query['filter_category']);
            unset ($query['id']);
        }
        
        // otherwise, create the sef url from the query
        if ( !empty ($query['view'])) {
            $view = $query['view'];
            $segments[] = $view;
            unset ($query['view']);
        }
        
        if ( !empty ($query['task'])) {
            $task = $query['task'];
            $segments[] = $task;
            unset ($query['task']);
        }

        if ( !empty ($query['filter_category'])) {
            $filter_category = $query['filter_category'];
            $segments[] = $filter_category;
            unset ($query['filter_category']);
        }
        
        if ( !empty ($query['id'])) {
            $id = $query['id'];
            $segments[] = $id;
            unset ($query['id']);
        }
        
        return $segments;        
    }
    
    /**
     * Parses the segments of a URL
     *
     * @param   array   The segments of the URL to parse
     * @return  array   The URL attributes
     */
    function parse( $segments )
    {
        //echo "segments:<br /><pre>";
        //print_r($segments);
        //echo "</pre>";
    
        $vars = array();
        $count = count($segments);
        
        $vars['view'] = $segments[0];        
        switch ($segments[0])
        {
            case 'products':
                if ($count == '2')
                {
                    $vars['filter_category'] = $segments[1];    
                }
                
                if ($count == '3')
                {
                    $vars['task'] = $segments[1];
                    $vars['id'] = $segments[2];    
                }
                
                if ($count == '4')
                {
                    $vars['task'] = $segments[1];
                    $vars['filter_category'] = $segments[2];
                    $vars['id'] = $segments[3];    
                }
                
                break;
            default:
                if ($count == '2')
                {
                    $vars['task'] = $segments[1];    
                }
                
                if ($count == '3')
                {
                    $vars['task'] = $segments[1];
                    $vars['id'] = $segments[2];    
                }
                break;
        }        

        
        //echo "vars:<br /><pre>";
        //print_r($vars);
        //echo "</pre>";
            
        return $vars;
    }
}

/**
 * Build the route
 * Is just a wrapper for TiendaRoute::build()
 * 
 * @param unknown_type $query
 * @return unknown_type
 */
function TiendaBuildRoute(&$query)
{
    return TiendaRoute::build($query);
}

/**
 * Parse the url segments
 * Is just a wrapper for TiendaRoute::parse()
 * 
 * @param unknown_type $segments
 * @return unknown_type
 */
function TiendaParseRoute($segments)
{
    return TiendaRoute::parse($segments);
}