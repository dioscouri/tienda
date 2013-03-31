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

    

	public function button($product_id, $attribs = array()) {
		
		$html = '';
		if(empty($attribs)){
			$attribs['addclass'] =  'addWishList wishlist btn btn-primary' ;
			$attribs['removeclass'] = 'removeWishlist wishlist btn btn-danger' ;
		}
		$user = JFactory::getUser();
		if ($user -> id) {

			if(empty($attribs['addclass'])) {
			$attribs['addclass'] = 'addWishList wishlist btn btn-primary';
			}
			if(empty($attribs['formName'])) {
			$attribs['formName'] = 'adminForm_'.$product_id;
			}
			$formName = $attribs['formName'];

			$onclick = "document.$formName.task.value='addtowishlist'; document.$formName.submit();";


			$text = JText::_('COM_TIENDA_ADD_TO_WISHLIST');
			$class = $attribs['addclass'];
                
              
			$html .= '<div id="add_to_wishlist_'.$product_id.'" class="add_to_wishlist btn-group">';
			$html .= '<a class="'.$class.'" href="javascript:void(0);" onclick="'.$onclick.'">'.$text.'</a>';

			$html .=  $this->makeSelect($user -> id);
			$html .= '</div>';
					
		} else {

		}

		return $html;
	}

	function makeSelect($user_id, $caret = true) {

		DSCModel::addIncludePath( JPATH_ADMINISTRATOR .'/components/com_tienda/models' );
		$model = DSCModel::getInstance('Wishlists', 'TiendaModel');
		$items = $model->getButtonLists($user_id);
       
        $html ='';
	        if($caret){
	        $html .= ' <button class="addWishList btn btn-primary dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>';	
	        }
        $html .= '<ul class="wishlists dropdown-menu nav nav-list">';
	        foreach ($items as $item) {
	         $checked= ''; if($item->default) {$checked= 'checked';}
	         $html .= '<li><label class="checkbox"><input type="radio" name="wishlist_id" value="'.$item->wishlist_id.'" '.$checked.'> '.$item->name.'</label></li>';
	        }


        $html .= '</ul>';

        return $html;
	}
	
}