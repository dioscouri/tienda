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

class TiendaTableProductAttributeOptionValues extends TiendaTable 
{
	function TiendaTableProductAttributeOptionValues ( &$db ) 
	{
		
		$tbl_key 	= 'productattributeoptionvalue_id';
		$tbl_suffix = 'productattributeoptionvalues';
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
			$this->setError( JText::_('COM_TIENDA_PRODUCT_ATTRIBUTE_OPTION_ASSOCIATION_REQUIRED') );
			return false;
		}
        if (empty($this->productattributeoptionvalue_value))
        {
            $this->setError( JText::_('COM_TIENDA_ATTRIBUTE_OPTION_VALUE_REQUIRED') );
            return false;
        }
		return true;
	}
	
    /**
     * Adds context to the default reorder method
     * @return unknown_type
     */
    function reorder($where = '')
    {
        parent::reorder('productattributeoption_id = '.$this->_db->Quote($this->productattributeoption_id) );
    }
}
