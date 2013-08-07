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

class TiendaTableWishlists extends TiendaTable 
{
	function TiendaTableWishlists ( &$db ) 
	{
		$tbl_key 	= 'wishlist_id';
		$tbl_suffix = 'wishlists';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'tienda';
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );	
	}
    
    function check()
    {        
        if (empty($this->user_id))
        {
            $this->setError( JText::_('COM_TIENDA_USER_REQUIRED') );
        }

        if (empty($this->wishlist_name))
        {
            // count the number of lists for this user
            $query = "SELECT COUNT(*) FROM #__tienda_wishlists WHERE user_id = '" . (int) $this->user_id . "'";
            $db = $this->getDBO();
            $db->setQuery( $query );
            $count = $db->loadResult();
            
            $this->wishlist_name = "List " . ($count+1);
        }
        
        return parent::check();
    }
    
    function delete( $oid=null, $doReconciliation=true )
    {
        $k = $this->_tbl_key;
        if ($oid) {
            $this->$k = intval( $oid );
        }
        
        $id = $this->$k; 
        
        if ($return = parent::delete( $oid ))
        {
            if ($id) 
            {
                $query = "UPDATE #__tienda_wishlistitems SET wishlist_id = '0' WHERE wishlist_id = '". $id . "'";
                $db = $this->getDBO();
                $db->setQuery( $query );
                $db->query();                
            }
        }
        
        return parent::check();
    }
}
