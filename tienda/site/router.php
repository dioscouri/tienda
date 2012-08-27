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

if ( !class_exists('Tienda') ) 
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR."/components/com_tienda/defines.php" );

Tienda::load( "TiendaHelperRoute", 'helpers.route' );

/**
 * Build the route
 * Is just a wrapper for TiendaHelperRoute::build()
 * 
 * @param unknown_type $query
 * @return unknown_type
 */
function TiendaBuildRoute(&$query)
{
    return TiendaHelperRoute::build($query);
}

/**
 * Parse the url segments
 * Is just a wrapper for TiendaHelperRoute::parse()
 * 
 * @param unknown_type $segments
 * @return unknown_type
 */
function TiendaParseRoute($segments)
{
    return TiendaHelperRoute::parse($segments);
}