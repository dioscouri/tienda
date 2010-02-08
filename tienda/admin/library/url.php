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
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

class TiendaUrl {
	
	/**
	 * Wrapper that adds the current Itemid to the URL
	 *
	 * @param	string $string The string to translate
	 *
	 */
	function &_( $url, $text, $params='', $xhtml=true, $ssl=null, $addItemid='1' ) {
		if ($addItemid == '1') { $url = TiendaUrl::addItemid($url); }
		$return = "<a href='".JRoute::_($url, $xhtml, $ssl)."' ".addslashes($params)." >".$text."</a>";
		return $return;			
	}

	/**
	 * Wrapper that adds the current Itemid to the URL
	 *
	 * @param	string $string The string to translate
	 *
	 */
	function &addItemid( $url ) {
		global $Itemid;
		$return = $url;
		$return.= "&Itemid=".$Itemid;
		return $return;			
	}

	/**
	 * Wrapper that adds the current Itemid to the URL
	 *
	 * @param	string $string The string to translate
	 *
	 */
	function popup( $url, $text, $width='', $height='', $top=0, $left=0, $class='', $update=false, $img=false ) 
	{
		$html = "";
		($update)
            ? JHTML::_('behavior.modal', 'a.modal', array('onClose'=>'\function(){tiendaUpdate();}'))
            : JHTML::_('behavior.modal');

		$url;
		
		$handler = ($img)
		  ? "{handler:'image'}"
		  : "{handler:'iframe',size:{x:window.getSize().scrollSize.x-80, y: window.getSize().size.y-80}, onShow:$('sbox-window').setStyles({'padding': 0})}";

		if (!empty($width))
		{
			if (empty($height))
			{
				$height = 480;
			}
			$handler = "{handler: 'iframe', size: {x: $width, y: $height}}";
		}

		$html	= "<a class=\"modal\" href=\"$url\" rel=\"$handler\" >\n";
		$html 	.= "<span class=\"$class\" >\n";
        $html   .= "$text\n";
		$html 	.= "</span>\n";
		$html	.= "</a>\n";
		
		return $html;
	}

}