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
defined( '_JEXEC' ) or die( 'Restricted access' );

JLoader::import( 'com_tienda.tables._base', JPATH_ADMINISTRATOR.DS.'components' );

class TableTools extends TiendaTable 
{

	function TableTools( &$db ) 
	{
		$tbl_key 	= 'id';
		$tbl_suffix = 'tools';
		$this->set( '_suffix', $tbl_suffix );
		
		parent::__construct( "#__plugins", $tbl_key, $db );	
	}
}