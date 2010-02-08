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
defined('_JEXEC') or die('Restricted access');

JLoader::import( 'com_tienda.helpers._base', JPATH_ADMINISTRATOR.DS.'components' );

class TiendaHelperDiagnostics extends TiendaHelperBase 
{
	/**
	 * Performs basic checks on your Tienda installation to ensure it is configured OK
	 * @return unknown_type
	 */
	function checkInstallation() 
	{
		// TODO check all DB tables
		// TODO if no articles associated for site::dashboard, create default articles for dashboard
			// and update config
	}

}