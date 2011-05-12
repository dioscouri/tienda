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

abstract class TiendaToolPluginImportCsv extends TiendaToolPluginImport
{
	/*
	 * Array of indexes fields which we want to process (an empty array means we want to process all fields)
	 */
	public $import_fields = array();
	
	/*
	 * Number of fields in a row (0 means that it'll be calculated from the first row -> header)
	 */
  public $import_fields_num = 0;
  
  /*
   * Method to use to parse the data (1 - explode, 2 - our own (more complex and slower) method)
   */
  public $parse_method = 1;
  
  /*
   * Preserve header as a firt row of the result array
   */
  public $import_preserve_header = false;
  
  /*
   * If first line of the content should be skipped (not parsed as a record)
   */
  public $import_skip_first = true;
  
  /*
   * Delimier distinguishing records from each other (for method 2, it can be used also in field content)
   */
  public $import_rec_deliminer = "\n";
  
  /*
   * Deliminer distinguishing fields in a record
   */
  public $import_field_separator = ',';
  
  /*
   * If we want to get rid of double quotes in string-containing fields
   */
  public $import_clear_fields = true;
  
  /*
   * If we want to have the same field indexes in result array as in the CSV file
   */
  public $import_preserve_indexes = true;

  /*
   * If we are just beginning with the import
   */
  public $import_begin_import = true;
  
  /*
   * If we want to use the throttled import
   */
  public $import_throttled_import = false;
  
  /*
   * Number of records we want to parse (0 means all)
   */
  public $import_num_records = 0;

  /*
   * Offset in the file
   */
  public $import_offset = 0;

  /*
   * Default size of a chunk of a file
   */
  public $import_chunk_size = 4096;
 
	/*
	 * Sets default values for variables from request
	 */
	function getDefaultState()
	{
		parent::getDefaultState();
		$this->state->field_separator = ',';
		$this->state->skip_first = 0;
	}

	/*
	 * Prepares data for importing => upload the file to server
	 */
	function prepareData()
	{
		Tienda::load( 'TiendaFile', 'library.file' );
		$this->_vars->upload = new TiendaFile();
    // handle upload creates upload object properties
		$success = $this->_vars->upload->handleUpload( 'source_import' );
		if( $success ) // upload was sucessfull
		{
			
			// Move the file to let us reuse it 
			$this->_vars->upload->setDirectory( JFactory::getConfig()->get( 'tmp_path', JPATH_SITE.DS.'tmp' ) );
			$this->_vars->upload->upload();
			$this->_vars->upload->file_path = $this->_vars->upload->getFullPath();
		}
		else
		{
			$this->setError( JText::_( 'Could Not Upload File: '.$this->_vars->upload->getError() ) );
			return false;
		}

		// Set the uploaded file as the file to use during the real import
		$this->set( 'source_import', $this->_vars->upload->getFullPath() );
		return true;
	}
	
	/*
	 * Checks if format of the source is valid
	 */
	function checkSourceFormat()
	{
		if( strtolower( $this->_vars->upload->getExtension() ) != 'csv' ) // not a CSV file?!
		{
			$this->setError( JText::_( 'This is not a Supported file' ) );
			return false;
		}
		return true;
	}

	/*
	 * Loads data from the source
	 */
	function loadImportingData()
	{
		Tienda::load( 'TiendaFile', 'library.file' );
		$this->vars->upload = new TiendaFile();
    $this->vars->upload->full_path = $this->vars->upload->file_path = $this->source_import;
    $this->vars->upload->proper_name = TiendaFile::getProperName( $this->source_import );

    // load file
    if( !$this->import_throttled_import )
    {
    	$this->vars->upload->fileToText();
    	$this->source_data = $this->vars->upload->fileastext;
    }
    return true;
	}

	/*
	 * Parses importing data into format from which they can be migrated
	 */
	function parseData()
	{
   	Tienda::load( 'TiendaCSV', 'library.csv' );
   	
   	$this->getImportedFields(); // get list fields to import
   	$this->import_skip_first = $this->state->skip_first;
   	$this->import_field_separator = $this->state->field_separator;
   	
		$params = new JRegistry();
		$params->setValue( 'skip_first', $this->import_skip_first );
		$params->setValue( 'num_records', $this->import_num_records );
		$params->setValue( 'num_fields', $this->import_fields_num );
		$params->setValue( 'clear_fields', $this->import_clear_fields );
		$params->setValue( 'chunk_size', $this->import_chunk_size );
		$params->setValue( 'preserve_header', $this->import_preserve_header );
		$params->setValue( 'offset', $this->import_offset );
		$params->setValue( 'begin_import', $this->import_begin_import );
		$params->setValue( 'throttled_import', $this->import_throttled_import );
		$params->setValue( 'rec_deliminer', $this->import_rec_deliminer );
		$params->setValue( 'field_deliminer', $this->import_field_separator );
		
		if( $this->import_throttled_import ) // use name of the file as source for the importer
			$this->source_data = $this->source_import;
		else
		{
	   	$data = TiendaCSV::toArray( $this->source_data,
	   															$this->import_fields,
 	   															$this->import_fields_num,
	   															$this->parse_method,
	   															$params );
	
	   	if( !$data ) // an error during parsing data
	   	{
	   		$this->setError( JText::_( 'ERROR IN INTEGRITY OF DATA' ) );
	   		return false;
	   	}
	
			$this->set( 'data', $data );
		}
		return true;
	}
	
	/*
	 * Performs migration of data
	 * 
	 * @return Additional HTML code you would like to display on the final step
	 */
	function migrate()
	{
		if( $this->import_throttled_import )
		{
			$result = '';
			$this->import_skip_first = $this->state->skip_first;
			$this->import_field_separator = $this->state->field_separator;
   	
			$params = new JRegistry();
			$params->setValue( 'skip_first', $this->import_skip_first );
			$params->setValue( 'num_records', $this->import_num_records );
			$params->setValue( 'num_fields', $this->import_fields_num );
			$params->setValue( 'clear_fields', $this->import_clear_fields );
			$params->setValue( 'chunk_size', $this->import_chunk_size );
			$params->setValue( 'preserve_header', $this->import_preserve_header );
			$params->setValue( 'offset', $this->import_offset );
			$params->setValue( 'begin_import', $this->import_begin_import );
			$params->setValue( 'throttled_import', true );
			$params->setValue( 'rec_deliminer', $this->import_rec_deliminer );
			$params->setValue( 'field_deliminer', $this->import_field_separator );
			
			while( true )
			{
				$data = TiendaCSV::fromFileToArray( $this->source_import, $this->import_fields, $this->import_fields_num, $this->parse_method, $params );
				$c = count( $data[0] );
				$this->set( 'data', $data[0] );
				$result .= $this->migrate_data();
				if( $c != $this->import_num_records )
					break;
				$params->setValue( 'offset', $data[1] );
				$params->setValue('begin_import', false );  
			}
			return $result;
		}
		else
			return $this->migrate_data();
	}

	/*
	 * Performs the actual migration of data
	 * 
	 * @return Additional HTML code you would like to display on the final step
	 */
	function migrate_data()
	{
		return '';
	}
		
	
	/*
	 * Updates list of fields to import
	 */
	function getImportedFields()
	{
		$this->import_fields = array();
	}
}