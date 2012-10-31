<?php
/**
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

/** Import library dependencies */
jimport('joomla.plugin.plugin');

/**
 * Ambrasubs Plugin
 *
 * @package		Joomla
 * @subpackage	JFramework
 * @since 		1.5
 */
class plgTiendaAward_AmbraPoints extends JPlugin 
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'award_ambrapoints';
    
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
	function plgTiendaAward_AmbraPoints(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}
	/**
     * Check if is installed Ambra
     * 
     * @return unknown_type
     */
    /**
     * Checks the extension is installed
     * 
     * @return boolean
     */
    function _isInstalled()
    {
        $success = false;

        jimport( 'joomla.filesystem.file' );
        $filePath = JPATH_ADMINISTRATOR."/components/com_ambra/defines.php";
        if (JFile::exists($filePath))
        {
            $success = true;
            if ( !class_exists('Ambra') )
            { 
                JLoader::register( "Ambra", JPATH_ADMINISTRATOR."/components/com_ambra/defines.php" );
            }
        }           
        return $success;
    }
    
    /**
     * Award points on review
     * 
     * @param object $row product comment
     * @return bool $success
     */
    function onAfterSaveProductComments( $row )
    {
        $success = false;

	    if (!$this->_isInstalled())
        {
            return $success;    
        }
     	
		JLoader::register( "Ambra", JPATH_ADMINISTRATOR."/components/com_ambra/helpers/point.php");
        $helper = Ambra::get( "AmbraHelperPoint", 'helpers.point' );
					
        if ($helper->createLogEntry( $row->user_id, 'com_tienda', 'onAfterSaveProductComments' ))
        {
           JFactory::getApplication()->enqueueMessage( $helper->getError() );
           $success = true;           
        }
        
		return $success;
	}
	
	/**
	 * Award points on purchase
	 * 
	 * @param int $orderid
	 * @return bool $success
	 */
	function doCompletedOrderTasks( $orderid )
    {    	
        $success = false;
    	$user_id=JFactory::getUser()->id;

	    if (!$this->_isInstalled())
        {
            return $success;    
        }        
        
	    $model = JModel::getInstance( 'Orders', 'TiendaModel' );
		$model->setId( $orderid );
		$order = $model->getItem();
			
		$subtotal = $order->order_subtotal;		
		$min_purchase_points = $this->params->get('min_purchase_value');
					
		if ( $subtotal>=$min_purchase_points )
		{
			$allpayments_awarded = $this->params->get('allpayments_awarded');
			
	        if( $allpayments_awarded )
	        { 
	        	$success = $this->createLogEntry( $user_id, 'com_tienda', 'doCompletedOrderTasks', $subtotal );
	        	
	        }
	        else
	        {
	          	$model = JModel::getInstance( 'OrderPayments', 'TiendaModel' );
            	$model->setState( 'select', 'tbl.orderpayment_type');
				$model->setState( 'filter_orderid', $orderid );
				$orderpayment_type = $model->getResult();
	           	
	           	if( $orderpayment_type!='payment_alphauserpoints' && $orderpayment_type!='payment_ambrapoints' )
	           	{
	            	$success = $this->createLogEntry( $user_id, 'com_tienda', 'doCompletedOrderTasks', $subtotal );
	           	}
	           	else 
	           	{
	           		
	           		JFactory::getApplication()->enqueueMessage( JText::sprintf('TIENDA AMBRAPOINTS AWARD ERROR PAYMENT TYPE',$orderpayment_type), 'notice' );
					return $success;
	           	}
	        }			
		}
        
		return $success;
	}
		
	/**
     * Creates a pointhistory log entry 
     * if the user parameters allow for it 
     * 
     * @param int $user_id  Valid user id number
     * @param str $scope    Generally the com_whatever
     * @param str $event    the event name as set in _pointhistory
     * @return true if OK, false if fail, null if no action; all with a message in the error object 
     */
    function createLogEntry( $user_id, $scope, $event, $subtotal )
    {
    	// is user_id valid?
        if (empty(JFactory::getUser( $user_id )->id ))
        {   
            JFactory::getApplication()->enqueueMessage( JText::_('COM_TIENDA_INVALID_USER'), 'notice' );
            return false;
        }
       
        // TODO is scope + event pairing valid?
        
        // get the user's userdata
        jimport( 'joomla.application.component.model' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_ambra/tables' );
        
        
        $max_points = Ambra::get( "AmbraHelperUser", "helpers.user" )->getMaxPoints( JFactory::getUser( $user_id )->id );
         
        JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_ambra/models' );
        $model = JModel::getInstance('Users', 'AmbraModel');
        $model->setId( $user_id );
       
        $manual_approval = Ambra::get( "AmbraHelperUser", "helpers.user" )->getManualApproval($user_id);
        $userdata = $model->getItem();
     
        // has the user equaled or exceeded their lifetime max?
        $max_points = Ambra::get( "AmbraHelperUser", "helpers.user" )->getMaxPoints( JFactory::getUser( $user_id )->id );
        if ($max_points != '-1' && $userdata->points_total > $max_points)
        {         die("here");
            JFactory::getApplication()->enqueueMessage( JText::_('User Exceeded Max Points'), 'notice' );
            return false;            
        } 
                 
        // has the user equaled or exceeded their daily max?
        $pointhistory_today = Ambra::get( "AmbraHelperUser", "helpers.user" )->getTodayPoints( JFactory::getUser( $user_id )->id );
        $max_points_per_day = Ambra::get( "AmbraHelperUser", "helpers.user" )->getMaxPointsPerDay( JFactory::getUser( $user_id )->id );
        if ($max_points_per_day != '-1' && $pointhistory_today > $max_points_per_day)
        { 
            JFactory::getApplication()->enqueueMessage( JText::_('User Exceeded Max Points for the Day'), 'notice' );
            return false;            
        }         
        
        // get the enabled, not expired pointrules for this scope + event where profile_id = '0' (all profiles) OR profile_id = this user's profile
        // (by using filter_pointprofile instead of filter_profile)
        $today = Ambra::get( "AmbraHelperBase", "helpers._base" )->getToday();
        $model = JModel::getInstance('PointRules', 'AmbraModel');
        $model->setState( 'filter_enabled', 1 );
        $model->setState( 'filter_datetype', 'expires' );
        $model->setState( 'filter_date_from', $today );
        $model->setState( 'filter_scope', $scope );
        $model->setState( 'filter_event', $event );
        $model->setState( 'filter_pointprofile', $userdata->profile_id );
       
        if (!$pointrules = $model->getList())
        { 
            JFactory::getApplication()->enqueueMessage( JText::_('No Valid Points Found for this Event'), 'notice' );
            return false;
        }
        
        // foreach pointrule
        $ruleHelper = Ambra::get( "AmbraHelperRule", "helpers.rule" );
        $errors = array(); // track errors
        $points = 0;
        foreach ($pointrules as $pointrule)
        { 
            // has the pointrule equaled or exceeded its max uses?
            if ($pointrule->pointrule_uses_max > '-1' && $pointrule->pointrule_uses >= $pointrule->pointrule_uses_max)
            {
                // skip it
                continue;
            }
            
            // has the user equaled or exceeded the pointrule's user-limits (total & per day)?
            $user_uses = $ruleHelper->getUses( $pointrule->pointrule_id, $user_id, 'total' );
            if ($user_uses >= $pointrule->pointrule_uses_per_user && $pointrule->pointrule_uses_per_user > '-1')
            {
                // skip it
                continue;
            }

            $user_uses_today = $ruleHelper->getUses( $pointrule->pointrule_id, $user_id, 'today' );
            if ($user_uses_today >= $pointrule->pointrule_uses_per_user_per_day && $pointrule->pointrule_uses_per_user_per_day > '-1')
            {
                // skip it
                continue;
            }

    
            // if here, all OK
            // create a pointhistory table object
            $pointhistory = JTable::getInstance('PointHistory', 'AmbraTable');
           
            // set properties
            $pointhistory->user_id = $user_id;
            $pointhistory->pointrule_id = $pointrule->pointrule_id;
            
            $value_type = $this->params->get('award_points_type');            
        	switch ($value_type) {
            		case 'Fixed':
            			$pointhistory->points = $pointrule->pointrule_value;            			
            		break;
            		
            		case 'Percentage':
            			$percentage = $this->params->get('percentage_value');
            			$pointhistory->points = round($subtotal * $percentage / 100);
            			          			
            		break;
            }        
            
            $pointhistory->points_updated = 0;
            $expirationperiod = AmbraConfig::getInstance()->get('expirationperiod', '');
            $expirationperiod = (int)$expirationperiod;
            $orgDate=date('Y-m-d');
            $cd = strtotime($orgDate);
			$retDate = date('Y-m-d', mktime(0,0,0,date('m',$cd)+$expirationperiod,date('d',$cd),date('Y',$cd)));
			$pointhistory->expire_date=$retDate;
            if ($pointrule->pointrule_auto_approve == 1)
            {
                // enable pointhistory
                $pointhistory->pointhistory_enabled = 1;
            }
                else
            {
            	if (empty($manual_approval))
            	{
                	$pointhistory->pointhistory_enabled = 1;
            	}
            }
            
           
            // TODO When do points expire?
            
            // save it and move on
            if (!$pointhistory->save())
            {
                $errors[] = $pointhistory->getError(); 
            }
                else
            {
            	
                // track the number of points?
                $event = $pointrule->pointrule_name;
                $points = $points + $pointhistory->points;
               
            }
        }
        
        if (!empty($errors))
        
        {
            JFactory::getApplication()->enqueueMessage( implode( '<br/>', $errors ), 'notice' );
            return false;
        }

        
        if (empty($errors) && !empty($points))
        {
            if ($points == '1') { $string = 'point'; } else { $string = 'points'; }
            $lang = JFactory::getLanguage();
            $lang->load( 'com_ambra', JPATH_ADMINISTRATOR );
            $login_note		=AmbraConfig::getInstance()->get('login_point_notification', '');
            $avatar_note	=AmbraConfig::getInstance()->get('avatar_point_notification', '');
            $affiliate_note	=AmbraConfig::getInstance()->get('affiliate_point_notification', '');
            $productcomment_point_notification	=AmbraConfig::getInstance()->get('productcomment_point_notification', '');
            $purchase_point_notification	=AmbraConfig::getInstance()->get('purchase_point_notification', '');
            if(($login_note && $event=="Logging In")||($avatar_note && $event=="Uploading an Avatar")||($affiliate_note && $event=="Becoming an Affiliate")||($productcomment_point_notification && $event=="Leaving Comments on product")||($purchase_point_notification &&  $event=="doCompletedOrderTasks" ))
	        {	           
	           JFactory::getApplication()->enqueueMessage( JText::sprintf('TIENDA AMBRAPOINTS AWARD MESSAGE ONPURCHASE', $points ) );
	           return true;
            
	        }
            else
            {
            	return false;
            }
        }
        
        // shouldn't end up here
        JFactory::getApplication()->enqueueMessage( JText::_('Something Wrong Happened'), 'notice' );
        return false;   
    }
}  