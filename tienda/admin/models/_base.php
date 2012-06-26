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

Tienda::load( 'TiendaQuery', 'library.query' );

class TiendaModelBase extends DSCModel
{
    var $_filterinput = null; // instance of JFilterInput
    
    function __construct($config = array())
    {
        parent::__construct($config);
     
    }
    
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
     function getTable($name='', $prefix='TiendaTable', $options = array())
    {
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        return parent::getTable($name, $prefix, $options);
    }
     
    
    
	
	
	/**
	 * Retrieves the data for a paginated list
	 * @return array Array of objects containing the data from the database
	 */
	public function getList($refresh = false)
	{
		if (empty( $this->_list ) || $refresh)
		{
			$query = $this->getQuery($refresh);
			$this->_list = $this->_getList( (string) $query, $this->getState('limitstart'), $this->getState('limit') );
			
			$overridden_methods = $this->getOverriddenMethods( get_class($this) );
			if (!in_array('getList', $overridden_methods))  
			{
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger( 'onPrepare'.$this->getTable()->get('_suffix').'List', array( &$this->_list ) );
			}
		}
		return $this->_list;
	}
	
	/**
     * convert data from local to GMT
	 * TODO handle solar and legal time where is present.
     */
    function local_to_GMT_data( $local_data )
    {
		$GMT_data=$local_data ;
		if(!empty($local_data))
		{
			$config = JFactory::getConfig();
			$offset = $config->getValue('config.offset');
			$offset=0-$offset;
			$date = date_create($local_data);
			date_modify($date,  $offset.' hour');		
			$GMT_data= date_format($date, 'Y-m-d H:i:s');
		}
		return $GMT_data;
    }
}