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

class TiendaTableEavAttributeEntities extends TiendaTableXref 
{
	/** 
	 * @param $db
	 * @return unknown_type
	 */
	function TiendaTableEavAttributeEntities ( &$db ) 
	{
		$keynames = array();
		$keynames['eaventity_id']  = 'eaventity_id';
		$keynames['eaventity_type']  = 'eaventity_type';
        $keynames['eavattribute_id'] = 'eavattribute_id';
        $this->setKeyNames( $keynames );
                
		$tbl_key 	= 'eaventity_id';
		$tbl_suffix = 'eavattributeentityxref';
		$name 		= 'tienda';
		
		$this->set( '_tbl_key', $tbl_key );
		$this->set( '_suffix', $tbl_suffix );
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );	
	}
	
	function check()
	{
		if (empty($this->eavattribute_id))
		{
			$this->setError( JText::_('COM_TIENDA_CATEGORY_REQUIRED') );
			return false;
		}
		if (empty($this->eaventity_id))
		{
			$this->setError( JText::_('COM_TIENDA_ENTITY_REQUIRED') );
			return false;
		}
		if (empty($this->eaventity_type))
		{
			$this->setError( JText::_('COM_TIENDA_ENTITY_TYPE_REQUIRED') );
			return false;
		}
		
		return true;
	}
}
