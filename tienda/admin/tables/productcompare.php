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

class TiendaTableProductCompare extends TiendaTableEav 
{
    /**
     * @param $db
     * @return unknown_type
     */
    function TiendaTableProductCompare ( &$db ) 
    {
        $keynames = array();
        $keynames['user_id']    = 'user_id';
        $keynames['session_id'] = 'session_id';
        $keynames['product_id'] = 'product_id';
        
        // load the plugins (when loading this table outside of tienda, this is necessary)
        JPluginHelper::importPlugin( 'tienda' );
       
        $this->setKeyNames( $keynames );
    	
        $tbl_key      = 'productcompare_id';
        $tbl_suffix   = 'productcompare';
        $name         = 'tienda';
        
        $this->set( '_tbl_key', $tbl_key );
        $this->set( '_suffix', $tbl_suffix );
        
        $this->_linked_table = 'products';
        
        parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );    
    }
    
    function check()
    {        
        if (empty($this->user_id) && empty($this->session_id))
        {
            $this->setError( JText::_('COM_TIENDA_USER_OR_SESSION_REQUIRED') );
            return false;
        }
        
        if (empty($this->product_id))
        {
            $this->setError( JText::_('COM_TIENDA_PRODUCT_REQUIRED') );
            return false;
        }        
    
        return true;
    }
    
    
    /**
     * (non-PHPdoc)
     * @see tienda/admin/tables/TiendaTable#delete($oid)
     */
    function delete( $oid='' )
    {
        if (empty($oid))
        {
            // if empty, use the values of the current keys
            $keynames = $this->getKeyNames();
            foreach ($keynames as $key=>$value)
            {
                $oid[$key] = $this->$key; 
            }
            if (empty($oid))
            {
                // if still empty, fail
                $this->setError( JText::_('COM_TIENDA_CANNOT_DELETE_WITH_EMPTY_KEY') );
                return false;
            }
        }
        
        if (!is_array($oid))
        {
            $keyName = $this->getKeyName();
            $arr = array();
            $arr[$keyName] = $oid; 
            $oid = $arr;
        }

        $dispatcher = JDispatcher::getInstance();
        $before = $dispatcher->trigger( 'onBeforeDelete'.$this->get('_suffix'), array( $this, $oid ) );
        if (in_array(false, $before, true))
        {
            return false;
        }
        
        $db = $this->getDBO();
        
        // initialize the query
        $query = new TiendaQuery();
        $query->delete();
        $query->from( $this->getTableName() );
        
        foreach ($oid as $key=>$value)
        {
            // Check that $key is field in table
            if ( !in_array( $key, array_keys( $this->getProperties() ) ) )
            {
                $this->setError( get_class( $this ).' does not have the field '.$key );
                return false;
            }
            // add the key=>value pair to the query
            $value = $db->Quote( $db->getEscaped( trim( strtolower( $value ) ) ) );
            $query->where( $key.' = '.$value);
        }

        $db->setQuery( (string) $query );

        if ($db->query())
        {
            $dispatcher = JDispatcher::getInstance();
            $dispatcher->trigger( 'onAfterDelete'.$this->get('_suffix'), array( $this, $oid ) );
            return true;
        }
        else
        {
            $this->setError($db->getErrorMsg());
            return false;
        }
    }
    
	function store( $updateNulls=false )
	{
		$this->_linked_table_key = $this->product_id;
		return parent::store();
	}
}
