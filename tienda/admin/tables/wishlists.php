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

Tienda::load( 'TiendaTableEav', 'tables._baseeav' );

class TiendaTableWishlists extends TiendaTableEav 
{
    /**
     * @param $db
     * @return unknown_type
     */
    function TiendaTableWishlists ( &$db ) 
    {
        $keynames = array();
        $keynames['user_id']    = 'user_id';
        $keynames['product_id'] = 'product_id';
        $keynames['product_attributes'] = 'product_attributes';

        // load the plugins (when loading this table outside of tienda, this is necessary)
        JPluginHelper::importPlugin( 'tienda' );
        
        
        $this->setKeyNames( $keynames );
    	
        $tbl_key      = 'wishlist_id';
        $tbl_suffix   = 'wishlists';
        $name         = 'tienda';
        
        $this->set( '_tbl_key', $tbl_key );
        $this->set( '_suffix', $tbl_suffix );
        
        parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );    
    }
    
    function check()
    {        
        if (empty($this->user_id))
        {
            $this->setError( JText::_('COM_TIENDA_USER_REQUIRED') );
            return false;
        }
      	

        
        return true;
    }
  
}
