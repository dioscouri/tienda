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

class TiendaControllerDashboard extends TiendaController 
{
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
		
		$this->set('suffix', 'dashboard');
	}

	function search()
	{
	    $filter = JRequest::getVar('tienda_search_admin_keyword');
	    $filter_view = JRequest::getCmd('tienda_search_admin_view');
	    
	    $redirect = "index.php?option=com_tienda&view=" . $filter_view . "&filter=" . urlencode( $filter );
	    
	    JFactory::getApplication()->redirect( $redirect );
	}
}

?>