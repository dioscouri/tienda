<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

Tienda::load( 'TiendaTable', 'tables._base' );

class TiendaTablePosRequests extends TiendaTable 
{
	function TiendaTablePosRequests ( &$db ) 
	{
		
		$tbl_key 	= 'pos_id';
		$tbl_suffix = 'posrequests';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'tienda';
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );	
	}
	
	function check()
	{
		$nullDate	= $this->_db->getNullDate();
		if (empty($this->created_date) || $this->created_date == $nullDate)
		{
			$date = JFactory::getDate();
			$this->created_date = $date->toMysql();
		}
		
		if (empty($this->salt))
		{
			$this->salt = $this->GenerateSalt( 12 );
		}
		
		$this->token = $this->CalculateHash($this);
		return true;
	}
	
	public function CalculateHash($item)
	{
		$sw = Tienda::getInstance()->get("secret_word");
		return sha1($sw.$item->order_id.$item->pos_id.$item->token.$item->user_id.$item->mode);
	}
	
	public function GenerateSalt($len)
	{
		jimport("joomla.user.helper");
		return JUserHelper::genRandomPassword($len);
	}
}
