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
 * Award AlphaUserPoints Plugin
 *
 * @package		Joomla
 * @subpackage	JFramework
 * @since 		1.5
 */
class plgTiendaAward_alphauserpoints extends JPlugin 
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'award_alphauserpoints';
    
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
	function plgTiendaAward_alphauserpoints(& $subject, $config)
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
        $api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';	    
		if (JFile::exists($api_AUP))
        {
        	require_once ($api_AUP);
        
            $success = true;
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
     	
        $award_pointsonreviews = $this->params->get('award_points_reviews');
        
        if( $award_pointsonreviews )
        {
			$award_points_value = $this->params->get('points_per_review');
			if( empty($award_points_value) )
			{
				JError::raiseError(500, JText::_('TIENDA ALPHAUSERPOINTS AWARD MESSAGE VALUE ERROR'));
				return $success;
			}
			else 
			{
				$this->insertUserpoints( $award_points_value, JText::_('TIENDA ALPHAUSERPOINTS AWARD ONPRODUCTCOMMENT') );
				JFactory::getApplication()->enqueueMessage( JText::sprintf('TIENDA ALPHAUSERPOINTS AWARD MESSAGE ONPRODUCTCOMMENT',$award_points_value) );
				$success = true;
			}
        }
        
		return $success;
	}
	
	/**
	 * Award points on purchase
	 * 
	 * @param int $orderid
	 * @return bool $success
	 */
	function doCompletedOrderTasks($orderid )
    {
        $success = false;

	    if (!$this->_isInstalled())
        {
            return $success;    
        }
        
	    $model = JModel::getInstance( 'Orders', 'TiendaModel' );
		$model->setId( $orderid );
		$order = $model->getItem();		
			
		$subtotal = $order->order_subtotal;
		$min_purchase_points = $this->params->get('min_purchase_value');
		
		$points_value = $this->params->get('points_value');            	
        $value_type = $this->params->get('award_points_type');

		// check if purches have allowed minimum amount
		if ( $subtotal >= $min_purchase_points )
		{
			$model = JModel::getInstance( 'OrderPayments', 'TiendaModel' );
			$model->setState( 'filter_orderid', $orderid );
			$orderpayment = $model->getItem();
		
			// check what kind of payment is made and
			// check if that kind is allowed
			$allpayments_awarded = $this->params->get('allpayments_awarded');
			
            if( $allpayments_awarded )
            {            	
            	// check how to calculate awarded points
            	switch ($value_type) {
            		case 'Fixed':
            			$this->insertUserpoints( $points_value, JText::sprintf('TIENDA ALPHAUSERPOINTS AWARD ONPURCHASE', $order->order_id) );
						JFactory::getApplication()->enqueueMessage( JText::sprintf('TIENDA ALPHAUSERPOINTS AWARD MESSAGE ONPURCHASE',$points_value) );
						$success = true;
            			
            		break;
            		case 'Percentage':
            			$points = round($subtotal * $points_value / 100);
            			$this->insertUserpoints( $points, JText::sprintf('TIENDA ALPHAUSERPOINTS AWARD ONPURCHASE', $order->order_id ) );
						JFactory::getApplication()->enqueueMessage( JText::sprintf('TIENDA ALPHAUSERPOINTS AWARD MESSAGE ONPURCHASE', $points ) );
						$success = true;
            			
            		break;
            	}
            }
            else 
            {
            	$orderpayment_type = $orderpayment->orderpayment_type;
            	
            	if( $orderpayment_type!='payment_alphauserpoints' && $orderpayment_type!='payment_ambrapoints' )
            	{
	            	switch ($value_type) {
	            		case 'Fixed':
	            			$this->insertUserpoints( $points_value, JText::sprintf('TIENDA ALPHAUSERPOINTS AWARD ONPURCHASE', $order->order_id) );
							JFactory::getApplication()->enqueueMessage( JText::sprintf('TIENDA ALPHAUSERPOINTS AWARD MESSAGE ONPURCHASE',$points_value) );
							$success = true;
	            			
	            		break;
	            		case 'Percentage':
	            			$points = round($subtotal * $points_value / 100);
	            			$this->insertUserpoints( $points, JText::sprintf('TIENDA ALPHAUSERPOINTS AWARD ONPURCHASE', $order->order_id ) );
							JFactory::getApplication()->enqueueMessage( JText::sprintf('TIENDA ALPHAUSERPOINTS AWARD MESSAGE ONPURCHASE', $points ) );
							$success = true;
	            			
	            		break;
	            	}
            	}
            }
		}
        
		return $success;
	}
	
	/**
     * Gets alpha user points
     * 
     * @param void
     * @return $userpoints int
     */
    function getUserpoints()
    {
    	// get alphauserpoints for the user
		$api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';	    
		if ( file_exists($api_AUP))
		{
			require_once ($api_AUP);
					
			$referrerid = $this->getReferreid( JFactory::getUser()->id );
			
			$userpoints = AlphaUserPointsHelper::getCurrentTotalPoints( $referrerid );			
		}
		
		return $userpoints;
    }
    
    /**
     * Updates alpha user points
     * 
     * @param $assignpoints int
     * @return void
     */
    function insertUserpoints( $amount_points, $award_desc )
    {
    	// reduce number of points for the payment
    	$api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';	    
		if ( file_exists($api_AUP))
		{
			require_once ($api_AUP);
			
			// create the rule for Tienda payment if not exists
			$this->checkTiendaAwardRule();
							
			$referrerid = $this->getReferreid( JFactory::getUser()->id );
			
			$jnow = & JFactory::getDate();
			$now  = $jnow->toMySQL();	
			
			//AlphaUserPointsHelper::newpoints( 'function_name', '', '', '', -$amount_points);
			
			AlphaUserPointsHelper::insertUserPoints( $referrerid, $amount_points, $rule_expire='0000-00-00 00:00:00', AlphaUserPointsHelper::getRuleID( $this->_element ), 0, '', $award_desc );
		}     
    }
    
    /** 
     * Gets $referrerid for the user and starts the session
     *  
     * @param int $userID
     */    
	function getReferreid( $userID )
	{	
		if ( !$userID ) return;	
		// get referre ID on login	
		$db	   =& JFactory::getDBO();
		$query = "SELECT referreid FROM #__alpha_userpoints WHERE `userid`='$userID'";
		$db->setQuery( $query );
		$referreid = $db->loadResult();
		if ( $referreid )
		{		
			@session_start('alphauserpoints');	
			$_SESSION['referrerid'] = $referreid;
		}	
		
		return $referreid;
	}
	
	/**
	 * Checks AlphaUserPoints for Tienda payment
	 * 
	 * @param void
	 * @return void
	 */
	function checkTiendaAwardRule()
	{
		$api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';	    
		if ( file_exists($api_AUP))
		{
			require_once ($api_AUP);
		
			$referrerid = $this->getReferreid( JFactory::getUser()->id );
			
			$rule_name = AlphaUserPointsHelper::getNameRule( $this->_element );
			
			if( empty( $rule_name ) )
			{
				JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_alphauserpoints'.DS.'tables');
				// save new points into alpha_userpoints_rules table
				$row =& JTable::getInstance('Rules');
				$row->id			   = NULL;
				$row->rule_name		   = 'Tienda Award AlphaUserPoints';
				$row->rule_description = 'Rule for awarding points on Tienda action';
				$row->rule_plugin	   = 'Tienda Award AlphaUserPoints';
				$row->plugin_function  = $this->_element;			
				$row->component		   = 'com_tienda';
				$row->calltask		   = '';
				$row->taskid		   = '';
				$row->category		   = '';
				if ( !$row->store() )
				{
					JError::raiseError(500, $row->getError());
				}	
			}		
		}
	}
}