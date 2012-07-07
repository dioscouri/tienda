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

class plgTiendaReport_cart extends TiendaReportPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
    var $_element    = 'report_cart';

    /**
     * @var $default_model  string  Default model used by report
     */
    var $default_model    = 'carts';

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
	function plgTiendaReport_cart(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$language = JFactory::getLanguage();
		$language -> load('plg_tienda_'.$this->_element, JPATH_ADMINISTRATOR, 'en-GB', true);
		$language -> load('plg_tienda_'.$this->_element, JPATH_ADMINISTRATOR, null, true);
	}

    /**
     * Override parent::_getData() to sort products in users cart
     *
     * @return objectlist
     */
    function _getData()
    {
        $state = $this->_getState();
		$model = $this->_getModel();

		$query = $model->getQuery();

		 // group results by product ID
        $query->group('tbl.product_id');

		// select the total carts
		$field = array();
        $field[] = " COUNT(*) AS total_carts ";
        $query->select( $field );

         // order results by the total sales
        $query->order('total_carts DESC');

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
        $model = $this->_getModel( 'carts' );
        $ns = $this->_getNamespace();

        $state = parent::_getState(); // get the basic state values from the parent method

       	$state['filter_name'] = $app->getUserStateFromRequest($ns.'name', 'filter_name', '', '');
        $state = $this->_handleRangePresets( $state );

        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );
        }

        return $state;

    }
}
