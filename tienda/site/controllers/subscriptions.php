<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

class TiendaControllerSubscriptions extends TiendaController 
{
	/**
	 * constructor
	 */
	function __construct() 
	{ 
		if (empty(JFactory::getUser()->id))
		{
			$url = JRoute::_( "index.php?option=com_tienda&view=orders" );
			$redirect = "index.php?option=com_user&view=login&return=".base64_encode( $url );
			$redirect = JRoute::_( $redirect, false );
			JFactory::getApplication()->redirect( $redirect );
			return;
		}
		
		parent::__construct();
		$this->set('suffix', 'subscriptions');
    $this->registerTask( 'subscription_enabled.enable', 'boolean' );
    $this->registerTask( 'subscription_enabled.disable', 'boolean' );
    $this->registerTask( 'lifetime_enabled.enable', 'boolean' );
    $this->registerTask( 'lifetime_enabled.disable', 'boolean' );
    $this->registerTask( 'update_subscription', 'update' );
	}
	
	/**
	 * Sets the model's state
	 * 
	 * @return array()
	 */
    function _setModelState()
    {
    	$state = parent::_setModelState();   	
		$app = JFactory::getApplication();
		$model = $this->getModel( $this->get('suffix') );
    	$ns = $this->getNamespace();

        $state['order']     = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.created_datetime', 'cmd');
        $state['direction'] = $app->getUserStateFromRequest($ns.'.filter_direction', 'filter_direction', 'DESC', 'word');
        $state['filter_orderid']       = $app->getUserStateFromRequest($ns.'filter_orderid', 'filter_orderid', '', '');
        $state['filter_type']       = $app->getUserStateFromRequest($ns.'filter_type', 'filter_type', '', '');
        $state['filter_transaction']    = $app->getUserStateFromRequest($ns.'filter_transaction', 'filter_transaction', '', '');
        $state['filter_user']         = $app->getUserStateFromRequest($ns.'filter_user', 'filter_user', '', '');
        $state['filter_userid']         = $user_id=JFactory::getUser()->id;
        $state['filter_id_from']    = $app->getUserStateFromRequest($ns.'id_from', 'filter_id_from', '', '');
        $state['filter_id_to']      = $app->getUserStateFromRequest($ns.'id_to', 'filter_id_to', '', '');
        $state['filter_date_from'] = $app->getUserStateFromRequest($ns.'date_from', 'filter_date_from', '', '');
        $state['filter_date_to'] = $app->getUserStateFromRequest($ns.'date_to', 'filter_date_to', '', '');
        $state['filter_datetype']   = 'created';
        $state['filter_total_from']    = $app->getUserStateFromRequest($ns.'filter_total_from', 'filter_total_from', '', '');
        $state['filter_total_to']      = $app->getUserStateFromRequest($ns.'filter_total_to', 'filter_total_to', '', '');
		$state['filter_enabled']       = $app->getUserStateFromRequest($ns.'filter_enabled', 'filter_enabled', '', '');
		$state['filter_lifetime']       = $app->getUserStateFromRequest($ns.'filter_lifetime', 'filter_lifetime', '', '');
		
    	foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );	
		}
  		return $state;
    }
    
    /**
     * 
     * Adds a subscription history entry to a subscription
     * @return unknown_type
     */
    function unsubscribe()
    {  
        $row = JTable::getInstance('Subscriptions', 'TiendaTable');
        $id=JRequest::getInt('id');
        $row->load($id);
        $row->subscription_enabled="0";
        if ($row->save())
        {
            $dispatcher = JDispatcher::getInstance();
            $dispatcher->trigger( 'onAfterUpdateStatus'.$this->get('suffix'), array( $row ) );
        }
            else
        {
            $this->messagetype  = 'notice';
            $this->message      = JText::_('COM_TIENDA_SAVE_FAILED')." - ".$row->getError();
        }

        $redirect = "index.php?option=com_tienda";
        $redirect .= '&view='.$this->get('suffix').'&task=view&id='.$row->subscription_id;
        $redirect = JRoute::_( $redirect, false );
        $this->setRedirect( $redirect, $this->message, $this->messagetype );
    }
    
    /**
     * (non-PHPdoc)
     * @see tienda/site/TiendaController#view()
     */
    function view() 
    {
    	// if the user cannot view order, fail
        $model  = $this->getModel( $this->get('suffix') );
        $subscriptions = $model->getTable( 'subscriptions' );
        $subscriptions->load( $model->getId() );
        //$subscriptions->getItems();
        
        $row = $model->getItem();
                
        $user_id = JFactory::getUser()->id;
        if (empty($user_id) || $user_id != $row->user_id)
        {
        	$this->messagetype  = 'notice';
        	$this->message      = JText::_('COM_TIENDA_INVALID_SUBSCRIPTIONS');
            $redirect = "index.php?option=com_tienda&view=".$this->get('suffix');
            $redirect = JRoute::_( $redirect, false );
            $this->setRedirect( $redirect, $this->message, $this->messagetype );
            return;
        }
        
        Tienda::load( 'TiendaUrl', 'library.url' );
        
        $view = $this->getView( 'subscriptions', 'html' );
        $view->set( '_controller', 'subscriptions' );
        $view->set( '_view', 'orders' );
        $view->set( '_doTask', true);
        $view->set( 'hidemenu', false);
        $view->setModel( $model, true );
        $view->assign( 'order', $subscriptions );
        
        $view->setLayout( 'view' );
        $view->display();
        $this->footer();
    }
    
}

?>