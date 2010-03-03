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
defined('_JEXEC') or die('Restricted access');

JLoader::import( 'com_tienda.helpers._base', JPATH_ADMINISTRATOR.DS.'components' );

class TiendaHelperDiagnostics extends TiendaHelperBase 
{
	/**
	 * Performs basic checks on your Tienda installation to ensure it is configured OK
	 * @return unknown_type
	 */
	function checkInstallation() 
	{
		// TODO check all DB tables for integrity
		
		// Check if a default currencies has been selected, and if the selected currency
		// really exists
		$default_currencyid = TiendaConfig::getInstance()->get('default_currencyid', '-1');
		if($default_currencyid == '-1'){
			$this->setError(JText::_("No Default Currency Selected"));
			return false;
		} else{
			// Check if the currency exists
			$table = &JTable::getInstance('Currencies', 'TiendaTable');
			if(!$table->load($default_currencyid)){
				$this->setError(JText::_("Currency does not exists"));
				return false;	
			}
		}
		return true;
	}

}