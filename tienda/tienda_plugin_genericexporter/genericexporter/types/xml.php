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

Tienda::load( 'TiendaGenericExporterTypeBase', 'genericexporter.types._base',  array( 'site'=>'site', 'type'=>'plugins', 'ext'=>'tienda' ));

class TiendaGenericExporterTypeXML extends TiendaGenericExporterTypeBase
{	
	public $_format			= 'XML';
	public $_lowercasetag 	= true;
	public $_modelone		= '';
	private $_count 		= -1;
	private $_root 			= true;
	
	function TiendaGenericExporterTypeXML($options = array())
	{		
		if(isset($options['model']))
		{
			$this->_model = $options['model'];
		}
	}		
	
	/**
	 * Method to set the xml tag casing
	 * @param boolean $case
	 * @return void
	 */
	function setTagCase($case)
	{
		$this->_lowercasetag = $case;
	}
	
	/**
	 * Method to process the export
	 * @return 
	 */	
	function processExport()
	{
		$export = new JObject();
		if(empty($this->_model))
		{
			$this->_errors = JText::_('PLEASE SET A MODEL IN THE PLUGIN METHOD PROCESSEXPORT');
			return $this;
		}
		
      	$classname = 'TiendaGenericExporterModel'.$this->_model; 
        Tienda::load( $classname, 'genericexporter.models.'.$this->_model,  array( 'site'=>'site', 'type'=>'plugins', 'ext'=>'tienda' ));                 
        $class = new $classname;        	
      	$items = $class->loadDataList();
    	
      	$this->_modelone = $class->getSingleName();
      	      	
		if(empty($items))
		{
			$this->_errors = JText::_('NO DATA FOUND');
			return $this;
		}

		//convert items to array upto child nodes	
		//$items = $this->objectToArray( $items );				
		$f_name = $this->_model.'_'.time().'.xml';
		$this->_link = 'tmp/'.$f_name;
	    $this->_name = $f_name;	     	
	  
	    if(!$res = $this->fromXMLToFile( 'tmp'.DS.$f_name, $items ))
	   	{
	    	$this->_errors = JText::_('ERROR SAVING FILE');	     	
		}
	     	     
	     return $this;
	}	
	
	/**
	 * Method to turn an object list into array upto child nodes	 
	 * @param mixed $items
	 * @return array
	 */
	private function objectToArray( $items )
	{			
		$arr_record = array();	
		$vars = (array) $items;	
		// go through all variables
		foreach( $vars as $key=>$value ) 
		{	
			$arr_record[$key] = is_object( $value ) || is_array( $value ) ? $this->objectToArray( $value ) : $value;			
		}
		
		return $arr_record;
	}
	
	
	/**	 
	 * Method to write XML into file
	 * @param string $file_path
	 * @param array $items
	 * @return boolean
	 */
	private function fromXMLToFile( $file_path , $items )
	{
		jimport( 'joomla.filesystem.file' );		
		$model = $this->_lowercasetag ? strtolower($this->_model) : strtoupper($this->_model);
		$modelone = $this->_lowercasetag ? strtolower($this->_modelone) : strtoupper($this->_modelone);
		
		$tab = '     ';
		$buffer = '<' . $model . '>';		
		$i = 0;		
		foreach($items as $item)
		{
			$buffer .= "\r\n";
			$buffer .= $tab.'<' . $modelone . '>';	

			if(is_object($item)) $item = (array) ( $item );	
			$buffer .= $tab.$tab.$this->arraytoXML($item, true);
			
			$buffer .= $tab.'</' .$modelone . '>';	
			$i++;
		}		
		$buffer .= "\r\n";
		$buffer .= '</' .$model . '>';	
		$xml = $this->getXML($buffer);		

		return JFile::write( $file_path, $xml );
	}
	
	/**
	 * Method to convert an array to XML
	 * @param array $tob the array to convert. if none specified, it can use the array from the previously parsed file/xml string
	 * @return string the xml string
	 */
	private function arraytoXML($array = array()) 
	{
		$result = "";
	
		if(!$countA = count($array))
		{
			$this->_errors = JText::_('NO DATA AVAILABLE FOR XML CREATION');			
			return $this;
		}	

		$i = 0;		
		foreach($array as $key => $value)
		{
			$tab = '          ';						
			$key = $this->_lowercasetag ? strtolower($key) : strtoupper($key);
			
			$found = false;
			if(is_string($value) && (strpos($value, '<') !== false  || strpos($value, '&') !== false ))
			{
				$found = true;
			}
			if($found)
			{
				$value = '<![CDATA[' . $value . ']]>';
			}
						
			$result .= "\r\n";			
			$result .= $tab . '<' . $key . '>';				
			$result .= is_array($value) || is_object($value) ? '' : $value;			
			$result .= '</' . $key . '>';

			$this->_root = false;			
			$i++;
		}		
		
		$result .= "\r\n";
		return $result;			
	}
	
	/**
	 * Method xml tag, version, encoding, etc...
	 * @param array $xml
	 * @param string $version the xml version
	 * @param string $encoding the encoding of the xml
	 * @param string $doctype a doctype to add at the xml file
	 * @return string the full xml string
	 */
	function getXML($xml = null, $version='1.0', $encoding='utf-8', $doctype='')
	{
		return '<?xml version="'. $version . '" encoding="' . $encoding . '"?>' . "\r\n" . ($doctype!='' ? $doctype . "\r\n" : '') . $xml;
	}
	
}
