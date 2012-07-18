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

class TiendaControllerProductDownloads extends TiendaController
{
	/**
	 * constructor
	 */
	function __construct() 
	{
		if (empty(JFactory::getUser()->id))
		{
			$url = JRoute::_( "index.php?option=com_tienda&view=productdownloads" );
			Tienda::load( "TiendaHelperUser", 'helpers.user' );
			$redirect = JRoute::_( TiendaHelperUser::getUserLoginUrl( $url ), false );
			JFactory::getApplication()->redirect( $redirect );
			return;
		}
		parent::__construct();

		$this->set('suffix','productdownloads');
	}

	/**
 	 * 
	 * @return unknown_type
	*/
	function _setModelState()
	{
		$state = parent::_setModelState();      
		$app = JFactory::getApplication();
		$model = &$this->getModel( $this->get('suffix') );
		$ns = $this->getNamespace();
		$config = Tienda::getInstance();
		// adjust offset for when filter has changed
		if (
			$app->getUserState( $ns.'product_id' ) != $app->getUserStateFromRequest($ns.'product_id', 'filter_product_id', '', '') 
		)
		{
			$state['limitstart'] = '0';
		}
		
		$state['order']     = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.productdownload_startdate', 'cmd');
		$state['direction'] = $app->getUserStateFromRequest($ns.'.filter_direction', 'filter_direction', 'DESC', 'word');        
		$state['filter_product_id'] = $app->getUserStateFromRequest($ns.'product_id', 'filter_product_id', '', 'integer');
		$state['filter_user']     = JFactory::getUser()->id;
		$state['filter']      = $app->getUserStateFromRequest($ns.'filter', 'filter', '', 'word');        
		
		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );   
		}
		
		return $state;
	}
}