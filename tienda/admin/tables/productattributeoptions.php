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

class TiendaTableProductAttributeOptions extends TiendaTable 
{
	function TiendaTableProductAttributeOptions ( &$db ) 
	{
		
		$tbl_key 	= 'productattributeoption_id';
		$tbl_suffix = 'productattributeoptions';
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
		if (empty($this->productattribute_id))
		{
			$this->setError( JText::_( "Product Attribute Association Required" ) );
			return false;
		}
        if (empty($this->productattributeoption_name))
        {
            $this->setError( JText::_( "Attribute Option Name Required" ) );
            return false;
        }
		return true;
	}
	
    /**
     * Adds context to the default reorder method
     * @return unknown_type
     */
    function reorder()
    {
        parent::reorder('productattribute_id = '.$this->_db->Quote($this->productattribute_id) );
    }

    /**
     * Run function when saving
     * @see tienda/admin/tables/TiendaTable#save()
     */
    function save()
    {
    	$return = parent::save();
    	
    	$pa = JTable::getInstance('ProductAttributes', 'TiendaTable');
    	$pa->load( $this->productattribute_id );
    	
        Tienda::load( "TiendaHelperProduct", 'helpers.product' );
        TiendaHelperProduct::doProductQuantitiesReconciliation( $pa->product_id, '0' );
        
    	return $return;
    }
	
}
