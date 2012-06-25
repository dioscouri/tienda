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

class TiendaGrid extends DSCGrid
{
	public static function required()
	{
	    $html = '<img src="'.Tienda::getUrl( 'images' ).'required_16.png" alt="'.JText::_('COM_TIENDA_REQUIRED').'">';
        return $html;
	}
	
}