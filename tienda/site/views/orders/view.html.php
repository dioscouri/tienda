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
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaViewBase', 'views._base', array( 'site'=>'site', 'type'=>'components', 'ext'=>'com_tienda' ) );

class TiendaViewOrders extends TiendaViewBase 
{
    /**
     * 
     * @param $tpl
     * @return unknown_type
     */
    function display($tpl=null, $perform = true )
    {
        $layout = $this->getLayout();
        switch(strtolower($layout))
        {
            case "email":
        	case "print":
            case "view":
                $this->_form($tpl);
              break;
            case "form":
                JRequest::setVar('hidemainmenu', '1');
                $this->_form($tpl);
              break;
            case "default":
            default:
                $this->_default($tpl);
              break;
        }       
        parent::display($tpl);
    }
    
    /**
     * Basic methods for a form
     * @param $tpl
     * @return unknown_type
     */
    function _form($tpl='')
    {
        parent::_form($tpl);
        
        $shop_info = array();
        
   		 // Get the shop country name
		$countryModel = JModel::getInstance('Countries', 'TiendaModel');
		$countryModel->setId(Tienda::getInstance()->get('shop_country'));
		$countryItem = $countryModel->getItem();
		if($countryItem){
			$shop_info['shop_country_name'] = $countryItem->country_name;
		}
		
		// Get the shop zone name
		$zoneModel = JModel::getInstance('Zones', 'TiendaModel');
		$zoneModel->setId(Tienda::getInstance()->get('shop_zone'));
		$zoneItem = $zoneModel->getItem();
		if($zoneItem){
			$shop_info['shop_zone_name'] = $zoneItem->zone_name;
		}
		
		$this->assign('shop_info', (object) $shop_info);
    }

    /*
     * Loads layour for displaying taxes
     * 
     * @params $tpl Specifies name of layout (null means cart_taxes)
     * 
     * @return Content of a layout with taxes
     */
		function displayTaxes( $tpl = null )
		{
			$tmpl = 'cart_taxes';
			if( $tpl !== null )
				$tmpl = $tpl;
			$this->setLayout( $tmpl );
			
			return $this->loadTemplate( null );
		}
}
