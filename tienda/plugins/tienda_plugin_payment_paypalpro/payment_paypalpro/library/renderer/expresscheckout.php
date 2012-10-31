<?php
/**
 * @version	1.5
 * @package	tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

require_once dirname(__FILE__) . '/../renderer.php';

/**
 * tienda PayPalPro Expresscheckout Renderer
 *
 * @package		Joomla 
 * @since 		1.5
 */
class plgTiendaPayment_Paypalpro_Renderer_Expresscheckout extends plgTiendaPayment_Paypalpro_Renderer
{
	/**
	 * Renders the ExpressCheckout form
	 * 
	 * @param object $row
	 * @param array $prepop Array of values to fill in the form fields
	 * @return string
	 * @access protected
	 */
	function renderForm( $row, $prepop = array() ) 
	{
		$user = JFactory::getUser();
		$secure_post = $this->_params->get( 'secure_post', '0' );
		
		/*
		 * get all necessary data and prepare vars for assigning to the template
		 */		
		$vars = new JObject();
		
		$vars->action_url = JRoute::_("index.php?option=com_tienda&controller=checkout&task=confirmPayment&orderpayment_type={$this->_plugin_type}&paction=process_express_checkout", false, $secure_post);
		$vars->prepop = $prepop;
		$vars->user = $user;
		$vars->row = $row;		
		$vars->token_input = JHTML::_( 'form.token' );
		
		$html = $this->_getLayout('expresscheckout_form', $vars);
		return $html;
	}
}

