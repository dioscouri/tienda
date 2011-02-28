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
	public $_format	= 'XML';
	
	function TiendaGenericExporterTypeXML($options = array())
	{		
		if(isset($options['model']))
		{
			$this->_model = $options['model'];
		}
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
			$this->_errors = JText::_("Please set a model in the plugin method processExport");
			return $this;
		}
		
    	$arr = array();
      	$header = array(); // header -> it'll be filled out when 
      	$fill_header = true; // we need to fill header
      	
      	$classname = 'TiendaGenericExporterModel'.$this->_model; 
        Tienda::load( $classname, 'genericexporter.models.'.$this->_model,  array( 'site'=>'site', 'type'=>'plugins', 'ext'=>'tienda' ));                 
        $class = new $classname;        	
      	$list = $class->loadDataList();
debug(333333, $list);      	
		if(empty($list))
		{
			$this->_errors = JText::_("No Data Found");
			return $this;
		}
  
		for( $i = 0, $c = count( $list ); $i < $c; $i++ )
	    {
	    	if( $fill_header ) // need to fill header yet ?
	       	{
	       		$list_vars = get_object_vars( $list[$i] );	       	
	       		foreach( $list_vars as $key => $value ) // go through all variables
	       		{
	       			if( $fill_header )
	       			{
	       				$header[] = $key;
	       			}
	         	}
	         	$fill_header = false; // header is filled
	    	}
	       	$arr[] = $this->objectToString( $list[$i], true );
	     }
	     $f_name = $this->_model.'_'.time().'.csv';	     
	     
		 $this->_link = 'tmp'.DS.$f_name;
	     $this->_name = $f_name;
	     	
	    // if(!$res = TiendaCSV::FromArrayToFile( 'tmp'.DS.$f_name, $arr, $header ))
	    // {
	     //	$this->_errors = JText::_("Unable to write file.");	     	
	    // }
	     	     
	     return $this;
	}	

	function createXML()
	{
	
	
	}
	
	
	/**	
	 * Method to convert object to string
	 * @param object $obj
	 * @param unknown_type $root
	 */
	private function objectToString( $obj, $root = false )
    {
    	$arr_record = array();
		$list_vars = get_object_vars( $obj );
	
		foreach( $list_vars as $key => $value ) // go through all variables
		{
			if( is_object( $value ) )
			{
				$arr_record[] = $this->objectToString( $value );
			}
			else
			{
				if( is_array( $value ) )
				{
					@$value = implode( "\n", $value );
				}
				
				if($root)
				{
				
				}
				$arr_record[] = $root ?  $value : $key.'='.@urlencode( $value );		
			}
		}
		
		if( $root )
		{
			return $arr_record;
		}
		
		return implode( "\n", $arr_record );
    }
}
