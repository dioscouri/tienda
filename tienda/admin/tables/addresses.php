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

JLoader::import( 'com_tienda.tables._base', JPATH_ADMINISTRATOR.DS.'components' );

class TiendaTableAddresses extends TiendaTable 
{
	/**
	 * 
	 * 
	 * @param $db
	 * @return unknown_type
	 */
	function TiendaTableAddresses ( &$db ) 
	{
		
		$tbl_key 	= 'address_id';
		$tbl_suffix = 'addresses';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'tienda';
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );	
	}
	
	/**
	 * First stores the record
	 * Then checks if it should be the default 
	 * 
	 * @see tienda/admin/tables/TiendaTable#store($updateNulls)
	 */
	function store( $updateNulls=false )
	{
        if ( $return = parent::store( $updateNulls ))
        {
            if ($this->is_default_shipping == '1' || $this->is_default_billing == '1')
            {
	        	// update the defaults
	            $query = new TiendaQuery();
	            $query->update( "#__tienda_addresses" );
	            $query->where( "`user_id` = '{$this->user_id}'" );
	            $query->where( "`address_id` != '{$this->address_id}'" );
	            if ($this->is_default_shipping == '1')
	            {
	                $query->set( "`is_default_shipping` = '0'" );
	            }
				if ($this->is_default_billing == '1')
				{
	                $query->set( "`is_default_billing` = '0'" );
				}
	            $this->_db->setQuery( (string) $query );
	            if (!$this->_db->query())
	            {
	            	$this->setError( $this->_db->getErrorMsg() );
	            	return false;
	            }
            }
        }
        return $return;
	}
	
	/**
	 * Checks the entry to maintain DB integrity 
	 * @return unknown_type
	 */
	function check()
	{
		if (empty($this->user_id))
		{
			$this->user_id = JFactory::getUser()->id;
		    if (empty($this->user_id))
	        {
	            $this->setError( "User Required" );
	            return false;
	        }
		}
	    if (empty($this->address_name))
        {
            $this->setError( "Address Title For Your Reference" );
            return false;
        }
		if (empty($this->first_name))
		{
			$this->setError( "First Name Required" );
			return false;
		}
	    if (empty($this->last_name))
        {
            $this->setError( "Last Name Required" );
            return false;
        }
	    if (empty($this->address_1))
        {
            $this->setError( "At Least One Address Line is Required" );
            return false;
        }
	    if (empty($this->city))
        {
            $this->setError( "City Required" );
            return false;
        }
	    if (empty($this->postal_code))
        {
            $this->setError( "City Required" );
            return false;
        }
	    if (empty($this->country_id))
        {
            $this->setError( "Country Required" );
            return false;
        }
	    if (empty($this->zone_id))
        {
            $this->setError( "Zone Required" );
            return false;
        }
		return true;
	}
}
