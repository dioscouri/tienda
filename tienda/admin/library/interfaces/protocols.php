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

/**
 * Make sure that the controllers for specific protocols
 * implements some methods (for the future)
 *
 */
interface TiendaControllerProtocol
{ 	
}

interface TiendaControllerProtocolJson extends TiendaControllerProtocol
{
	const protocol = 'json';
}

interface TiendaControllerProtocolSoap extends TiendaControllerProtocol
{
	const protocol = 'soap';
}


?>