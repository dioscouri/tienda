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
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class TiendaController extends DSCControllerAdmin
{
	/**
     * default view
     */
    public $default_view = 'dashboard';
	 
	 /**
	 * @var array() instances of Models to be used by the controller
	 */
	public $_models = array();

	/**
	 * string url to perform a redirect with. Useful for child classes.
	 */
	protected $redirect;

	/**
	 * Hides a tooltip message
	 * @return unknown_type
	 */
	function pagetooltip_switch()
	{
		$msg = new stdClass();
		$msg->type 		= '';
		$msg->message 	= '';
		$view = JRequest::getVar('view');
		$msg->link 		= 'index.php?option=com_tienda&view='.$view;

		$key = JRequest::getVar('key');
		$constant = 'page_tooltip_'.$key;
		$config_title = $constant."_disabled";

		$database = &JFactory::getDBO();
		JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables/' );
		unset($table);
		$table = JTable::getInstance( 'config', 'TiendaTable' );
		$table->load( array('config_name'=>$config_title) );
		$table->config_name = $config_title;
		$table->value = '1';

		if (!$table->save())
		{
			$msg->message = JText::_('COM_TIENDA_ERROR') . ": " . $table->getError();
		}

		$this->setRedirect( $msg->link, $msg->message, $msg->type );
	}

	/**
	 * For displaying a searchable list of products in a lightbox
	 * Usage:
	 */
	function elementProduct()
	{
		$model 	= $this->getModel( 'elementproduct' );
		$view	= $this->getView( 'elementproduct' );
		$view->setModel( $model, true );
		$view->display();
	}

	/**
	 * For displaying a searchable list of images in a lightbox
	 * Usage:
	 */
	function elementImage()
	{
		$model 	= $this->getModel( 'elementimage' );
		$view	= $this->getView( 'elementimage' );
		$view->setModel( $model, true );
		$view->display();
	}

}

?>