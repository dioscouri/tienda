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

Tienda::load( 'TiendaTableXref', 'tables._basexref' );

class TiendaTableUserGroups extends TiendaTableXref 
{
	/** 
	 * @param $db
	 * @return unknown_type
	 */
	function TiendaTableUserGroups ( &$db ) 
	{
		$keynames = array();
		$keynames['user_id']  = 'user_id';
        $keynames['group_id'] = 'group_id';
        $this->setKeyNames( $keynames );
                
		$tbl_key 	= 'user_id';
		$tbl_suffix = 'usergroupxref';
		$name 		= 'tienda';
		
		$this->set( '_tbl_key', $tbl_key );
		$this->set( '_suffix', $tbl_suffix );
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );	
	}
	
	function check()
	{
		if (empty($this->group_id))
		{
			$this->setError( JText::_('COM_TIENDA_GROUP_REQUIRED') );
			return false;
		}
		if (empty($this->user_id))
		{
			$this->setError( JText::_('COM_TIENDA_USER_REQUIRED') );
			return false;
		}
		
		return true;
	}
}
