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
            $redirect = "index.php?option=com_tienda&view=carts";
            $redirect = JRoute::_( $redirect, false );
            JFactory::getApplication()->redirect( $redirect );
            return;
        }
		
		parent::__construct();
		$this->set('suffix', 'dashboard');
	}
}