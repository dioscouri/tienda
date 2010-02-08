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

class TableOrderPayments extends TiendaTable
{
	/**
	 *
	 *
	 * @param $db
	 * @return unknown_type
	 */
	function TableOrderPayments ( &$db )
	{
		$tbl_key 	= 'orderpayment_id';
		$tbl_suffix = 'orderpayments';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'tienda';

		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );
	}
	
	function check()
	{
        $db         = $this->getDBO();
        $nullDate   = $db->getNullDate();
	    if (empty($this->created_date) || $this->created_date == $nullDate)
        {
            $date = JFactory::getDate();
            $this->created_date = $date->toMysql();
        }
		return true;
	}
}