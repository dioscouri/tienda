<?php
/**
 * @version    1.5
 * @package    Tienda
 * @author     Dioscouri Design
 * @link     http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

class modTiendaCartHelper
{
    function getCart()
    {
    	//require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'defines.php' );
        Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        JModel::addIncludePath( JPATH_SITE.DS.'components'.DS.'com_tienda'.DS.'models' );

        // determine whether we're working with a session or db cart
        $suffix = TiendaHelperCarts::getSuffix();
    	$model = JModel::getInstance( $suffix, 'TiendaModel' );
    	$cart = $model->getList();
    	return $cart;
    }
}
?>
