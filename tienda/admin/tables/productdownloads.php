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

class TiendaTableProductDownloads extends TiendaTable 
{
	function TiendaTableProductDownloads ( &$db ) 
	{
		
		$tbl_key 	= 'productdownload_id';
		$tbl_suffix = 'productdownloads';
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
			$this->setError( JText::_( "Product File ID Required" ) );
			return false;
		}
        if (empty($this->user_id))
        {
            $this->setError( JText::_( "User ID Required" ) );
            return false;
        }
	    if (empty($this->order_id))
        {
            $this->setError( JText::_( "Order ID Required" ) );
            return false;
        }
        // TODO This is technically unnecessary because of the join you can do with productfile_id, maybe remove it eventually?
	    if (empty($this->product_id))
        {
            $this->setError( JText::_( "Product ID Required" ) );
            return false;
        }
	    $nullDate   = $this->_db->getNullDate();
        if (empty($this->productdownload_startdate) || $this->productdownload_startdate == $nullDate)
        {
            $date = JFactory::getDate();
            $this->productdownload_startdate = $date->toMysql();
        }
        // if the enddate is 0000-00-00 then the download never expires
		return true;
	}
}
