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

class TiendaTableTaxrates extends TiendaTable 
{
	function TiendaTableTaxrates ( &$db ) 
	{
		
		$tbl_key 	= 'tax_rate_id';
		$tbl_suffix = 'taxrates';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'tienda';
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );	
	}
	
	/**
	 * Checks the table object for integrity
	 * @return unknown_type
	 */
	function check()
	{
		if (empty($this->tax_rate))
		{
			$this->setError( "Tax Rate Required" );
			return false;
		}
	    if (empty($this->geozone_id))
        {
            $this->setError( "GeoZone Required" );
            return false;
        }
		$nullDate	= $this->_db->getNullDate();
		if (empty($this->created_date) || $this->created_date == $nullDate)
		{
			$date = JFactory::getDate();
			$this->created_date = $date->toMysql();
		}
		return true;
	}
}
