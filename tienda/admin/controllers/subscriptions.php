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
        $state['filter_transactionid']    = $app->getUserStateFromRequest($ns.'filter_transactionid', 'filter_transactionid', '', '');
        $state['filter_user']         = $app->getUserStateFromRequest($ns.'filter_user', 'filter_user', '', '');
        $state['filter_userid']         = $app->getUserStateFromRequest($ns.'filter_userid', 'filter_userid', '', '');
        $state['filter_id_from']    = $app->getUserStateFromRequest($ns.'id_from', 'filter_id_from', '', '');
        $state['filter_id_to']      = $app->getUserStateFromRequest($ns.'id_to', 'filter_id_to', '', '');
        $state['filter_date_from'] = $app->getUserStateFromRequest($ns.'date_from', 'filter_date_from', '', '');
        $state['filter_date_to'] = $app->getUserStateFromRequest($ns.'date_to', 'filter_date_to', '', '');
        $state['filter_datetype']   = 'created';
        $state['filter_total_from']    = $app->getUserStateFromRequest($ns.'filter_total_from', 'filter_total_from', '', '');
        $state['filter_total_to']      = $app->getUserStateFromRequest($ns.'filter_total_to', 'filter_total_to', '', '');
		$state['filter_enabled']       = $app->getUserStateFromRequest($ns.'filter_enabled', 'filter_enabled', '', '');
		$state['filter_lifetime']       = $app->getUserStateFromRequest($ns.'filter_lifetime', 'filter_lifetime', '', '');
				if( TiendaConfig::getInstance()->get( 'display_subnum', 0 ) )
					$state['filter_subnum']       = $app->getUserStateFromRequest($ns.'filter_subnum', 'filter_subnum', '', '');
				
		
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
    function update()
    {
        $row = JTable::getInstance('SubscriptionHistory', 'TiendaTable');
        $post = JRequest::get('post', '4');
        $row->bind( $post );
        $row->subscription_id = JRequest::getInt('id');
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
}

?>