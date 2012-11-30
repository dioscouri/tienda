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

class TiendaUrl extends DSCUrl 
{
	public static function popup( $url, $text, $options = array() ) 
	{
	    if ($options['bootstrap']) {
	        return self::popupbootstrap( $url, $text, $options );
	    }
	    
		$html = "";
		
		JHTML::_('behavior.modal', 'a.tienda-modal');
		
		if (!empty($options['update']))
		{
		    $onclose = 'onClose: function(){ Dsc.update(); },';
		}
            else
		{
		    $onclose = '';
		}

		// set the $handler_string based on the user's browser
        $handler_string = "{handler:'iframe', ". $onclose ." size:{x: window.innerWidth-80, y: window.innerHeight-80}, onShow:$('sbox-window').setStyles({'padding': 0})}";
	    $browser = DSC::getClass( 'DSCBrowser', 'library.browser' );
        if ( $browser->getBrowser() == DSCBrowser::BROWSER_IE ) 
        {
            // if IE, use 
            $handler_string = "{handler:'iframe', ". $onclose ." size:{x:window.getSize().scrollSize.x-80, y: window.getSize().size.y-80}, onShow:$('sbox-window').setStyles({'padding': 0})}";            
        }
		
		$handler = (!empty($options['img']))
		  ? "{handler:'image'}"
		  : $handler_string;

		if (!empty($options['width']))
		{
			if (empty($options['height']))
			{
				$options['height'] = 480;
			}
			$handler = "{handler: 'iframe', ". $onclose ." size: {x: ".$options['width'].", y: ".$options['height']. "}}";
		}

		$id = (!empty($options['id'])) ? $options['id'] : '';
		$class = (!empty($options['class'])) ? $options['class'] : '';
		
		$html	= "<a class=\"tienda-modal\" href=\"$url\" rel=\"$handler\" >\n";
		$html 	.= "<span class=\"".$class."\" id=\"".$id."\" >\n";
        $html   .= "$text\n";
		$html 	.= "</span>\n";
		$html	.= "</a>\n";
		
		return $html;
	}

	/**
	 * TODO Push this upstream once tested
	 * 
	 * @param unknown_type $url
	 * @param unknown_type $text
	 * @param unknown_type $options
	 */
	public static function popupbootstrap( $url, $text, $options = array() )
	{
	    $version = isset($options['version']) ? $options['version'] : 'default';	    
	    DSC::loadBootstrap();
	    JHTML::_( 'script', 'bootstrap-modal.js', 'media/dioscouri/bootstrap/'.$version.'/js/' );

	    $time = time();
	    $modal_id = isset($options['modal_id']) ? $options['modal_id'] : 'modal-' . $time;
	    $button_class = isset($options['button_class']) ? $options['button_class'] : 'btn';
	    $label = 'label-' . $time;
	    
	    $button = '<a href="'.$url.'" data-target="#'.$modal_id.'" role="button" class="'.$button_class.'" data-toggle="modal">'.$text.'</a>';
	    
	    $modal = '';	    
        $modal .= '<div id="'.$modal_id.'" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="'.$label.'" aria-hidden="true">';
        $modal .= '    <div class="modal-header">';
        $modal .= '        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>';
        $modal .= '        <h3 id="'.$label.'">'.$text.'</h3>';
        $modal .= '    </div>';
            
        $modal .= '    <div class="modal-body">';
        $modal .= '    </div>';
        
        $modal .= '</div>';
	    
        return $button.$modal;
	}

}