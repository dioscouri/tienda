<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Daniele Rosario
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

class plgTiendaTool_CsvOrders extends TiendaToolPluginImport
{

	/*
	 * Sets default values for variables from request
	 */
	function getDefaultState()
	{
		$this->_state->lol = '(party)';
	}

	/*
	 * Get HTML code for form layout of step 1
	 * 
	 * @return HTML code for the step
	 */
	function getHtmlStep1Form()
	{
		return Tienda::dump( $this->_state );
	}
}