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

abstract class TiendaToolPluginImport extends JObject
{
	/*
	 * So we can access variables from request
	 */
	public $_state; 

	/*
	 * So we can access specific variables in plugin used for this step
	 */
	public $_vars;

	/*
	 * Name of the importer displayed in select box
	 */
	public $importer_name = 'Generic Import';
	
	/*
	 * Gets the current step during import 
	 */
	function getCurrentStep()
	{
		if( empty( $state->uploaded_file ) ) // no file loaded -> usually step 1
		{
			return 1;
		}
		else // right now, we dont need to check if there is a step 3 so we're sure that this is the step 2
			return 2; 
	}

	/*
	 * Prepares data for importing
	 */
	function prepareData()
	{
		return true;
	}

	/*
	 * Checks if format of the source is valid
	 */
	function checkSourceFormat()
	{
		return true;
	}
	
	/*
	 * Loads data from the source
	 */
	function loadImportingData()
	{
		return true;
	}
	
	/*
	 * Checks if db contains everything what is required
	 */
	function checkIntegrityDb()
	{
		return true;
	}

	/*
	 * Parses importing data into format from which they can be migrated
	 */
	function _parseData()
	{
		return true;
	}

	/*
	 * Sets default values for variables from request
	 */
	function getDefaultState()
	{
	}

	/*
	 * Get HTML code for the specified step
	 * 
	 * @param $step Number of step
	 * @param $type "Form" - 1 or "Message" - 2
	 * 
	 * @return HTML code for the specified step
	 */
	function getHtmlStep( $step, $type )
	{
		switch( $step )
		{
			case 1 :
				if( $type == 1 )
					return $this->getHtmlStep1Form();
				break;
			case 2 :
				if( $type == 1 )
					return $this->getHtmlStep2Form();
				else
					if( $type == 2 )
						return $this->getHtemlStep2Msg();
				break;
			case 3:
				if( $type == 1 )
					return $this->getHtmlStep3Form();
				else
					if( $type == 2 )
						return $this->getHtemlStep3Msg();
				break;
		}
		return ''; // unknown step
	}
	
	/*
	 * Get HTML code for form layout of step 1
	 * 
	 * @return HTML code for the step
	 */
	function getHtmlStep1Form()
	{
		return JText::_( 'NO ADDITIONAL INFORMATION REQUIRED' );
	}

	/*
	 * Get HTML code for form layout of step 2
	 * 
	 * @return HTML code for the step
	 */
	function getHtmlStep2Form()
	{
		return '';
	}
	
	/*
	 * Get HTML code for form layout of step 3
	 * 
	 * @return HTML code for the step
	 */
	function getHtmlStep3Form()
	{
		return '';
	}

	/*
	 * Get HTML code for message layout of step 2
	 * 
	 * @return HTML code for the step
	 */
	function getHtmlStep2Msg()
	{
		return '';
	}
	
	/*
	 * Get HTML code for message layout of step 3
	 * 
	 * @return HTML code for the step
	 */
	function getHtmlStep3Msg()
	{
		return '';
	}
}