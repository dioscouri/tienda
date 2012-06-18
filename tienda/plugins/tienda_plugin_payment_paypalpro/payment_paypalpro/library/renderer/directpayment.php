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

require_once dirname(__FILE__) . '/../renderer.php';

/**
 * Tienda PayPalPro DirectPayment Renderer
 *
 * @package		Joomla 
 * @since 		1.5
 */
class plgTiendaPayment_Paypalpro_Renderer_Directpayment extends plgTiendaPayment_Paypalpro_Renderer
{
	
	/**
	 * Renders the DirectPayment form
	 * 
	 * @param object $row
	 * @param array $prepop Array of values to fill in the form fields
	 * @param boolean $display_note
	 * @param boolean $display_typeinfo
	 * @return string
	 * @access public
	 */
	function renderForm( $row, $prepop = array(), $display_note = '1', $display_typeinfo = '1' ) 
	{
		$user =& JFactory::getUser();
		$secure_post = $this->_params->get( 'secure_post', '0' );
		$config = &Tienda::getInstance();
		
		/*
		 * get all necessary data and prepare vars for assigning to the template
		 */		
		$vars = new JObject();
		$vars->action_url = JRoute::_( "index.php?option=com_tienda&controller=checkout&task=confirmPayment&orderpayment_type={$this->_plugin_type}&paction=process_direct_payment", false, $secure_post );
		$vars->prepop = $prepop;
		$vars->user =& $user;
		$vars->cctype_input = $this->_getCardTypesInput( 'cardtype', !empty($prepop['cardtype']) ? $prepop['cardtype'] : '' );
		$vars->country_input = $this->_getCountriesInput( 'country', !empty($prepop['country']) ? $prepop['country'] : '' );
		$vars->row =& $row;		
		$vars->token_input = JHTML::_( 'form.token' );
		$vars->display_note = $display_note;
		$vars->display_typeinfo = $display_typeinfo;
		$vars->display_value = $config->get( 'display_value', '1' );
	
		$vars->display_period = $config->get( 'display_period', '1' );
		
		$vars->currency_preval = $config->get( 'currency_preval', '$' );
		$vars->currency_postval	= $config->get( 'currency_postval', ' USD' );
		
		$html = $this->_getLayout('directpayment_form', $vars);
		return $html;
	}
	
	/**
	 * Generates a dropdown list of valid CC types
	 * 
	 * @param string $fieldname
	 * @param string $default
	 * @param string $options
	 * @return string
	 * @access protected
	 */
	function _getCardTypesInput( $field = 'cardtype', $default = '', $options = '' )
	{		
		$types = array();
		$types[] = JHTML::_('select.option', 'Visa', JText::_('COM_TIENDA_VISA') );
		$types[] = JHTML::_('select.option', 'MasterCard', JText::_('COM_TIENDA_MASTERCARD') );
		$types[] = JHTML::_('select.option', 'Amex', JText::_('COM_TIENDA_AMERICANEXPRESS') );
		$types[] = JHTML::_('select.option', 'Discover', JText::_('COM_TIENDA_DISCOVER') );
		//$types[] = JHTML::_('select.option', 'Maestro', JText::_('COM_TIENDA_MAESTRO') );		
		//$types[] = JHTML::_('select.option', 'Solo', JText::_('COM_TIENDA_SOLOT') );
		
		$return = JHTML::_('select.genericlist', $types, $field, $options, 'value','text', $default);
		return $return;
	}
	
	/**
	 * Generates a dropdown list of valid countries
	 * 
	 * @param string $fieldname
	 * @param string $default
	 * @param string $options
	 * @return string
	 * @access protected
	 */
	function _getCountriesInput( $field = 'country', $default = '', $options = '' )
	{		
		$types = array();
		$types[] = JHTML::_('select.option', 'US', JText::_('COM_TIENDA_UNITED_STATES') );
		$types[] = JHTML::_('select.option', 'CA', JText::_('COM_TIENDA_CANADA') );
		$types[] = JHTML::_('select.option', 'GB', JText::_('COM_TIENDA_UNITED_KINGDOM') );
		
		$return = JHTML::_('select.genericlist', $types, $field, $options, 'value','text', $default);
		return $return;
	}
}
