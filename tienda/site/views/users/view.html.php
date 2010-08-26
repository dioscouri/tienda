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

JLoader::import( 'com_tienda.views._base', JPATH_SITE.DS.'components' );
Tienda::load( 'TiendaUrl', 'library.url' );

class TiendaViewUsers extends TiendaViewBase  
{
	/**
	 * 
	 * @param $tpl
	 * @return unknown_type
	 */
	function display($tpl=null) 
	{
		
		$layout = $this->getLayout();
		echo " I am calling janu ji ".$layout."  ".$tpl;
		switch(strtolower($layout))
		{
			case "form":
				$this->_default($tpl);
			  break;
			case "default":
			default:
				$this->_default($tpl);
			  break;
		}
		parent::display($tpl);
	}
	

}