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

jimport('joomla.log.log');
JLog::addLogger(array('text_file' => 'tienda.baseeav.php'), JLog::ALL, array('BaseEAV'));

Tienda::load( 'TiendaTable', 'tables._base' );

class TiendaTableEav extends TiendaTable
{
	/**
	 * If this "mirrors" another table (ex orderitems => products), put the mirrored table suffix here
	 * On save the system will also mirror the eav values for the saved item
	 * @var string
	 */
	protected $_linked_table = '';

	/**
	 * If this "mirrors" another table (ex orderitems => products), put the mirrored table key here
	 * (ex: the product_id)
	 * @var int
	 */
	protected $_linked_table_key = 0;

	/**
	 * If this "mirrors" another table (ex orderitems => products), put name of the the mirrored table key
	 * (ex: product_id)
	 * @var int
	 */
	protected $_linked_table_key_name = '';
	
	
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
		Tienda::load( "TiendaHelperEav", 'helpers.eav' );

		$dispatcher = JDispatcher::getInstance();
		$before = $dispatcher->trigger( 'onBeforeStore'.$this->get('_suffix'), array( &$this ) );
		if (in_array(false, $before, true))
		{
			return false;
		}

		$key = $this->_tbl_key;
		$id = $this->$key;
		$post_id = JRequest::getInt( $key, null, 'post' ); // ID from post
		
		$app = JFactory::getApplication();
		$editable_by = $app->isAdmin() ? 1 : 2;

		// Get the custom fields for this entities
		$eavs = TiendaHelperEav::getAttributes( $this->get('_suffix'), $id, false, $editable_by );

		// Is this a mirrored table (see decription at the beginning of this file)
		if(strlen($this->_linked_table) && $this->_linked_table_key)
		{
			// Copy the custom field value to this table
			$mirrored_eavs = TiendaHelperEav::getAttributes( $this->_linked_table, $this->_linked_table_key, false, $editable_by );
			$eavs = array_merge($eavs, $mirrored_eavs);
		}
		 
		$custom_fields = array();
		
