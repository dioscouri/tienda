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

class TiendaTableCreditTypes extends TiendaTable 
{
	function TiendaTableCreditTypes ( &$db ) 
	{
		
		$tbl_key 	= 'credittype_id';
		$tbl_suffix = 'credittypes';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'tienda';
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );	
	}
	
	function check()
	{
	    if (empty($this->credittype_code))
        {
            $this->setError( JText::_('Credit Type Code Cannot Be Empty') );
            return false;
        }
        
		$nullDate	= $this->_db->getNullDate();
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
