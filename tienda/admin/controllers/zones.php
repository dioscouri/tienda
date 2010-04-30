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
defined( '_JEXEC' ) or die( 'Restricted access' );

class TiendaControllerZones extends TiendaController 
{
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
		
		$this->set('suffix', 'zones');
	}
	
	/**
	 * Sets the model's state
	 * 
	 * @return array()
	 */
	function _setModelState()
	{
		$state = parent::_setModelState();   	
		$app = JFactory::getApplication();
		$model = $this->getModel( $this->get('suffix') );
		$ns = $this->getNamespace();

        $state['filter_id_from']    = $app->getUserStateFromRequest($ns.'id_from', 'filter_id_from', '', '');
        $state['filter_id_to']      = $app->getUserStateFromRequest($ns.'id_to', 'filter_id_to', '', '');
        $state['filter_name']         = $app->getUserStateFromRequest($ns.'name', 'filter_name', '', '');
        $state['filter_code']         = $app->getUserStateFromRequest($ns.'code', 'filter_code', '', '');
		$state['filter_countryid'] 	= $app->getUserStateFromRequest($ns.'countryid', 'filter_countryid', '', '');
		
		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );	
		}
		return $state;
	}

	/**
	 * 
	 * @return unknown_type
	 */
	function filterZones() 
	{
		JLoader::import( 'com_tienda.library.json', JPATH_ADMINISTRATOR.DS.'components' );
		Tienda::load( 'TiendaSelect', 'library.select' );
		
		$idtag = 'zone_id';
		$countryid = JRequest::getVar( 'countryid', '', 'request', 'int' );
		$idprefix = JRequest::getVar( 'idprefix', '', 'request');
		if (count($idprefix)>0){$idtag = $idprefix.$idtag;}		
		
		$url = "index.php?option=com_tienda&format=raw&controller=zones&task=addZone&geozoneid=";
		$attribs = array( 
			'class' => 'inputbox',
			'size' => '1');
		
		$hookgeozone = JRequest::getVar( 'hookgeozone', TRUE, 'request', 'boolean' );
		if($hookgeozone){
			$attribs['onchange'] = 'tiendaDoTask( \''.$url.'\'+document.getElementById(\'geozone_id\').value+\'&zoneid=\'+this.options[this.selectedIndex].value, \'current_zones_wrapper\', \'\');';
		}

		$html = TiendaSelect::zone( '', $idtag, $countryid, $attribs, $idtag, true);

		// set response array
		$response = array();
		$response['msg'] = $html;
			
		// encode and echo (need to echo to send back to browser)
		echo ( json_encode( $response ) );

		return;
	}

	/**
	 * 
	 * @return unknown_type
	 */
	function addZone() 
	{
		JLoader::import( 'com_tienda.library.json', JPATH_ADMINISTRATOR.DS.'components' );

		$zoneid = JRequest::getVar( 'zoneid', '', 'request', 'int' );		
		$geozoneid = JRequest::getVar( 'geozoneid', '', 'request', 'int' );

		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$zonerelation = JTable::getInstance( 'Zonerelations', 'TiendaTable' );
		$zonerelation->zone_id = $zoneid;
		$zonerelation->geozone_id = $geozoneid;

		$html = ($zonerelation->save()) ? TiendaHTML::zoneRelationsList($geozoneid) : $zonerelation->getError();

		// set response array
		$response = array();
		$response['msg'] = $html;
			
		// encode and echo (need to echo to send back to browser)
		echo ( json_encode( $response ) );

		return;
	}

	/**
	 * 
	 * @return unknown_type
	 */
	function removeZone() 
	{
		JLoader::import( 'com_tienda.library.json', JPATH_ADMINISTRATOR.DS.'components' );

		$zrid = JRequest::getVar( 'zrid', '', 'request', 'int' );		
		$geozoneid = JRequest::getVar( 'geozoneid', '', 'request', 'int' );

		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$zonerelation = JTable::getInstance( 'Zonerelations', 'TiendaTable' );
		$zonerelation->load( $zrid );

		$html = ($zonerelation->delete()) ? TiendaHTML::zoneRelationsList($geozoneid) : $zonerelation->getError();

		// set response array
		$response = array();
		$response['msg'] = $html;
			
		// encode and echo (need to echo to send back to browser)
		echo ( json_encode( $response ) );

		return;
	}
}

?>