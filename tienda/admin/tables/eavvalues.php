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

class TiendaTableEavValues extends TiendaTable
{
	// If the table type was set
	private $active = false;
	
	// Allowed table types
	private $allowed_types = array();
	
	function __construct( $tbl_name, $tbl_key, &$db )
	{
		
		$this->allowed_types = array('int', 'varchar', 'decimal', 'text', 'datetime');		
		
		// do NOT parent::__construct, do the two thing that we can do now
		$this->_tbl_key	= 'eavvalue_id';
		$this->_db		=& $db;
		
		// do NOT set table properties based on table's fields
		// delegate this to the setType() method
	}
	
	/**
	 * 
	 * Set the type of the table, to correctly use the related db table (eavvaluesvarchar, etc)
	 * @param string $type
	 */
	public function setType($type)
	{
		// Check the type
		$type = strtolower($type);
		if(!in_array($type, $this->allowed_types))
		{
			$type = 'varchar'; // default to varchar
		}
		
		$name 		= 'tienda';
		$eav_suffix = 'eavvalues';
		
		// Set the correct suffix
		$this->set( '_suffix', $eav_suffix.$type );
		$tbl_name =  "#__{$name}_{$eav_suffix}{$type}";
		$this->_tbl = $tbl_name;
		
		// Now set the properties!
		$this->setTableProperties();
		
		// Table Type defined: Activate the table
		$this->active = true;
	}
	
	public function store( $updateNulls=false )
	{
		// Check the table activation status first
		if(!$this->active)
		{
			// Activate it with a default value
			$this->setState('');
		}
		
		parent::store( $updateNulls );
	}
	
	public function load( $oid=null, $reset=true)
	{
		// Check the table activation status first
		if(!$this->active)
		{
			// Activate it with a default value
			$this->setState('');
		}
		
		parent::load( $oid, $reset );
	}
	
	public function save()
	{
		// Check the table activation status first
		if(!$this->active)
		{
			// Activate it with a default value
			$this->setState('');
		}
		
		parent::save();
	}
	
	public function reset()
	{
		// Check the table activation status first
		if(!$this->active)
		{
			// Activate it with a default value
			$this->setState('');
		}
		
		parent::reset();
	}
	
	public function check()
	{
		// Check the table activation status first
		if(!$this->active)
		{
			// Activate it with a default value
			$this->setState('');
		}
		
		parent::check();
	}
	
	public function delete( $oid = null )
	{
		// Check the table activation status first
		if(!$this->active)
		{
			// Activate it with a default value
			$this->setState('');
		}
		
		parent::delete( $oid );
	}
	
	public function move( $change, $where = '' )
	{
		// Check the table activation status first
		if(!$this->active)
		{
			// Activate it with a default value
			$this->setState('');
		}
		
		parent::move( $change, $where );
	}
	
	public function bind( $from, $ignore=array() )
	{
		// Check the table activation status first
		if(!$this->active)
		{
			// Activate it with a default value
			$this->setState('');
		}
		
		parent::bind( $from, $ignore );
	}
	
	public function getAllowedTypes()
	{
		return $this->allowed_types;
	}
}
