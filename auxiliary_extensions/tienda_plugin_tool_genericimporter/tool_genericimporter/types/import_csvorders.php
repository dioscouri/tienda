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

require_once JPATH_SITE.'/plugins/tienda/tool_genericimporter/genericimport_csv.php';

class plgTiendaTool_CsvOrders extends TiendaToolPluginImportCsv
{
	/*
	 * Name of the importer displayed in select box
	 */
	public $importer_name = 'CSV import 2';

	/*
	 * Sets default values for variables from request
	 */
	function getDefaultState()
	{
		parent::getDefaultState();
		$this->import_throttled_import = true;
		$this->import_num_records = 1;
		$this->state->lol = '(party)';
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
		$state = $this->get( 'state' );
		$checked = array( '', '\'checked\'' );
		$answer = array( JText::_( 'No' ), JText::_( 'Yes' ) );
		$rows = array();
		$only_value = $step == 2;
		$skip_first_value = strcmp( $state->skip_first, 'on') == 0;

		$rows[] = $this->generateRowInput( $only_value, JText::_( 'Source Import' ).': *' ,'file', 'source_import', $this->source_import );
		$rows[] = $this->generateRowInput( $only_value, JText::_( 'Separator' ) ,'text', 'field_separator', $state->field_separator, 10 );
		$rows[] = $this->generateRowInput( $only_value, JText::_( 'Skip First Row' ).'?' ,'checkbox', 'skip_first', $skip_first_value, null, $answer[$skip_first_value], null, $checked[$skip_first_value] );

		$this->set( 'table_rows', $rows );
		return parent::getAdminTableHtml( $step );
	}

	/*
	 * Performs the actual migration of data
	 * 
	 * @return Additional HTML code you would like to display on the final step
	 */
	function migrate_data()
	{
		return Tienda::dump( $this->data );
	}
}