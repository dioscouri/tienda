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

class modTiendaUserAddressHelper
{
    function getAddresses()
    {
    	//require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'defines.php' );
        JLoader::import( 'com_tienda.helpers.user', JPATH_ADMINISTRATOR.DS.'components' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        JModel::addIncludePath( JPATH_SITE.DS.'components'.DS.'com_tienda'.DS.'models' );

        // determine whether we're working with a session or db cart
        //$userAddress = TiendaHelperUser::getPrimaryAddress(JRequest::getVar('id', 0));
    	$model = JModel::getInstance( 'Addresses', 'TiendaModel' );
    	$model->setState('filter_userid', JRequest::getVar('id', 0));
    	$userAddresses = $model->getList();
    	return $userAddresses;
    }
}
?>
