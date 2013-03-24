<?php
/**
 * @version 1.5
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

class WishlistHelper {

	function __construct(){
		DSC::loadJQuery();
		JHTML::_('script', 'wishlist.js', 'media/com_tienda/js/');
	}

    public function addButton($product_id,  $attribs = array() ) {
		
		if(empty($attribs['addclass'])) {
			$attribs['addclass'] = 'addWishList wishlist btn btn-primary';
		}
		$text = JText::_('COM_TIENDA_ADD_TO_WISHLIST');
		$html = '';
		$html .= '';
		$html .= '<a id="wishlistbutton-' . $product_id .'" class="'.$attribs['addclass'].'"  data-loading-text="Loading..."';
		$html .= ' href="';
		$html .= $this -> makeurl($product_id);
		$html .= '">' . $text;
		$html .= '</a>';

		return $html;

	}

	public function removeButton($product_id, $attribs = array()) {
		if(empty($attribs['removeclass'])) {
			$attribs['removeclass'] = 'removeWishlist wishlist btn btn-danger';
		}
		$text = JText::_('COM_TIENDA_REMOVE_FROM_WISHLIST');


		$html = ''; 
		$html .= '<a id="wishlistbutton-' . $product_id. '" class="'.$attribs['removeclass'].'"  data-loading-text="Loading..."';
		$html .= ' href="';
		$html .= $this -> removeurl($product_id);
		$html .= '">' . $text;
		$html .= '</a>';

		return $html;
	}

	public function button($product_id, $attribs = array()) {
		
		
		if(empty($attribs)){
			$attribs['addclass'] =  'addWishList wishlist btn btn-primary' ;
			$attribs['removeclass'] = 'removeWishlist wishlist btn btn-danger' ;
		}
		$user = JFactory::getUser();
		if ($user -> id) {
			DSCModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
			$model = DSCModel::getInstance('Wishlists', 'TiendaModel');
			
			$pid = $model -> checkItem($product_id, $user -> id);
		
			if ($pid) {
				return $this -> removeButton($product_id,$attribs);
			} else {
				return $this -> addButton($product_id, $attribs);
			}
		} else {

		}
	}

	function makeurl($product_id) {

		if (!empty($product_id)) {
		$href = '';
		$href .= JURI::root();
		$href .= 'index.php?option=com_tienda&view=wishlists&task=add&format=raw';
		$href .= '&pid=' . $product_id;
		}
		

		return $href;
	}
	function removeurl($product_id) {

		if (!empty($product_id)) {
		$href = '';
		$href .= JURI::root();
		$href .= 'index.php?option=com_tienda&view=wishlists&task=remove&format=raw';
		$href .= '&pid=' . $product_id;
		}
		

		return $href;
	}
}