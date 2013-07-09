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
    static public function getAddresses()
    {
        Tienda::load( 'TiendaHelperUser', 'helpers.user' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
        JModel::addIncludePath( JPATH_SITE.'/components/com_tienda/models' );

        // get the user's addresses using the address model
    	$model = JModel::getInstance( 'Addresses', 'TiendaModel' );
    	$model->setState('filter_userid', JRequest::getVar('id', 0, 'request', 'int'));
    	$model->setState('filter_deleted', 0);
    	$userAddresses = $model->getList();
    	return $userAddresses;
    }
}
?>
