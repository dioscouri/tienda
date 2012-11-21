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

class TiendaViewConfig extends TiendaViewBase 
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
			case "default":
			default:
			    $this->set( 'leftMenu', 'leftmenu_configuration' );
				$this->_default($tpl);
			  break;
		}
	}
	
	/**
	 * 
	 * @return void
	 **/
	function _default($tpl = null) 
	{
		Tienda::load( 'TiendaSelect', 'library.select' );
		Tienda::load( 'TiendaGrid', 'library.grid' );
		Tienda::load( 'TiendaTools', 'library.tools' );

		// check config
			$row = Tienda::getInstance();
			$this->assign( 'row', $row );
		
		// add toolbar buttons
			JToolBarHelper::apply('save');
			JToolBarHelper::cancel( 'close', 'COM_TIENDA_CLOSE' );
			
		// plugins
        	$filtered = array();
	        $items = TiendaTools::getPlugins();
			for ($i=0; $i<count($items); $i++) 
			{
				$item = &$items[$i];
				// Check if they have an event
				if ($hasEvent = TiendaTools::hasEvent( $item, 'onListConfigTienda' )) {
					// add item to filtered array
					$filtered[] = $item;
				}
			}
			$items = $filtered;
			$this->assign( 'items_sliders', $items );
			
		// Add pane
			jimport('joomla.html.pane');
			$sliders = JPane::getInstance( 'sliders' );
			$this->assign('sliders', $sliders);
			
		// form
			$validate = JUtility::getToken();
			$form = array();
			$view = strtolower( JRequest::getVar('view') );
			$form['action'] = "index.php?option=com_tienda&controller={$view}&view={$view}";
			$form['validate'] = "<input type='hidden' name='{$validate}' value='1' />";
			$this->assign( 'form', $form );
			
		// set the required image
		// TODO Fix this to use defines
			$required = new stdClass();
			$required->text = JText::_('COM_TIENDA_REQUIRED');
			$required->image = "<img src='".JURI::root()."/media/com_tienda/images/required_16.png' alt='{$required->text}'>";
			$this->assign('required', $required );
			
		// Elements
		$elementArticleModel 	= JModel::getInstance( 'ElementArticle', 'TiendaModel' );
		$this->assign( 'elementArticleModel', $elementArticleModel );
		
			// terms
			$elementArticle_terms 		= $elementArticleModel->fetchElement( 'article_terms', @$row->get('article_terms') );
			$resetArticle_terms			= $elementArticleModel->clearElement( 'article_terms', '0' );
			$this->assign('elementArticle_terms', $elementArticle_terms);
			$this->assign('resetArticle_terms', $resetArticle_terms);
            // shipping
            $elementArticle_shipping       = $elementArticleModel->fetchElement( 'article_shipping', @$row->get('article_shipping') );
            $resetArticle_shipping         = $elementArticleModel->clearElement( 'article_shipping', '0' );
            $this->assign('elementArticle_shipping', $elementArticle_shipping);
            $this->assign('resetArticle_shipping', $resetArticle_shipping);			
			

    }
    
}
