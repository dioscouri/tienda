<?php
/**
 * @version    1.5
 * @package    Tienda
 * @author     Dioscouri Design
 * @link     http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaViewBase', 'views._base', array( 'site'=>'site', 'type'=>'components', 'ext'=>'com_tienda' ) );

class TiendaViewCarts extends TiendaViewBase  
{
    
    
    /**
	 * Basic commands for displaying a list
	 *
	 * @param $tpl
	 * @return unknown_type
	 */
 /*	function _default($tpl='')
	{
		
				
		// form
			$validate = JUtility::getToken();
			$form = array();
			$view = strtolower( JRequest::getVar('view') );
			$form['action'] = "index.php?option=com_tienda&controller={$view}&view={$view}";
			$form['validate'] = "<input type='hidden' name='{$validate}' value='1' />";
			$this->assign( 'form', $form );
    }
   */ 
}