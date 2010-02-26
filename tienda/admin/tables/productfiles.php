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

class TiendaTableProductFiles extends TiendaTable 
{
	/**
	 * 
	 * 
	 * @param $db
	 * @return unknown_type
	 */
	function TiendaTableProductFiles ( &$db ) 
	{
		
		$tbl_key 	= 'file_id';
		$tbl_suffix = 'productfiles';
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
		if (empty($this->product_id))
		{
			$this->setError( JText::_( "Product Association Required" ) );
			return false;
		}
        if (empty($this->file_name))
        {
            $this->setError( JText::_( "Attribute Name Required" ) );
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
        parent::reorder('product_id = '.$this->_db->Quote($this->product_id) );
    }
	
}
