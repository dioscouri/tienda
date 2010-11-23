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

JLoader::import( 'com_tienda.views._base', JPATH_SITE.DS.'components' );

class TiendaViewCarts extends TiendaViewBase  
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
                $this->_form($tpl);
              break;
            case "default":
            default:
                $this->_default($tpl);
              break;
        }
    }
    
    /**
	 * Basic commands for displaying a list
	 *
	 * @param $tpl
	 * @return unknown_type
	 */
	function _default($tpl='')
	{
		Tienda::load( 'TiendaSelect', 'library.select' );
		Tienda::load( 'TiendaGrid', 'library.grid' );
		$model = $this->getModel();

		// set the model state
            $state = $model->getState();
            
            JFilterOutput::objectHTMLSafe( $state );
            $this->assign( 'state', $state );

		// page-navigation
			$this->assign( 'pagination', $model->getPagination() );
			
		// list of items
			$this->assign('items', $model->getList());
			
		// form
			$validate = JUtility::getToken();
			$form = array();
			$view = strtolower( JRequest::getVar('view') );
			$form['action'] = "index.php?option=com_tienda&controller={$view}&view={$view}";
			$form['validate'] = "<input type='hidden' name='{$validate}' value='1' />";
			$this->assign( 'form', $form );
    }
    
}