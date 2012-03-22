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

Tienda::load( 'TiendaTableXref', 'tables._basexref' );

class TiendaTableProductCategories extends TiendaTableXref 
{
	/** 
	 * @param $db
	 * @return unknown_type
	 */
	function TiendaTableProductCategories ( &$db ) 
	{
		$keynames = array();
		$keynames['product_id']  = 'product_id';
        $keynames['category_id'] = 'category_id';
        $this->setKeyNames( $keynames );
                
		$tbl_key 	= 'product_id';
		$tbl_suffix = 'productcategoryxref';
		$name 		= 'tienda';
		
		$this->set( '_tbl_key', $tbl_key );
		$this->set( '_suffix', $tbl_suffix );
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );	
	}
	
	function check()
	{
		if (empty($this->category_id))
		{
			$this->setError( JText::_('Category Required') );
			return false;
		}
		if (empty($this->product_id))
		{
			$this->setError( JText::_('Product Required') );
			return false;
		}
		
		return true;
	}
}
