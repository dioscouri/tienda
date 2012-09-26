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

Tienda::load( 'TiendaModelBase', 'models._base' );

class TiendaModelRegister extends TiendaModelBase 
{
	function getTable()
	{
		JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
		$table = JTable::getInstance( 'UserInfo', 'TiendaTable' );
		return $table;
	}
}