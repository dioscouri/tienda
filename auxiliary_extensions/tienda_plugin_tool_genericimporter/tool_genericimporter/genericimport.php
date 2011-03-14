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
	public $state; 

	/*
	 * So we can access specific variables in plugin used for this step
	 */
	public $vars;

	/*
	 * Importing data
	 */
	public $data;
	
	/*
	 * Rows of the admintable table
	 */
	public $table_rows = array();

	/*
	 * Name of the importer displayed in select box
	 */
	public $importer_name = 'Generic Import';
	
	/*
	 * Source of importing data
	 */
	public $source_import = '';

	/*
	 * Data from source
	 */
	public $source_data = '';

	/*
	 * Pointer to parent
	 */
	public $plugin; 
	
	/*
	 * Description of importer in form 2
	 */
	public $tool_description = 'THIS TOOL HANDLES GENERIC IMPORT INTO TIENDA';
	
	/*
	 * Note from importer in form 2
	 */
	public $form_2_note = 'PLEASE REVIEW THE FOLLOWING INFORMATION';

	/*
	 * Note from importer in form 2
	 */
	public $form_2_fieldset = 'Additional Information';	

	/*
	 * Gets the current step during import 
	 */
	function getCurrentStep()
	{
		if( empty( $this->source_import ) ) // no file loaded -> usually step 1
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
	function parseData()
	{
		return true;
	}

	/*
	 * Performs migration of data
	 * 
	 * @return Additional HTML code you would like to display on the final step
	 */
	function migrate()
	{
		return '';
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
	 * @param $type "Form" - 1 or "View" - 2
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
						return $this->getHtmlStep2View();
				break;
			case 3:
				if( $type == 2 )
					return $this->getHtmlStep3View();
				break;
		}
		return ''; // unknown combination of a step and a type
	}
	
	/*
	 * Get HTML code for form layout of step 1
	 * 
	 * @return HTML code for the step
	 */
	function getHtmlStep1Form()
	{
		return $this->getAdminTableHtml( 1 );
	}

	/*
	 * Get HTML code for form layout of step 2
	 * 
	 * @return HTML code for the step
	 */
	function getHtmlStep2Form()
	{
		return $this->getAdminTableHtml( 2 );
	}

	/*
	 * Get HTML code for view layout of step 2
	 * 
	 * @return HTML code for the step
	 */
	function getHtmlStep2View()
	{
		return '';
	}
	
	/*
	 * Get HTML code for view layout of step 3
	 * 
	 * @return HTML code for the step
	 */
	function getHtmlStep3View()
	{
		return '';
	}
	
	/*
	 * Generates HTML code for the usual admintable table
	 * 
	 * @param $step Step for which the code is generated
	 * 
	 * @return String HTML code of the table
	 */
	function getAdminTableHtml( $step )
	{
		if( count( $this->get( 'table_rows' ) ) )
		{
			$html = '<table class="admintable">'."\n";
			$html.= implode( "\n", @$this->get( 'table_rows' ) );
			$html.= "\n".'</table>';
		
			return $html;
		}
		else // otherwise inform user that there are no additional information required
			return JText::_( 'NO ADDITIONAL INFORMATION REQUIRED' );
	}
	
	/*
	 * Generates a row with raw data for the admintable table
	 * 
	 * @param $key Key for this row
	 * @param $raw Raw content of the second column
	 * 
	 * @return String HTML code for this row
 	 */
	function generateRowRaw( $key, $raw )
	{
		$html = '  <tr>'."\n";
		$html.= '  <td width="100" align="right" class="key">'.$key.'</td>'."\n";
		$html.= '  <td>'.$raw.'</td>'."\n";
		$html.= "  <td></td>\n";
		$html.= '</tr>';
		
		return $html;
	}
	
	/*
	 * Generates a row with an input element for the admintable table
	 * 
	 * @param $only_value If true, only value+a hidden input field with it is returned (useful for step 2)
	 * @param $key Key for this row
	 * @param $type type of the element
	 * @param $name Name of the element
	 * @param $value Value of the element (empty by default)
	 * @param $size Size of the input field (none defined by default)
	 * @param $value_only Text for only_value=true when the text is different from value - for example for checkboxes (empty by default)
	 * @param $maxlength Max number of chars for the input (none defined by default)
	 * @param $attr Additional attributes for the input (none by default)
	 * @param $id ID of the element (in case it is not provided, name attribute is used)
	 * @param $hide_only_value Hides this row if we want to (false by default)
	 * 
	 * @return String HTML code for this row
 	 */
	function generateRowInput( $only_value, $key , $type, $name, $value = null, $size = null, $value_only = null, $maxlength = null, $attr = null, $id = null, $hide_value_only = false )
	{		
		if( !$id )
			$id = $name;
	
		$input = '';
		if( $only_value ) // display only value and hide the element
		{
			if( $hide_value_only ) // hide this row if we want to
				return '';

			if( empty( $value_only ) ) // display the real value
				$html = $value;
			else // display a corresponding text instead of the real value
				$html = $value_only;
			
			$input = $html.'<input type="hidden" name="'.$name.'" id="'.$id.'" value="'.@$value.'" />';
		}
		else // display the actual input element
		{
			if( !empty( $value ) )
				$value = ' value="'.$value.'" ';

			if( !empty( $size ) )
				$size = ' size="'.$size.'" ';

			if( !empty( $maxlength) )
				$size = ' maxlength="'.$maxlength.'" ';
			
				$input = '<input type="'.$type.'" name="'.$name.'" id="'.$id.'" '.$value.$attr.$size.$maxlength.' />';
		}
		return $this->generateRowRaw( $key, $input );
	}
}