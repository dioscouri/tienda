<?php
/**
 * @version	1.5
 * @package	Tienda
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaToolPlugin', 'library.plugins.tool' );

class plgTiendaTool_GenericImporter extends TiendaToolPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
	var $_element   = 'tool_genericimporter';

	/*
	 * Array of values from request
	 */
	var $state;
	
	/*
	 * Array of specific variables in plugin used for this step
	 */
	var $vars;
	
	/*
	 * Importer class
	 */
	var $_importer;

	/*
	 * Prefix for files containing importers
	 */
	var $_file_prefix = 'import_';

	/*
	 * Prefix for objects containing importers
	 */
	var $_plugin_prefix = 'plgTiendaTool_';
	
	function plgTiendaTool_GenericImporter(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
		require_once JPATH_SITE.'/plugins/tienda/tool_genericimporter/genericimport.php';
		
		$this->state = new JObject();
		$this->vars = new JObject();
		
		$this->state->importer = JRequest::getCmd( 'importer' ); // get name of current importer
		$this->import_select = $this->_getListImporters();

		if ( $this->importerExists( $this->state->importer ) ) // check if the importer exists
		{
			// that's our boy -> load him
			$classname = $this->_plugin_prefix.$this->state->importer;
			$this->_importer = new $classname();
			$this->_importer->state = &$this->state; // save pointer to the array with variables from request
			$this->_importer->vars = &$this->vars; // save pointer to the array with variables for this step
			$this->_importer->plugin = &$this; // pointer to this plugin
		}
		else // the importer does not exist
		{
			$this->_importer = NULL;
		}
		
		$this->_getState(); // get variables from request ( we do it only once and then we store the result)
	}
	
	/*
	 * Checks if an importer with the name exists
	 * 
	 * @param $name Name of the importer
	 * 
	 * @return True or False
	 */
	function importerExists( $name )
	{
		jimport('joomla.filesystem.file');
		$file = JPATH_SITE.'/plugins/tienda/tool_genericimporter/types/'.$this->_file_prefix.$name.'.php';
		$success = JFile::exists( $file ); // does this file exist?

		if( $success ) // load him, if you can
		{
			require_once $file;
		}
		return $success;
	}
	
	/*
	 * Whether an importer is loaded
	 * 
	 * @return True of False
	 */
	function isLoaded()
	{
		return !empty( $this->_importer );
	}
	
	/**
		* Overriding 
		* 
		* @param $options
		* @return unknown_type
	*/
	function onGetToolView( $row )
	{
		if ( !$this->_isMe( $row ) )
		{
			return null;
		}
        
		// go to a "process suffix" method
		// which will first validate data submitted,
		// and if OK, will return the html?
		$suffix = $this->_getTokenSuffix();
		$html = $this->_processSuffix( $suffix );

		return $html;
	}

	/**
		* Validates the data submitted based on the suffix provided
		* 
		* @param $suffix
		* @return html
		*/
	function _processSuffix( $suffix='' )
	{
		$html = "";
	
		switch( $suffix )
		{
			case "2":
    		if ( !$verify = $this->_verifyData() )
    		{
					JError::raiseNotice( '_verifyData', $this->getError() );
					$suffix = 1;
					$html .= $this->_renderForm( '1' );
				}
				else
				{
					// migrate the data and output the results
					if( !$this->isLoaded() ) // no importer loaded
					{
						$this->setError( JText::_('No Importer Loaded') );
						$html .= $this->_renderForm( '1' );
					}
					else
					{
						// do the actual migration
						$html .= $this->_doMigration();
					}
				}
				break;
    	case "1":
				if ( !$verify = $this->_verifyData() )
    		{
    			JError::raiseNotice( '_verifyData', $this->getError() );
    			$html .= $this->_renderForm( '1' );
    		}
    		else
    		{
    			$suffix++;

    			// display a 'connection verified' message
    			// and request confirmation before migrating data
    			$html .= $this->_renderForm( $suffix );
        
    			$html .= $this->_renderView( $suffix );                    
    		}
    		break;
    	default:
    		$html .= $this->_renderForm( '1' );
    		break;
		}
		return $html;
	}
	
	/**
		* Prepares the 'view' tmpl layout
		*  
		* @return unknown_type
		*/
	function _renderView( $suffix='' )
	{
		$layout = 'view_'.$suffix;
		$html = $this->_getLayout( $layout, $this->vars );

		return $html;
	}
  
	/**
		* Prepares variables for the form
		* 
		* @return unknown_type
		*/
	function _renderForm( $suffix='' )
	{
    $this->vars->token = $this->_getToken( $suffix );

    $layout = 'form_'.$suffix;
    $html = $this->_getLayout( $layout, $this->vars );

    return $html;
	}

	/*
	 * Get HTML code for the specified step => wrapper for importers (for AJAX calls) 
	 * so it depends on it gets data from Request
	 * 
	 * @return HTML code for the specified step
	 */
	function getHtmlStepAjax()
	{
		$response = array();
		$response['error'] = 0;
		$response['msg'] = '';

		if( !$this->isLoaded() ) // no importer
		{
			$response['msg'] = JText::_('No Additional Information');
			echo json_encode( $response );
			return;
		}

		$step = JRequest::getInt( 's' ,1 );
		$type = JRequest::getInt( 't', 1); // 1 - form, 2 - view
		
		$response['msg'] = $this->_importer->getHtmlStep( $step, $type );
		echo json_encode( $response );
	}
	
	/*
	 * Get HTML code for the specified step => wrapper for importers 
	 * 
	 * @param $step Actual step
	 * @param $type Type of layout (1 - form; 2 - view)
	 * 
	 * @return HTML code for the specified step
	 */
	function getHtmlStep( $step, $type )
	{
		if( !$this->isLoaded() ) // no importer
			return JText::_('No Additional Information');

		return $this->_importer->getHtmlStep( $step, $type );
	}
		
	/*
	 * Gets a select box of importers
	 * 
	 * @return HTML code of the select box
	 */
	function _getListImporters()
	{
		jimport( 'joomla.filesystem.folder' );
		$path = JPATH_SITE.'/plugins/tienda/tool_genericimporter/types';
		$files = JFolder::files( $path );
		$options = array();
		$skip_prefix = strlen( $this->_file_prefix );

		// get all importers and get their names
		for( $i = 0, $c = @count( $files); $i < $c; $i++ )
		{
			require_once $path.'/'.$files[$i];
			$name_letters = strlen( $files[$i] ) - $skip_prefix - 4; // 4 => '.php'
			$name = substr( $files[$i], $skip_prefix, $name_letters );
			$classname = $this->_plugin_prefix.$name;
			if( class_exists( $classname ) )
			{
				$obj = new $classname();
				$options[] = JHtml::_( 'select.option', $name, JText::_( $obj->get( 'importer_name' ) ) );
			}
		}
		
		$js = ' onchange="tiendaGetImporterLayout( this, \'divAdditionalInfo\' );" ';
		return JHtml::_( 'select.genericlist', $options, 'importer', $js, 'value', 'text', $this->state->importer );
	}

	/*
	 * Adds a button to the toolbar
	 */
	function onAfterDisplayAdminComponentTienda()
	{
		// get ID of this plugin
		$db = JFactory::getDbo();
		$q = 'SELECT id FROM #__plugins WHERE `element` = \'tool_genericimporter\'';
		$db->setQuery( $q );
		$id = $db->loadResult();
		$url = 'index.php?option=com_tienda&view=tools&task=view&id='.$id;
		$bar = JToolBar::getInstance('toolbar');
		$bar->prependButton( 'link', 'Importer', 'Generic Importer', $url );
	}

	/*
	 * THESE FUNCTIONS USES FUNCTIONS FROM THE LOADED IMPORTER
	 */

	/**
		* Gets the appropriate values from the request
		*/
	function _getState()
	{
		if( $this->isLoaded() ) // get default state of values from request from the current importer
		{
			$this->_importer->getDefaultState();
			$this->_importer->source_import = JRequest::getVar( 'source_import', @$this->_importer->source_import );
		}
		    
		foreach( $this->state->getProperties() as $key => $value )
		{
			$new_value = JRequest::getVar( $key );
			$value_exists = array_key_exists( $key, $_POST );
			if ( $value_exists && !empty( $key ) )
			{
				$this->state->$key = $new_value;
			}
		}
	}

	/*
	 * Verifies integrity of importing data
	 */
	function _verifyData()
	{
		if( !$this->isLoaded() ) // no importer loaded :(
		{
			$this->setError( JText::_('No Importer Loaded') );
			return false;
		}
		
		if( $this->_importer->getCurrentStep() == 1 ) // first state => validation data
		{
			// prepare data for importing (i.e. upload a file to a server)
			$success = $this->_importer->prepareData();
			if( !$success ) // error during preparing data
			{
				$this->setError( JText::_('Error during preparing data for importing').' '.$this->_importer->getError() );
				return false;
			}
			
			// check format of the souce (i.e. if a file has a correct extension )
			$success = $this->_importer->checkSourceFormat();
			if( !$success ) // error during checking initial format
			{
				$this->setError( JText::_('Error during checking format of source').' '.$this->_importer->getError() );
				return false;
			}
			
			return true;
		}
		else if(  $this->_importer->getCurrentStep() == 2 ) // just to be sure we check if this is really a step 2
		{
			// load importing data
			$success = $this->_importer->loadImportingData();
			if( !$success ) // error during loading importing format
			{
				$this->setError( JText::_('Error during loading importing data').' '.$this->_importer->getError() );
				return false;
			}

			// check if everything in DB is ready ( i.e. adding xref tables if necessary )
			$success = $this->_importer->checkIntegrityDb();
			if( !$success ) // error during checking integrity of data
			{
				$this->setError( JText::_('Error during checking integirty of db').' '.$this->_importer->getError() );
				return false;
			}
			
			$success = $this->_importer->parseData();
			if( !$success ) // error during parsing importing format
			{
				$this->setError( JText::_('Error during parsing importing data').' '.$this->_importer->getError() );
				return false;
			}
			
			return $success;
		}
		return false; /// something went wrong as nothing happend until now
	}

	/*
	 * Performs the actual migration
	 */
	function _doMigration()
	{
		$suffix = $this->_getTokenSuffix();
		$layout = 'view_'.++$suffix;
		$this->vars->additional_html = $this->_importer->migrate();

		return $this->_getLayout( $layout, $this->vars );
	}
}