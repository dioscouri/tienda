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
        $filePath = JPATH_ADMINISTRATOR.DS."components".DS."com_ambra".DS."defines.php";
        if (JFile::exists($filePath))
        {
            $success = true;
            if ( !class_exists('Ambra') )
            { 
                JLoader::register( "Ambra", JPATH_ADMINISTRATOR.DS."components".DS."com_ambra".DS."defines.php" );
            }
        }           
        return $success;
    }
    
    /**
     * Method is after an review on product
     * @param $row
     */
    function onAfterSaveProductComments( $row )
    {
        $success = null;

	    if (!$this->_isInstalled())
        {
            return $success;    
        }
     	
		JLoader::register( "Ambra", JPATH_ADMINISTRATOR.DS."components".DS."com_ambra".DS."helpers".DS."point.php");
        $helper = Ambra::get( "AmbraHelperPoint", 'helpers.point' );
		
			
        if ($helper->createLogEntry( $row->user_id, 'com_tienda', 'onAfterSaveProductComments' ))
        {
           JFactory::getApplication()->enqueueMessage( $helper->getError() );
        }
        
		return $success;
	}
	
	/**
	 * 
	 * @param unknown_type $row
	 */
	function doCompletedOrderTasks($orderid )
    {
        $success = null;
    	$user_id=JFactory::getUser()->id;

	    if (!$this->_isInstalled())
        {
            return $success;    
        }
        
	    $model = JModel::getInstance( 'Orders', 'TiendaModel' );
		$model->setId( $orderid );
		$item=$model->getItem();
		$subtotal=$item->order_subtotal;
		JLoader::register('AmbraConfig', JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ambra'.DS.'defines.php');
		$min_purchase_points=AmbraConfig::getInstance()->get('min_purchase_points', '');
		if ($subtotal>=$min_purchase_points)
		{
        	JLoader::register( "Ambra", JPATH_ADMINISTRATOR.DS."components".DS."com_ambra".DS."helpers".DS."point.php");
            $helper = Ambra::get( "AmbraHelperPoint", 'helpers.point' );
			
            if ($helper->createLogEntry( $user_id, 'com_tienda', 'doCompletedOrderTasks' ))
            {
                JFactory::getApplication()->enqueueMessage( $helper->getError() );
            }
		}
        
		return $success;
	}
}
    
