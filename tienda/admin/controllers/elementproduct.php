<?php
/**
 * @version	1.5
 * @package	Tienda
 * @user 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

class TiendaControllerElementProduct extends DSCControllerAdmin 
{
	function __construct() 
	{
		parent::__construct();
		
		$this->set('suffix', 'elementproduct');
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

        $state['filter_state']    = $app->getUserStateFromRequest($ns.'filter_state', 'product_state', '', 'string');        
        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );   
        }
        return $state;
    }
    
    function display($cachable=false, $urlparams = false)
    {
        $this->hidefooter = true;
        
        $object = JRequest::getVar('object');
        $view = $this->getView( $this->get('suffix'), 'html' );
        $view->assign( 'object', $object );
        parent::display();
    }
}

?>