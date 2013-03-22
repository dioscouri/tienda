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

    public function addButton($product_id, $user_id, $name, $url = null, $text = 'Add', $attribs = array() ) {
		
		if(empty($attribs['addclass'])) {
			$attribs['addclass'] = 'addWishList wishlist btn btn-primary';
		}

		$html = '';
		$html .= '';
		$html .= '<a id="fav-' . $object_id . '-' . $scope_id . '" class="'.$attribs['addclass'].'"  data-loading-text="Loading..."';
		$html .= ' href="';
		$html .= $this -> makeurl($object_id, $scope_id, $name, $url);
		$html .= '">' . $text;
		$html .= '</a>';

		return $html;

	}

	public function removeButton($wid = null, $object_id = null, $scope_id = null, $name = null, $url = null, $text = 'remove', $attribs = array()) {
		if(empty($attribs['removeclass'])) {
			$attribs['removeclass'] = 'removeWishlist wishlist btn btn-danger';
		}
		if(empty($text)){
			$text =  'Remove' ;
		}
		$html = ''; 
		$html .= '<a id="fav-' . $pid. '" class="'.$attribs['removeclass'].'"  data-loading-text="Loading..."';
		$html .= ' href="';
		$html .= $this -> removeurl($pid,$object_id, $scope_id, $name, $url);
		$html .= '">' . $text;
		$html .= '</a>';

		return $html;
	}

	public function button($object_id, $scope_id, $name, $url = null, $text = array(), $attribs = array()) {
		
		if(empty($text)){
			$text[0] = Favorites::getInstance()->get( 'favorites_add_text', 'Add' );
			$text[1] = Favorites::getInstance()->get( 'favorites_remove_text', 'Remove' );
		}
		if(empty($attribs)){
			$attribs['addclass'] =  'addWishList wishlist btn btn-primary' ;
			$attribs['removeclass'] = 'removeWishlist wishlist btn btn-danger' ;
		}
		$user = JFactory::getUser();
		if ($user -> id) {
			DSCModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
			$model = DSCModel::getInstance('Wishlists', 'TiendaModel');
			
			$pid = $model -> checkItem($object_id, $scope_id, $name, $url, $user -> id);
		
			if ($pid) {
				return $this -> removeFavButton($pid, '','','','',$text[1]);
			} else {
				return $this -> addFavButton($object_id, $scope_id, $name, $url, $text[0] , $attribs);
			}
		}
	}

	function makeurl($object_id, $scope_id, $name, $url = null) {

		//$u = JFactory::getURI();
		$href = '';
		$href .= JURI::root();
		$href .= 'index.php?option=com_tienda&view=wishlists&task=add&format=raw';
		if (!empty($object_id)) {
			$href .= '&pid=' . $object_id;
		}
		

		return $href;
	}
	function removeurl($pid = null,$object_id = null, $scope_id= null, $name= null, $url = null) {

		//$u = JFactory::getURI();
		$href = '';
		$href .= JURI::root();
		$href .= 'index.php?option=com_tienda&view=wishlists&task=remove&format=raw';

		if (!empty($pid)) {
			$href .= '&pid=' . $pid;
		}
		

		return $href;
	}
}