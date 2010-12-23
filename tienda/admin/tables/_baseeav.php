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
	 * If this "mirrors" another table (ex orderitems => products), put the mirrored table suffix here
	 * On save the system will also mirror the eav values for the saved item
	 * @var string
	 */
	protected $linked_table = '';
	
	/**
	 * If this "mirrors" another table (ex orderitems => products), put the mirrored table key here
	 * (ex: the product_id)
	 * @var int
	 */
	protected $linked_table_key = 0;
	
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
				
				// Check if the key exists in this object (could be a ->load() ->set() ->store() workflow)
				if( property_exists($this, $key))
				{
					// Fetch the value from the post (if any) and overwrite the object value if it exists
					$value = JRequest::getVar($key, null, 'post');
					if($value === null)
					{
						// If not, use the object value
						$value = $this->$key;
					}
					
					// Store it into the array for eav values
					$custom_fields[] = array('eav' => $eav, 'value' => $this->$key);
				}
				// It wasn't in the object, but is it in the post? (new value)
				else
				{
					// Fetch the value from the post (if any)
					$value = JRequest::getVar($key, null, 'post');
					if($value !== null)
					{
						// Store it into the array for eav values
						$custom_fields[] = array('eav' => $eav, 'value' => $value);
					}
				}
			}
		}
		
		// Is this a mirrored table (see decription at the beginning of this file)
    	if(strlen($this->linked_table) && $this->linked_table_key)
    	{
    		// Copy the custom field value to this table
    		$mirrored_eavs = TiendaHelperEav::getAttributes( $this->linked_table, $this->linked_table_key );
    		
	    	// If there are Custom Fields for the linked key
			if(count($mirrored_eavs))
			{
				foreach($mirrored_eavs as $eav)
				{
					$key = $eav->eavattribute_alias;
					
					// Check if the key exists in this object (already mirrored)
					if( !property_exists($this, $key))
					{
						// Get the value
						$value = TiendaHelperEav::getAttributeValue($eav, $this->linked_table, $this->linked_table_key);
						
						// Store it into the array for eav values
						$custom_fields[] = array('eav' => $eav, 'value' => $value);
					}
				}
			}
    		
    	}
		
		if ( $return = parent::store( $updateNulls ))
		{	
			// Store custom fields if needed
			if(count($custom_fields))
			{
				$key = $this->_tbl_key;
				$id = $this->$key;
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
		    		$table->eaventity_type = $this->get('_suffix');
		    		$stored = $table->store();
		    		
		    		// Log the errors
		    		if(!$stored)
		    		{
		    			if(strlen($this->getError()))
		    			{
			    			$this->setError($this->getError());
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
	    $dispatcher = JDispatcher::getInstance();
        $before = $dispatcher->trigger( 'onBeforeDelete'.$this->get('_suffix'), array( $this, $oid ) );
        if (in_array(false, $before, true))
        {
            return false;
        }
        
		if ( $return = parent::delete( $oid ))
		{
			// Delete also the values for that product in EAV tables
			$key = $this->_tbl_key;
			$id = $this->$key;
			
			// Get the custom fields for this entities
			Tienda::load('TiendaHelperEav', 'helpers.eav');
			$eavs = TiendaHelperEav::getAttributes( $this->get('_suffix'), $id );
			
			$error = false;
			$msg = '';
			
			foreach(@$eavs as $eav)
			{
				// get the value table
	    		$table = JTable::getInstance('EavValues', 'TiendaTable');
	    		// set the type based on the attribute
	    		$table->setType($eav->eavattribute_type);
	    		
	    		// load the value based on the entity id
	    		$keynames = array();
	    		$keynames['eavattribute_id'] = $eav->eavattribute_id; 
	    		$keynames['eaventity_id'] = $id;
	    		
	    		// If the value exists for this entity
	    		if($table->load($keynames))
	    		{
	    			// delete the value
	    			$result = $table->delete();
	    			if(!$result)
	    			{
	    				$error = true;
	    				$msg = $table->getError();
	    			}
	    		}
			}
			
			// log eav errors
			if($error)
			{
				$this->setError(JText::_('EAV Delete failed: ') . $msg);
				return false;
			}
				
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger( 'onAfterDelete'.$this->get('_suffix'), array( $this, $oid ) );
		}
		return $return;
	}
	
	/**
	 * Loads a row from the database and binds the fields to the object properties
	 * If $load_eav is true, binds also the eav fields linked to this entity
	 *
	 * @access	public
	 * @param	mixed	Optional primary key.  If not specifed, the value of current key is used
	 * @param	bool	reset the object values?
	 * @param	bool	load the eav values for this object
	 * 
	 * @return	boolean	True if successful
	 */
	function load( $oid=null, $reset=true, $load_eav = true )
	{	
		if (!is_array($oid))
		{
			// load by primary key if not array
			$keyName = $this->getKeyName();
			$oid = array( $keyName => $oid );
		}
		
		if (empty($oid))
		{
			// if empty, use the value of the current key
			$keyName = $this->getKeyName();
			$oid = $this->$keyName;
			if (empty($oid))
			{
				// if still empty, fail
				$this->setError( JText::_( "Cannot load with empty key" ) );
                return false;
			}
		}

        // allow $oid to be an array of key=>values to use when loading
        $oid = (array) $oid;
		
        if (!empty($reset))
        {
            $this->reset();
        }

        $db = $this->getDBO();
        
        // initialize the query
        $query = new TiendaQuery();
        $query->select( '*' );
        $query->from( $this->getTableName() );
        
		foreach ($oid as $key=>$value)
		{
            // Check that $key is field in table
            if ( !in_array( $key, array_keys( $this->getProperties() ) ) )
            {
            	// Check if it is a eav field
            	if($load_eav)
            	{
            		$k = $this->_tbl_key;
					$id = $this->$k;
					
					// Get the custom fields for this entities
					Tienda::load('TiendaHelperEav', 'helpers.eav');
					$eavs = TiendaHelperEav::getAttributes( $this->get('_suffix'), $id );
					
					// Is this a mirrored table (see decription at the beginning of this file)
			    	if(strlen($this->linked_table) && $this->linked_table_key)
			    	{
			    		// Copy the custom field value to this table
			    		$mirrored_eavs = TiendaHelperEav::getAttributes( $this->linked_table, $this->linked_table_key );
			    		$eavs = array_merge($eavs, $mirrored_eavs);
			    	}
					
					// loop through until the key is found or the eav are finished
					$found = false;
					$i = 0;
					while(!$found && ($i < count($eavs)))
					{
						// Does the key exists?
						if( $key == $eav[$i]->eavattribute_alias)
						{
							$found = true;
						}
						
						$i++;
					}
					
					// Was the key found?
					if(!$found)
					{
						// IF not return an error
						$this->setError( get_class( $this ).' does not have the field '.$key );
                		return false;	
					}
					
					// else let the store() method worry over this
            	}
                else
                {
                	$this->setError( get_class( $this ).' does not have the field '.$key );
                	return false;	
                }
            }
            // add the key=>value pair to the query
            $value = $db->Quote( $db->getEscaped( trim( strtolower( $value ) ) ) );
            $query->where( $key.' = '.$value);
		}
		
		$db->setQuery( (string) $query );
		
	    if ( $result = $db->loadAssoc() )
        {
        	$result = $this->bind($result);
        	
        	if( $result )
        	{
        		// Only now load the eav, in necessary
        		// Check if it is a eav field
            	if($load_eav)
            	{
            		$k = $this->_tbl_key;
					$id = $this->$k;
					
					// Get the custom fields for this entities
					Tienda::load('TiendaHelperEav', 'helpers.eav');
					$eavs = TiendaHelperEav::getAttributes( $this->get('_suffix'), $id );
					
					if(count($eavs))
					{
		            	foreach($eavs as $eav)
			    		{
			    			$key = $eav->eavattribute_alias;
			    			
			    			$value = TiendaHelperEav::getAttributeValue($eav, $this->get('_suffix'), $id);
			    			
		    				$item->{$key} = $value;
			    		}
					}
            	}
        		
        		$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger( 'onLoad'.$this->get('_suffix'), array( &$this ) );	
        	}
            
			return $result;
        }
        else
        {
            $this->setError( $db->getErrorMsg() );
            return false;
        }
	}
	
}
