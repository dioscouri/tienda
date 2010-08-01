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

Tienda::load( 'TiendaHelperBase', 'helpers._base' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class TiendaHelperSubscription extends TiendaHelperBase
{
    /**
     * Given a subscription ID, will cancel it
     * 
     * @param unknown_type $subscription_id
     * @return unknown_type
     */
    function cancel( $subscription_id )
    {
        
    }

    /**
     * Given a user_id and product_id, checks if the user has a valid subscription for it
     * 
     * @param $user_id
     * @param $product_id
     * @return unknown_type
     */
    function isValid( $user_id, $product_id )
    {
        
    }
}