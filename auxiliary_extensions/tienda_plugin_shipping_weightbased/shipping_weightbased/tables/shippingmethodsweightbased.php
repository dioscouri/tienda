<?php
/**
 * @version 1.5
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

class TiendaTableShippingMethodsWeightbased extends TiendaTable
{
	function TiendaTableShippingMethodsWeightbased ( &$db )
	{

		$tbl_key    = 'shipping_method_weightbased_id';
		$tbl_suffix = 'shippingmethods_weightbased';
		$this->set( '_suffix', $tbl_suffix );
		$name       = 'tienda';

		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );
	}

	function check()
	{
		return true;
	}

}
