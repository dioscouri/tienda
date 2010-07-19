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

class TiendaTableProductjevent extends TiendaTable 
{
    function TiendaTableProductjevent ( &$db ) 
    {
        
        $tbl_key    = 'productevent_id';
        $name       = 'tienda';
        $tbl_suffix  = 'productevent';
        $this->set( '_tbl_key', $tbl_key );
        $this->set( '_suffix', $tbl_suffix );
        parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );   
    }
}
