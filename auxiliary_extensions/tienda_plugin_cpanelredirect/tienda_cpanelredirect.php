<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');
jimport( 'joomla.application.component.model' );

class plgSystemTienda_CPanelRedirect extends JPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'tienda_cpanelredirect';
    
	function plgSystemTienda_CPanelRedirect(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}
	
    /**
     * 
     * @return unknown_type
     */
    function onAfterInitialise() 
    {
        $option = JRequest::getVar( 'option' );
        $app = JFactory::getApplication();
        if ($app->isAdmin() && (empty($option) || $option == 'com_cpanel'))
        {
            $app->redirect( 'index.php?option=com_tienda' );
        }
        return;
    }
}
