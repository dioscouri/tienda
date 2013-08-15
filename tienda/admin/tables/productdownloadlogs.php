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

class TiendaTableProductDownloadLogs extends TiendaTable 
{
	function TiendaTableProductDownloadLogs ( &$db ) 
	{
		
		$tbl_key 	= 'productdownloadlog_id';
		$tbl_suffix = 'productdownloadlogs';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'tienda';
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );
	}
	
	/**
	 * Checks row for data integrity.
	 *  
	 * @return unknown_type
	 */
	function check()
	{
		if (empty($this->productfile_id))
		{
			$this->setError( JText::_('COM_TIENDA_PRODUCT_FILE_ID_REQUIRED') );
			return false;
		}
        if (empty($this->user_id))
        {
            $this->setError( JText::_('COM_TIENDA_USER_ID_REQUIRED') );
            return false;
        }
	    $nullDate   = $this->_db->getNullDate();
        if (empty($this->productdownloadlog_datetime) || $this->productdownloadlog_datetime == $nullDate)
        {
            $date = JFactory::getDate();
            $this->productdownloadlog_datetime = $date->toMysql();
        }
        if (empty($this->productdownloadlog_ipaddress))
        {
            $this->productdownloadlog_ipaddress = JRequest::getVar( 'REMOTE_ADDR', '', 'SERVER' );
        }
		return true;
	}
}
