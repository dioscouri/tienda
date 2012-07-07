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

class plgTiendaReport_MostDownloadedFiles extends TiendaReportPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
    var $_element    = 'report_mostdownloadedfiles';

    /**
     * @var $default_model  string  Default model used by report
     */
    var $default_model    = 'productfiles';

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
	function plgTiendaReport_MostDownloadedFiles(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$language = JFactory::getLanguage();
		$language -> load('plg_tienda_'.$this->_element, JPATH_ADMINISTRATOR, 'en-GB', true);
		$language -> load('plg_tienda_'.$this->_element, JPATH_ADMINISTRATOR, null, true);
	}

    /**
     * Override parent::_getData() to set the direction of the product quantity
     *
     * @return objectlist
     */
    function _getData()
    {
        $state = $this->_getState();
        $model = $this->_getModel();
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
        $model = $this->_getModel( $this->default_model );
        $ns = $this->_getNamespace();

        $state = parent::_getState(); // get the basic state values from the parent method
       	$state['filter'] = $app->getUserStateFromRequest($ns, 'filter', '', '');
        $state['filter_date_from'] = $app->getUserStateFromRequest($ns.'date_from', 'filter_date_from', '', '');
        $state['filter_date_to'] = $app->getUserStateFromRequest($ns.'date_to', 'filter_date_to', '', '');      
        $state['filter_product_name'] = $app->getUserStateFromRequest($ns.'product_name', 'filter_product_name', '', '');
        $state['filter_file_name'] = $app->getUserStateFromRequest($ns.'file_name', 'filter_file_name', '', '');
        $state['direction'] = $app->getUserStateFromRequest( $ns.'direction', 'filter_direction', 'DESC', '' );
        $state['order'] = $app->getUserStateFromRequest( $ns.'order', 'filter_order', 'file_downloads', '' );
        $state = $this->_handleRangePresets( $state );
        
        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );
        }

        return $state;
    
    }
    
 	/**
     * Prepares the 'view' tmpl layout
     * when viewing a report
     *  
     * @return unknown_type
     */
    protected function _renderView($options='')
    {
        // TODO Load the report, get the data, and render the report html using the form inputs & data
        
        $vars = new JObject();
        $vars->items = $this->_getData();
        $vars->state = $this->_getModel()->getState();
        $vars->pagination = $this->_getModel()->getPagination();
        
        $html = $this->_getLayout('view', $vars);
        
        return $html;
    }
    
}
