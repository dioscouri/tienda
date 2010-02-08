<?php
/**
 * @version 1.5
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

/** Import library dependencies */
jimport('joomla.event.plugin');

/**
 * plgUserSynctiendacarts class.
 *
 * @extends JPlugin
 */
class plgUserTienda extends JPlugin
{
    function plgUserTienda(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage( '', JPATH_ADMINISTRATOR );
    }

    /**
     * When the user logs in, their session cart should override their db-stored cart.
     * Current actions take precedence
     * 
     * @param $user
     * @param $options
     * @return unknown_type
     */
    function onLoginUser($user, $options)
    {
        JLoader::import( 'com_tienda.helpers.carts', JPATH_ADMINISTRATOR.DS.'components' );
        TiendaHelperCarts::updateDbCart('', true);
        return true;
    }
}
?>
