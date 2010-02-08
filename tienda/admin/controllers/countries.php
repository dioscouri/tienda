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

class TiendaControllerCountries extends TiendaController 
{
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
		
		$this->set('suffix', 'countries');
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

        $state['filter_id_from']    = $app->getUserStateFromRequest($ns.'id_from', 'filter_id_from', '', '');
        $state['filter_id_to']      = $app->getUserStateFromRequest($ns.'id_to', 'filter_id_to', '', '');
        $state['filter_name']         = $app->getUserStateFromRequest($ns.'name', 'filter_name', '', '');
        $state['filter_code2']         = $app->getUserStateFromRequest($ns.'code2', 'filter_code2', '', '');
        $state['filter_code3']         = $app->getUserStateFromRequest($ns.'code3', 'filter_code3', '', '');
        
        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );   
        }
        return $state;
    }
}

?>