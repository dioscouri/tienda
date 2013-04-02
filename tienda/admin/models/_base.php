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

Tienda::load( 'DSCQuery', 'library.query' );

class TiendaModelBase extends DSCModel
{
    /**
     * Define this in your model to have all the objects in a getList() array be objects of this class
     * @var unknown_type
     */
    protected $_objectClass = null;
    
    public function __construct($config = array())
    {
        parent::__construct($config);
    
        $this->defines = Tienda::getInstance();
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
        DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
        return parent::getTable($name, $prefix, $options);
    }
     
    /**
     * Retrieves the data for a paginated list
     * @return array Array of objects containing the data from the database (cached)
     */
    public function getList($refresh = false)
    {
        if (empty( $this->_list ) || $refresh)
        {
            $this->_list = parent::getList($refresh);

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
    
    /**
     * Any errors set?  If so, check fails
     *
     */
    public function check()
    {
        $errors = $this->getErrors();
        if (!empty($errors))
        {
            foreach ($errors as $key=>$error)
            {
                $error = trim( $error );
                if (empty($error))
                {
                    unset($errors[$key]);
                }
            }
             
            if (!empty($errors))
            {
                return false;
            }
        }
         
        return true;
    }
    
    /**
     * Gets an array of objects from the results of database query.
     * TODO Push this upstream after checking for potential backwards-incompatiblity issues
     * 
     * @param   string   $query       The query.
     * @param   integer  $limitstart  Offset.
     * @param   integer  $limit       The number of records.
     *
     * @return  array  An array of results.
     *
     * @since   11.1
     */
    protected function _getList($query, $limitstart = 0, $limit = 0)
    {
        $key = !empty($this->_keyGetList) ? $this->_keyGetList : ''; 
        $class = !empty($this->_objectClass) ? $this->_objectClass : 'stdClass';
        
        $this->_db->setQuery($query, $limitstart, $limit);
        $result = $this->_db->loadObjectList( $key, $class );
    
        return $result;
    }
}