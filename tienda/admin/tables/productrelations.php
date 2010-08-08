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

class TiendaTableProductRelations extends TiendaTable 
{
    function TiendaTableProductRelations( &$db ) 
    {
        $tbl_key    = 'productrelation_id';
        $tbl_suffix = 'productrelations';
        $this->set( '_suffix', $tbl_suffix );
        $name       = 'tienda';
        
        parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );   
    }
	
	function check()
	{
		if (empty($this->product_id_from))
		{
			$this->setError( JText::_( "Product From Required" ) );
			return false;
		}

		if (empty($this->product_id_to))
        {
            $this->setError( JText::_( "Product To Required" ) );
            return false;
        }
        
	    if (empty($this->relation_type))
        {
            $this->setError( JText::_( "Relation Type Required" ) );
            return false;
        }
		
		return true;
	}
}
