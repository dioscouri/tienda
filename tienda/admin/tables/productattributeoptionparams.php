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

class TiendaTableProductAttributeOptionParams extends TiendaTable 
{
	function TiendaTableProductAttributeOptionParams ( &$db ) 
	{
		
		$tbl_key 	= 'productattributeoptionparam_id';
		$tbl_suffix = 'productattributeoptionparams';
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
		if (empty($this->productattributeoption_id))
		{
			$this->setError( JText::_( "Product Attribute Option Association Required" ) );
			return false;
		}
        if (empty($this->productattributeoptionparam_name))
        {
            $this->setError( JText::_( "Attribute Option Param Name Required" ) );
            return false;
        }
		return true;
	}
	
}
