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

JLoader::import( 'com_tienda.tables._basenested', JPATH_ADMINISTRATOR.DS.'components' );

class TiendaTableCategories extends TiendaTableNested 
{
	/**
	 * Constructs the object
	 * @param $db
	 * @return unknown_type
	 */
	function TiendaTableCategories ( &$db ) 
	{
		$tbl_key 	= 'category_id';
		$tbl_suffix = 'categories';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'tienda';
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );	
	}
	
	/**
	 * Checks the integrity of the object before a save 
	 * @return unknown_type
	 */
	function check()
	{		
		$db			= $this->getDBO();
		$nullDate	= $db->getNullDate();
		if (empty($this->created_date) || $this->created_date == $nullDate)
		{
			$date = JFactory::getDate();
			$this->created_date = $date->toMysql();
		}
		if (empty($this->modified_date) || $this->modified_date == $nullDate)
		{
			$date = JFactory::getDate();
			$this->modified_date = $date->toMysql();
		}
		$this->filterHTML( 'category_name' );
		if (empty($this->category_name))
		{
			$this->setError( JText::_( "Name Required" ) );
			return false;
		}
		
		return true;
	}
	
	/**
	 * Stores the object
	 * @param object
	 * @return boolean
	 */
	function store() 
	{
		$date = JFactory::getDate();
		$this->modified_date = $date->toMysql();
		$store = parent::store();		
		return $store;		
	}
	
	/**
	 * Attempts base getRoot() and if it fails, creates root entry
	 * 
	 * @see tienda/admin/tables/TiendaTableNested#getRoot()
	 */
	function getRoot()
	{
		if (!$result = parent::getRoot())
		{
			// add root
			$database = $this->_db;
			$query = "INSERT IGNORE INTO `#__tienda_categories` SET `category_name` = 'All Categories', `category_description` = '', `parent_id` = '0', `lft` = '1', `rgt` = '2', `category_enabled` = '1', `isroot` = '1'; ";
			$database->setQuery( $query );
			if ($database->query())
			{
				$insertid = $database->insertid();					
				JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
				$table = JTable::getInstance('Categories', 'TiendaTable');
				$table->load( $insertid );
				$table->rebuildTree();
				$result = $table;
			}
				else
			{
				$this->setError( $database->getErrorMsg() );
				return false;
			}
		}
		
		return $result;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see tienda/admin/tables/TiendaTableNested#getTree($parent, $enabled, $indent)
	 */
	function getTree( $parent=null, $enabled='1', $indent=' ' )
	{
		if (intval($enabled) > 0)
		{
			$enabled_query = "
			AND node.category_enabled = '1'
			AND NOT EXISTS
			(
				SELECT 
					* 
				FROM 
					{$this->_tbl} AS tbl 
				WHERE 
					tbl.lft < node.lft AND tbl.rgt > node.rgt
					AND tbl.category_enabled = '0' 
				ORDER BY 
					tbl.lft ASC
			)
			";			 
		}
		
		$key = $this->getKeyName();		
		$query = "
			SELECT node.*, COUNT(parent.{$key}) AS level, CONCAT( REPEAT('{$indent}', COUNT(parent.category_name) - 1), node.category_name) AS name
			FROM {$this->_tbl} AS node,
			{$this->_tbl} AS parent
			WHERE node.lft BETWEEN parent.lft AND parent.rgt
			$enabled_query
			GROUP BY node.{$key}
			ORDER BY node.lft;
		";
		$this->_db->setQuery( $query );
		$return = $this->_db->loadObjectList();
		return $return;		
	}
	
	/**
	 * 
	 * @param $parent
	 * @return unknown_type
	 */
	function updateParents( $parent=null )
	{
		$key = $this->getKeyName();
		if ($parent === null)
		{
			$root = $this->getRoot();
			if ($root === false) 
			{
				return false;
			}
			$parent = $root->$key;
		}
		
		$database = $this->_db;
		$tbl = $this->getTableName();
		
		$query = "
			UPDATE 
				{$tbl} AS tbl
			SET
				tbl.parent_id = '{$parent}'
			WHERE 
				tbl.parent_id = '0'
			AND 
				tbl.{$key} != '{$parent}'
		";
		$database->setQuery( $query );
		$database->query();		
	}
}
