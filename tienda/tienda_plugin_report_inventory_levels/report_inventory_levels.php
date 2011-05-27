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

Tienda::load( 'TiendaReportPlugin', 'library.plugins.report' );

class plgTiendaReport_inventory_levels extends TiendaReportPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
    var $_element    = 'report_inventory_levels';
    
     /**
     * @var $default_model  string  Default model used by report
     */
    var $default_model    = 'products';
    
    
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function plgTiendaReport_inventory_levels(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}
	   
	/**
     * Override parent::_getData() to set the direction of the product 
     *
     * @return objectlist
     */
    function _getData()
    {
    	
       	$state = $this->_getState();
        $model = $this->_getModel();		
      	$query = $model->getQuery();
      	
      	$model->setState( 'order', 'product_name' );
        $model->setState( 'direction', 'DESC' );
		
		$field = array();
		$field[] = " tbl.* ";
		$field[] = "pq.* ";
		
		$field[] = " SUM(pq.quantity) AS total_quantity ";
	
		$query->select( $field );
					
		$query->join('LEFT', '#__tienda_productquantities AS pq ON pq.product_id = tbl.product_id');
				
		$query->group('tbl.product_id');
		$model->setQuery( $query );
       	$data = $model->getList();
		
        return $data;
    }
    
 	/**
     * Override parent::_getState() to do the filtering
     *
     * @return object
     */
    function _getState()
    {
    	$app = JFactory::getApplication();
        $model = $this->_getModel( $this->get('default_model') );
        $ns = $this->_getNamespace();
		
        $state = parent::_getState();
              
       	$state['filter_name'] = $app->getUserStateFromRequest($ns.'name', 'filter_name', '', '');
        $state['filter_quantity_from'] = $app->getUserStateFromRequest($ns.'quantity_from', 'filter_quantity_from', '', '');
        $state['filter_quantity_to'] = $app->getUserStateFromRequest($ns.'quantity_to', 'filter_quantity_to', '', '');
        $state = $this->_handleRangePresets( $state );
        
        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );
        }

        return $state;
    
    }
    
	/**
	 * Returns a list of a product's attributes
	 * 
	 * @param int $id
	 * @return unknown_type
	 */
	function getAttributes( $id )
	{
		if ( empty( $id ) )
		{
			return array( );
		}
		JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
		$model = JModel::getInstance( 'ProductAttributes', 'TiendaModel' );
		$model->setState( 'filter_product', $id );
		
		$model->setState( 'order', 'tbl.ordering' );
		$model->setState( 'direction', 'ASC' );
		
		$items = $model->getList( );
		return $items;
	}

}