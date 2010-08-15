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
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model' );
Tienda::load( 'TiendaQuery', 'library.query' );

class TiendaModelBase extends JModel
{
    /**
     * Method to get a table object, load it if necessary.
     *
     * @access  public
     * @param   string The table name. Optional.
     * @param   string The class prefix. Optional.
     * @param   array   Configuration array for model. Optional.
     * @return  object  The table
     * @since   1.5
     */
    function &getTable($name='', $prefix='TiendaTable', $options = array())
    {
        if (empty($name)) {
            $name = $this->getName();
        }
        
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        if ($table = $this->_createTable( $name, $prefix, $options ))  {
            return $table;
        }

        JError::raiseError( 0, 'Table ' . $name . ' not supported. File not found.' );
        $null = null;
        return $null;
    }
    
	/**
	 * Empties the state
	 *
	 * @return unknown_type
	 */
	public function emptyState()
	{
		$state = JArrayHelper::fromObject( $this->getState() );
		foreach ($state as $key=>$value)
		{
			if (substr($key, '0', '1') != '_')
			{
				$this->setState( $key, '' );
			}
		}
		return $this->getState();
	}

	/**
	 * Gets a property from the model's state, or the entire state if no property specified
	 * @param $property
	 * @param $default
	 * @return unknown_type
	 */
	public function getState( $property=null, $default=null )
	{
		return $property === null ? $this->_state : $this->_state->get($property, $default);
	}

	/**
	 * Gets the model's query, building it if it doesn't exist
	 * @return valid query object
	 */
	public function getQuery()
	{
		if (empty( $this->_query ) )
		{
			$this->_query = $this->_buildQuery();
		}
		return $this->_query;
	}

	/**
	 * Sets the model's query
	 * @param $query	A valid query object
	 * @return valid query object
	 */
	public function setQuery( $query )
	{
		$this->_query = $query;
		return $this->_query;
	}

	/**
	 * Gets the model's query, building it if it doesn't exist
	 * @return valid query object
	 */
	public function getResultQuery( $refresh=false )
	{
		if (empty( $this->_resultQuery ) || $refresh )
		{
			$this->_resultQuery = $this->_buildResultQuery();
		}
		return $this->_resultQuery;
	}

	/**
	 * Sets the model's query
	 * @param $query	A valid query object
	 * @return valid query object
	 */
	public function setResultQuery( $query )
	{
		$this->_resultQuery = $query;
		return $this->_resultQuery;
	}

