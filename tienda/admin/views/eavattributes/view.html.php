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

Tienda::load( 'TiendaViewBase', 'views._base' );

class TiendaViewEavAttributes extends TiendaViewBase 
{
    /**
     * 
     * @param $tpl
     * @return unknown_type
     */
    function getLayoutVars($tpl=null) 
    {
        $layout = $this->getLayout();
        switch(strtolower($layout))
        {
            case "view":
                $this->_form($tpl);
              break;
            case "form":
                JRequest::setVar('hidemainmenu', '1');
                $this->_form($tpl);
              break;
            case "default":
            default:
                $this->set( 'leftMenu', 'leftmenu_customfields' );
                $this->_default($tpl);
              break;
        }
    }
    
    function _form($tpl=null)
    {
    		$id = JRequest::getVar('id', '');
    		$model = $this->getModel();
			$item = $model->getItem($id);
			
			switch(@$item->eaventity_type)
			{
				case 'products':
					// Products
					$productModel 	= JModel::getInstance( 'ElementProduct', 'TiendaModel' );
		         	// terms
		         	$product = JTable::getInstance('Products', 'TiendaTable');
		         	$product->load(@$item->eaventity_id);
					$elementArticle_product 		= $productModel->_fetchElement( 'eaventity_id',@$product->product_name) ;
					$resetArticle_product		= $productModel->_clearElement( 'eaventity_id', '0' );
					$this->assign('elementproduct', $elementArticle_product);
					$this->assign('resetproduct', $resetArticle_product);
			}    		
			
			parent::_form($tpl);
    }
}
