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

class TiendaViewCheckout extends TiendaViewBase
{
	/**
	 * 
	 * @param $tpl
	 * @return unknown_type
	 */
	function display($tpl=null) 
	{
		$layout = $this->getLayout();
		switch(strtolower($layout))
		{
			case "view":
				$this->_form($tpl);
			  	break;
			case "form":
				$this->_form($tpl);
			  	break;
			case "review":
				$this->_default($tpl);
			  	break;
			case "default":
			default:
				$this->_default($tpl);
			  	break;
		}
		parent::display($tpl);		
	}
	
	/**
	 * We could actually get rid of this override entirely 
	 * and just call $items = TiendaHelperPlugin::getPlugins();
	 * from within the tmpl file  
	 * 
	 */
	function _default($tpl = null)
	{
        parent::_default($tpl);
        
        Tienda::load( 'TiendaUrl', 'library.url' );
        Tienda::load( 'TiendaSelect', 'library.select' );
        Tienda::load( 'TiendaHelperUser', 'helpers.user' );
			
		$model = $this->getModel();
		
        // form
		$form = array();
		$controller = strtolower( $this->get( '_controller', JRequest::getVar('controller', JRequest::getVar('view') ) ) );
		$view = strtolower( $this->get( '_view', JRequest::getVar('view') ) );
		$task = strtolower( $this->get( '_task', 'edit' ) );
		$form['action'] = $this->get( '_action', "index.php?option=com_tienda&view={$view}");
		$form['validation'] = $this->get( '_validation', "index.php?option=com_tienda&controller={$controller}&task=validate&format=raw" );
		$form['validate'] = "<input type='hidden' name='".JUtility::getToken()."' value='1' />";
		$form['id'] = $model->getId();
		$this->assign( 'form', $form );
	}
	
	/**
	 * Displays the checkout progress
	 * @param int step
	 * @return html the progress layout
	 */
	function displayProgress($step)
	{
		
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

	/**
	 * Generates shipping hash
	 * @param $rate		Array with a shipping rate which is actually set
	 * 
	 * @return	Shipping hash as a string
	 */
	function generateHash( $rate )
	{
		Tienda::load( 'TiendaHelperShipping', 'helpers.shipping' );
		$ship_values = array();
		$ship_values['type'] = $rate->shipping_type;
		$ship_values['name'] = $rate->shipping_name;
		$ship_values['price'] = $rate->shipping_price;
		$ship_values['tax'] = $rate->shipping_tax;
		$ship_values['code'] = $rate->shipping_code;
		$ship_values['extra'] = $rate->shipping_extra;
		
		return TiendaHelperShipping::generateShippingHash( $ship_values );
	}
}
?>