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

class TiendaTableProductIssues extends TiendaTable
{
	function TiendaTableProductIssues ( &$db )
	{

		$tbl_key 	= 'product_issue_id';
		$tbl_suffix = 'productissues';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'tienda';

		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );
	}

	/**
	 * Checks row for data integrity.
	 * Assumes working dates have been converted to local time for display,
	 * so will always convert working dates to GMT
	 *
	 * @return unknown_type
	 */
	function check()
	{
		if (empty($this->product_id))
		{
			$this->setError( JText::_( "Product Association Required" ) );
			return false;
		}

		$nullDate = $this->_db->getNullDate();
		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$this->publishing_date = ($this->publishing_date != $nullDate) ? TiendaHelperBase::getOffsetDate( $this->publishing_date ) : $this->publishing_date;

		if (empty($this->created_date) || $this->created_date == $nullDate)
		{
			$date = JFactory::getDate();
			$this->created_date = $date->toMysql();
		}

		$date = JFactory::getDate();
		$this->modified_date = $date->toMysql();

		return true;
	}
}
