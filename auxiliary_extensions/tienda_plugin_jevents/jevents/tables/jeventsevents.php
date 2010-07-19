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

class TiendaTableJEventsEvents extends TiendaTable 
{
    /**
     * 
     * This table should correspond to the __jevents_vevent
     * @param $db
     */
    function TiendaTableJEventsEvents ( &$db ) 
    {
        $tbl_key    = 'ev_id';
        $name       = 'jevents';
        $tbl_suffix  = 'vevent';
        
        $this->set( '_tbl_key', $tbl_key );
        $this->set( '_suffix', $tbl_suffix );
        
        parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );   
    }
}
