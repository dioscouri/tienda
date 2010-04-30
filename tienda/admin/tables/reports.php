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

Tienda::load( 'TiendaTable', 'tables._base' );

class TiendaTableReports extends TiendaTable 
{
	function TiendaTableReports( &$db ) 
	{
		$tbl_key 	= 'id';
		$tbl_suffix = 'reports';
		$this->set( '_suffix', $tbl_suffix );
		
		parent::__construct( "#__plugins", $tbl_key, $db );	
	}
}