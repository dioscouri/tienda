<?php
/**
 * @version	0.1
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

Tienda::load( 'TiendaTable', 'tables._base' );

class TiendaTableEav extends TiendaTable
{
	/**
	 * Inserts a new row if id is zero or updates an existing row in the database table
	 * Check for custom fields and store them in the right table
	 * Can be overloaded/supplemented by the child class
	 *
	 * @access public
	 * @param boolean If false, null object variables are not updated
	 * @return null|string null if successful otherwise returns and error message
	 */
	function store( $updateNulls=false )
	{
		$dispatcher = JDispatcher::getInstance();
		$before = $dispatcher->trigger( 'onBeforeStore'.$this->get('_suffix'), array( &$this ) );
		if (in_array(false, $before, true))
		{
			return false;
		}

		$key = $this->_tbl_key;
		$id = $this->$key;
		
		// Get the custom fields for this entities
		Tienda::load('TiendaHelperEav', 'helpers.eav');
		$eavs = TiendaHelperEav::getAttributes( $this->get('_suffix'), $id );
		$custom_fields = array();
		
		// If there are Custom Fields
		if(count($eavs))
		{
			foreach($eavs as $eav)
			{
				$key = $eav->eavattribute_alias;
				
				// Fetch the value from the post
				$value = JRequest::getVar($key, null, 'post');
				if($value !== null)
				{
					// Store it into the array for eav values
					$custom_fields[] = array('eav' => $eav, 'value' => $value);
				}
			}
		}
		
		if ( $return = parent::store( $updateNulls ))
		{	
			// Store custom fields if needed
			if(count($custom_fields))
			{
				foreach($custom_fields as $cf)
				{
					// get the value table
		    		$table = JTable::getInstance('EavValues', 'TiendaTable');
		    		// set the type based on the attribute
		    		$table->setType($cf['eav']->eavattribute_type);
		    	
		    		// load the value based on the entity id
		    		$keynames = array();
		    		$keynames['eavattribute_id'] = $cf['eav']->eavattribute_id; 
		    		$keynames['eaventity_id'] = $id;
		    		$loaded = $table->load($keynames);
		    		
		    		// Add the value if it's a first time save
		    		if(!$loaded)
		    		{
		    			$table->eavattribute_id = $cf['eav']->eavattribute_id;
		    			$table->eaventity_id = $id;
		    		}
		    		
		    		// Store the value
		    		$table->eavvalue_value = $cf['value'];
		    		$stored = $table->store();
		    		
		    		// Log the errors
		    		if(!$stored)
		    		{
		    			if(strlen($this->getError()))
		    			{
			    			$this->setError($this->getError());
			    			return false;
		    			}
		    		}
		    		
				}
			}
			
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger( 'onAfterStore'.$this->get('_suffix'), array( $this ) );
		}
		return $return;
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
                $this->setError( JText::_( "Cannot delete with empty key" ) );
                return false;
            }
        }
        $oid = (array) $oid;

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
}
