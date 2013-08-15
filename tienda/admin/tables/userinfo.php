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

class TiendaTableUserInfo extends TiendaTable 
{
	function TiendaTableUserInfo( &$db ) 
	{
		$tbl_key 	= 'user_info_id';
		$tbl_suffix = 'userinfo';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'tienda';
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );	
	}
	
	function check()
	{
		if ($this->credits_withdrawable_total > $this->credits_total)
	    {
	        $this->credits_withdrawable_total = $this->credits_total;
	    }
		
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		
		$notnew = isset( $this->user_info_id );
		
		$old_record = JTable::getInstance( 'UserInfo', 'TiendaTable' );
		$old_record->load( $this->user_info_id );
		$changed_sub_num = $old_record->sub_number != $this->sub_number;
		
		if( $notnew && $app->isSite() && $changed_sub_num && !( $user->usertype == 'Super Administrator'  ) )
		{
				$this->setError( JText::_('COM_TIENDA_YOU_DO_NOT_HAVE_ENOUGH_RIGHTS_TO_PERFORM_THIS_TASK') );
				return false;
		}
		return true;
	}
}