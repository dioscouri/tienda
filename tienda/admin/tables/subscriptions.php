<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

Tienda::load( 'TiendaTable', 'tables._base' );

class TiendaTableSubscriptions extends TiendaTable 
{
	function TiendaTableSubscriptions ( &$db ) 
	{
		
		$tbl_key 	= 'subscription_id';
		$tbl_suffix = 'subscriptions';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'tienda';
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );	
	}
	
    function check()
    {
        $nullDate   = $this->_db->getNullDate();

        if (empty($this->created_datetime) || $this->created_datetime == $nullDate)
        {
            $date = JFactory::getDate();
            $this->created_datetime = $date->toMysql();
        }       
        return true;
    }
}