	/**
	 * Retrieves the data for a paginated list
	 * @return array Array of objects containing the data from the database
	 */
	public function getList()
	{
		if (empty( $this->_list ))
		{
			$query = $this->getQuery();
			$this->_list = $this->_getList( (string) $query, $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_list;
	}

	/**
	 * Gets an item for displaying (as opposed to saving, which requires a JTable object)
	 * using the query from the model and the tbl's unique identifier
	 *
	 * @return database->loadObject() record
	 */
	public function getItem()
	{
		if (empty( $this->_item ))
		{
			$this->emptyState();
			$query = $this->getQuery();
			// TODO Make this respond to the model's state, so other table keys can be used
			// perhaps depend entirely on the _buildQueryWhere() clause?
			$keyname = $this->getTable()->getKeyName();
			$value	= $this->_db->Quote( $this->getId() );
			$query->where( "tbl.$keyname = $value" );
			$this->_db->setQuery( (string) $query );
			$this->_item = $this->_db->loadObject();
		}
		
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger( 'onPrepare'.$this->get('_suffix'), array( &$this->_item ) );
		
		return $this->_item;
	}

	/**
	 * Retrieves the data for an un-paginated list
	 * @return array Array of objects containing the data from the database
	 */
	public function getAll()
	{
		if (empty( $this->_all ))
		{
			$query = $this->getQuery();
			$this->_all = $this->_getList( (string) $query, 0, 0 );
		}
		return $this->_all;
	}
	
    public function getSurrounding( $id )
    {
        $prev = $this->getState('prev');
        $next = $this->getState('next');
        if (strlen($prev) || strlen($next)) 
        {
            $return["prev"] = $prev;
            $return["next"] = $next;
            return $return;
        }

        // subtract/add to the limitstart/limit so you get the prev/next when you're at the end of a paginated list
        $limit = $this->getState('limit') + 25;
        $limitstart = (($this->getState('limitstart') - 25) < 0) ? 0 : $this->getState('limitstart') - 25;
        
        $query = $this->_buildQuery( true );
        $rowset = $this->_getList( (string) $query, $limitstart, $limit );
        $count = count($rowset);

        $key = $this->getTable()->getKeyName();
            
        $found = false;
        $prev_id = '';
        $next_id = '';

        for ($i=0; $i < $count && empty($found); $i++) 
        {
            $row = $rowset[$i];     
            if ($row->$key == $id)
            { 
                $found = true; 
                $prev_num = $i - 1;
                $next_num = $i + 1;
                if (!empty($rowset[$prev_num]->$key)) { $prev_id = $rowset[$prev_num]->$key; }
                if (!empty($rowset[$next_num]->$key)) { $next_id = $rowset[$next_num]->$key; }
            }
        }
        
        $return["prev"] = $prev_id;
        $return["next"] = $next_id; 
        return $return;
    }

	/**
	 * Paginates the data
	 * @return array Array of objects containing the data from the database
	 */
	public function getPagination()
	{
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}

	/**
	 * Retrieves the count
	 * @return array Array of objects containing the data from the database
	 */
	public function getTotal()
	{
		if (empty($this->_total))
		{
            $query = $this->getQuery();
            $this->_total = $this->_getListCount( (string) $query);
		}
		return $this->_total;
	}
	
	/**
	 * Retrieves the result from the query
	 * Useful on SUM and COUNT queries
	 * 
	 * @return array Array of objects containing the data from the database
	 */
	public function getResult( $refresh=false )
	{
		if (empty($this->_result) || $refresh)
		{
			$query = $this->getResultQuery( $refresh );
			$this->_db->setQuery( (string) $query );
			$this->_result = $this->_db->loadResult();
		}
		return $this->_result;
	}

	/**
	 * Method to set the identifier
	 *
	 * @access	public
	 * @param	int identifier
	 * @return	void
	 */
	public function setId($id)
	{
		// Set id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	/**
	 * Gets the identifier, setting it if it doesn't exist
	 * @return unknown_type
	 */
	public function getId()
	{
		if (empty($this->_id))
		{
			$id = JRequest::getVar( 'id', JRequest::getVar( 'id', '0', 'post', 'int' ), 'get', 'int' );
			$array = JRequest::getVar('cid', array( $id ), 'post', 'array');
			$this->setId( (int) $array[0] );
		}

		return $this->_id;
	}

    /**
     * Builds a generic SELECT query
     *
     * @return  string  SELECT query
     */
    protected function _buildQuery( $refresh=false )
    {
    	if (!empty($this->_query) && !$refresh)
    	{
    		return $this->_query;
    	}

    	$query = new TiendaQuery();

        $this->_buildQueryFields($query);
        $this->_buildQueryFrom($query);
        $this->_buildQueryJoins($query);
        $this->_buildQueryWhere($query);
        $this->_buildQueryGroup($query);
        $this->_buildQueryHaving($query);
        $this->_buildQueryOrder($query);

		return $query;
    }

 	/**
     * Builds a generic SELECT COUNT(*) query
     */
    protected function _buildResultQuery()
    {
    	$query = new TiendaQuery();
		$query->select( $this->getState( 'select', 'COUNT(*)' ) );

        $this->_buildQueryFrom($query);
        $this->_buildQueryJoins($query);
        $this->_buildQueryWhere($query);
        $this->_buildQueryGroup($query);
        $this->_buildQueryHaving($query);

        return $query;
    }

    /**
     * Builds SELECT fields list for the query
     */
    protected function _buildQueryFields(&$query)
    {
		$query->select( $this->getState( 'select', 'tbl.*' ) );
    }

	/**
     * Builds FROM tables list for the query
     */
    protected function _buildQueryFrom(&$query)
    {
    	$name = $this->getTable()->getTableName();
    	$query->from($name.' AS tbl');
    }

    /**
     * Builds JOINS clauses for the query
     */
    protected function _buildQueryJoins(&$query)
    {
    }

    /**
     * Builds WHERE clause for the query
     */
    protected function _buildQueryWhere(&$query)
    {
    }

    /**
     * Builds a GROUP BY clause for the query
     */
    protected function _buildQueryGroup(&$query)
    {
    }

    /**
     * Builds a HAVING clause for the query
     */
    protected function _buildQueryHaving(&$query)
    {
    }

    /**
     * Builds a generic ORDER BY clause based on the model's state
     */
    protected function _buildQueryOrder(&$query)
    {
		$order      = $this->_db->getEscaped( $this->getState('order') );
       	$direction  = $this->_db->getEscaped( strtoupper( $this->getState('direction') ) );

        if ($order)
        {
            $query->order("$order $direction");
        }
       	
       	// TODO Find an abstract way to determine if order is a valid field in query
    	// if (in_array($order, $this->getTable()->getColumns())) does not work
    	// because you could be ordering by a field from one of the JOINed tables
		if (in_array('ordering', $this->getTable()->getColumns()))
		{
    		$query->order('ordering ASC');
    	}
    }
}