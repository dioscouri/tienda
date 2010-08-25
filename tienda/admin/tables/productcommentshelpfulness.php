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

class TiendaTableProductCommentsHelpfulness extends TiendaTable 
{

	function TiendaTableProductCommentsHelpfulness( &$db ) 
	{
		$tbl_key 	= 'productcommentshelpfulness_id';
		$tbl_suffix = 'productcommentshelpfulness';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= "tienda";
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );	
	}
}
