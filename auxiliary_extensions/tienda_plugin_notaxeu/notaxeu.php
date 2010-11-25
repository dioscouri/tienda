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

Tienda::load( 'TiendaPluginBase', 'library.plugins._base' );

class plgTiendaNoTaxEu extends TiendaPluginBase
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'notaxeu';
    
	function plgTiendaNoTaxEu(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}
    
    /**
     * 
     * @param	TiendaTableOrders $order     The order table object
     * @return unknown_type
     */
    function onCalculateTaxTotals( &$order )
    {
        $params = $this->params;
        $geozone = $params->get('geozone_id');
        
        $order_geozones = $order->getBillingGeoZones();
        $geozones = array();
        foreach($order_geozones as $g)
        {
        	$geozones[] = $g->geozone_id; 
        }
        
        // Is in in the geozone, and is it a company?
        $is_company = strlen($order->getBillingAddress()->tax_number) > 0;
        if(in_array($geozone, $geozones) && ($is_company))
        {
        	if(strlen($order->getBillingAddress()->tax_number))
        	{
        		$order->order_tax = 0;
        		$order->save();
        	}
        }
    }
}
