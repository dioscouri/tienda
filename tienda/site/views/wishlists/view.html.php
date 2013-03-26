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

class TiendaViewWishlists extends TiendaViewBase  
{


	 /**
     * Basic methods for a form
     * @param $tpl
     * @return unknown_type
     */
    function _form($tpl='')
    {
       		$model = $this->getModel();

        // get the data
            $row = $model->getItem();
            JFilterOutput::objectHTMLSafe( $row );
            $this->assign('row', $row );

        // form
            $form = array();
            $form['action'] = $this->get( '_action', "index.php?option=com_tienda&view=wishlists&task=save&id=".$model->getId() );
            $form['validation'] = $this->get( '_validation', "index.php?option=com_tienda&view=wishlists&task=validate&format=raw" );
			$validate = JSession::getFormToken();		  
		    $form['validate'] = "<input type='hidden' name='".$validate."' value='1' />";
		    $form['id'] = $model->getId();
            $this->assign( 'form', $form );
        // set the required image
        // TODO Fix this
            $required = new stdClass();
            $required->text = JText::_( 'LIB_DSC_REQUIRED' );
            $required->image = DSCGrid::required( 'LIB_DSC_REQUIRED' );
            $this->assign('required', $required );
    }



}