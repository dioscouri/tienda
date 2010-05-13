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
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );

Tienda::load( "TiendaHelperBase", 'helpers._base' );

class TiendaHelperCurrency extends TiendaHelperBase 
{
    /**
     * Converts an amount from one currency to another
     * 
     * @param float $amount
     * @param str $currencyFrom
     * @param str $currencyTo
     * @return boolean
     */
    function convert( $amount, $currencyFrom, $currencyTo )
    {
        $return = false;
        
        return $return;
    }
}