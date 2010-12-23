<?php
/**
 * @version    1.5
 * @package    Tienda
 * @author     Dioscouri Design
 * @link     http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

Tienda::load( 'TiendaTable', 'tables._base' );

class TiendaTableEavAttributes extends TiendaTable
{
	function TiendaTableEavAttributes( &$db)
	{
		$tbl_key 	= 'eavattribute_id';
		$tbl_suffix = 'eavattributes';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'tienda';
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );	
	}
	
    function check()
    {      
    	if (empty($this->eavattribute_label))
        {
            $this->setError( JText::_( "Label Required" ) );
            return false;
        }  
        if (empty($this->eaventity_type))
        {
            $this->setError( JText::_( "Entity Type Required" ) );
            return false;
        }
    	if (empty($this->eavattribute_type))
        {
            $this->setError( JText::_( "Type Required" ) );
            return false;
        }
        if (empty($this->eavattribute_alias)) 
        {
            $this->eavattribute_alias = $this->eavattribute_label;
        }
        $this->eavattribute_alias = $this->stringDBSafe($this->eavattribute_alias);
        
        return true;
    }
}