		// Is this a mirrored table (see decription at the beginning of this file)
		// Get is eavs first, and then override them with values from the request
		if(strlen($this->_linked_table) && $this->_linked_table_key)
		{
			// Copy the custom field value to this table
			$mirrored_eavs = TiendaHelperEav::getAttributes( $this->_linked_table, $this->_linked_table_key, false, $editable_by );

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
						//						if( $id === null || $post_id == $id )
						$value = TiendaHelperEav::getAttributeValue($eav, $this->_linked_table, $this->_linked_table_key, !( $id === null || $post_id == $id ), false );
						//						else
						//							$value = null;

						// Store it into the array for eav values
						if( $value !== null )
						$custom_fields[$key] = array('eav' => $eav, 'value' => $value);
					}
					else
					{
						$value = $this->$key;
						unset($this->$key);
						// Store it into the array for eav values
						$custom_fields[$key] = array('eav' => $eav, 'value' => $value);
					}
				}
			}

		}

		// If there are Custom Fields in this object
		if(count($eavs))
		{
			foreach($eavs as $eav)
			{
				$key = $eav->eavattribute_alias;

				// Check if the key exists in this object (could be a ->load() ->set() ->store() workflow)
				if( property_exists($this, $key))
				{
					// Fetch the value from the post (if any) and overwrite the object value if it exists
					if( $eav->eavattribute_type == 'text' )
						$value = JRequest::getVar($key, null, 'post','string', JREQUEST_ALLOWHTML );
					else
						$value = JRequest::getVar($key, null, 'post');
					if($value === null)
					{
						// If not, use the object value
						$value = $this->$key;
					}
						
					unset($this->$key);
						
					// Store it into the array for eav values
					$custom_fields[$key] = array('eav' => $eav, 'value' => $value);
				}
				// It wasn't in the object, but is it in the post? (new value)
				else
				{
					if( $id === null || $post_id == $id ) // read post only if the post variables belong to this entity
					{
						// Fetch the value from the post (if any)
						$value = JRequest::getVar($key, null, 'post');
					}
					else
					{
						$value = null;
					}

					if($value !== null)
					{
						// Store it into the array for eav values
						$custom_fields[$key] = array('eav' => $eav, 'value' => $value);
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
				$this->setError(JText::_('COM_TIENDA_EAV_DELETE_FAILED') . $msg);
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
		$app = JFactory::getApplication();
		$editable_by = $app->isAdmin() ? 1 : 2;
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
				$this->setError( JText::_('COM_TIENDA_CANNOT_LOAD_WITH_EMPTY_KEY') );
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
		
		if( $load_eav )
		{

			Tienda::load( "TiendaHelperBase", 'helpers._base' );
			$eav_helper = TiendaHelperBase::getInstance( 'Eav' );
			$k = $this->_tbl_key;
			$id = $this->$k;
				
			// Get the custom fields for this entities
			Tienda::load('TiendaHelperEav', 'helpers.eav');
			$eavs = TiendaHelperEav::getAttributes( $this->get('_suffix'), $id, true, $editable_by );
				
			// Is this a mirrored table (see decription at the beginning of this file)
			if(strlen($this->_linked_table) && $this->_linked_table_key)
			{
				// Copy the custom field value to this table
				$mirrored_eavs = $eav_helper->getAttributes( $this->_linked_table, $this->_linked_table_key, true, $editable_by );
				$eavs = array_merge($eavs, $mirrored_eavs);
			}
		}
		
		foreach ($oid as $key=>$value)
		{
			// Check that $key is field in table
			if ( !in_array( $key, array_keys( $this->getProperties() ) ) )
			{
				// Check if it is a eav field
				if($load_eav)
				{						
					// loop through until the key is found or the eav are finished
					$found = false;
					$i = 0;
						
					while(!$found && ($i < count($eavs)))
					{
						// Does the key exists?
						if( $key == $eavs[$i]->eavattribute_alias)
						{
							$found = true;
						}
						else
							$i++;
					}
						
					// Was the key found?
					if(!$found)
					{
						// IF not return an error
						$this->setError( get_class( $this ).' does not have the field '.$key );
						return false;
					}

					// key was found -> add this EAV field
					$value_tbl_name = 'value_'.$eavs[$i]->eavattribute_alias;
					// for some reason MySQL makes spaces around '-' charachter 
					// (which is often charachter in aliases) that's why we replace it with '_'
					$value_tbl_name = str_replace("-", "_", $value_tbl_name);
					// Join the table based on the type of the value
					$table_type = $eav_helper->getType( $eavs[$i]->eavattribute_alias );
					// Join the tables
					$query->join('LEFT', '#__tienda_eavvalues'.$table_type.' AS '.$value_tbl_name.' ON ( '.$value_tbl_name.'.eavattribute_id = '.$eavs[$i]->eavattribute_id.' AND '.$value_tbl_name.'.eaventity_id =  '.$this->_tbl_key.' )' );
					// Filter using '='
					$query->where($value_tbl_name.".eavvalue_value = '".$value."'"); 
					// else let the store() method worry over this
					 
					 
				}
				else
				{
					$this->setError( get_class( $this ).' does not have the field '.$key );
					return false;
				}
			}
			else
			{
				// add the key=>value pair to the query
				$value = $db->Quote( $db->getEscaped( trim( strtolower( $value ) ) ) );
				$query->where( $key.' = '.$value);
			}
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
					$eavs = TiendaHelperEav::getAttributes( $this->get('_suffix'), $id, false, $editable_by );
						
					// Is this a mirrored table (see decription at the beginning of this file)
					if(strlen($this->_linked_table) && $this->_linked_table_key)
					{
						// Copy the custom field value to this table
						$mirrored_eavs = $eav_helper->getAttributes( $this->_linked_table, $this->_linked_table_key );
						$eavs = array_merge($eavs, $mirrored_eavs);
					}

					if(count($eavs))
					{
						foreach($eavs as $eav)
						{
							$key = $eav->eavattribute_alias;

							$value = $eav_helper->getAttributeValue($eav, $this->get('_suffix'), $id);

							$this->{$key} = $value;
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

	public function getLinkedTable()
	{
		return $this->_linked_table;
	}

	public function getLinkedTableKey()
	{
		return $this->_linked_table_key;
	}

	public function getLinkedTableKeyName()
	{
		return $this->_linked_table_key_name;
	}
	
}
