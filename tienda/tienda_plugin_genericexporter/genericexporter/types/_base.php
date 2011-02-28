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

class TiendaGenericExporterTypeBase extends JObject
{	
	public $_format	= '';	
	public $_link = '';
	public $_name = '';
	public $_model = '';
	
	function TiendaGenericExporterTypeCSV($options = array())
	{		
		if(isset($options['model']))
		{
			$this->_model = $options['model'];
		}
	}
		
	/**	 
	 * Method to get the export format
	 * @return string
	 */
	function getFormat()
	{
		return $this->_format;
	}
	
	/**	 
	 * Method to set the model or the table
	 * @see /plugins/tienda/genericexporter/models
	 * @param string $model 
	 * @return void
	 */
	function setModel( $model = '' )
	{
		$this->_model = $model;
	}
	
	/**
	 * Method to process the export
	 * can be override by the child class
	 * @return 
	 */	
	function processExport()
	{		
	}	
}
