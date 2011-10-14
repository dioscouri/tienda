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

class TiendaControllerDashboard extends TiendaController
{
	/**
	 * constructor
	 */
	function __construct() 
	{
        if (empty(JFactory::getUser()->id))
        {
            $url = JRoute::_( "index.php?option=com_tienda&view=dashboard" );
            $redirect = "index.php?option=com_user&view=login&return=".base64_encode( $url );
            $redirect = JRoute::_( $redirect, false );
            JFactory::getApplication()->redirect( $redirect );
            return;
        }
		
		parent::__construct();
		$this->set('suffix', 'dashboard');
	}
	/**
	 * (non-PHPdoc)
	 * @see tienda/admin/TiendaController::display()
	 */
	function display()
	{
	    Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        JRequest::setVar( 'view', $this->get('suffix') );
        $view   = $this->getView( $this->get('suffix'), JFactory::getDocument()->getType() );
        $model  = $this->getModel( $this->get('suffix') );
        $this->_setModelState();
        $view->set('_doTask', true);
        $view->setModel( $model, true );
        $view->setLayout('default');
        
        $user_id = JFactory::getUser()->id;
        $userinfo = JTable::getInstance('UserInfo', 'TiendaTable');
        $userinfo->load( array( 'user_id'=>$user_id ) );
        $view->assign( 'userinfo', $userinfo );
        
        $view->display();
        $this->footer();
        return;        
	}
}