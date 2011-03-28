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

Tienda::load( 'TiendaModelOrders', 'models.orders' );

class TiendaModelPOS extends TiendaModelOrders
{
    function getTable($name='Orders', $prefix='TiendaTable', $options = array())
    {
        return parent::getTable( $name, $prefix, $options );
    }
}