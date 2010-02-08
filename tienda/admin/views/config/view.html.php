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

JLoader::import( 'com_tienda.views._base', JPATH_ADMINISTRATOR.DS.'components' );

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
		JLoader::import( 'com_tienda.library.select', JPATH_ADMINISTRATOR.DS.'components' );
		JLoader::import( 'com_tienda.library.grid', JPATH_ADMINISTRATOR.DS.'components' );
		JLoader::import( 'com_tienda.library.tools', JPATH_ADMINISTRATOR.DS.'components' );

		// check config
			$row = TiendaConfig::getInstance();
			$this->assign( 'row', $row );
		
		// add toolbar buttons
			JToolBarHelper::save('save');
			JToolBarHelper::cancel( 'close', JText::_( 'Close' ) );
			
		// plugins
        	$filtered = array();
	        $items = TiendaHelperTools::getPlugins();
			for ($i=0; $i<count($items); $i++) 
			{
				$item = &$items[$i];
				// Check if they have an event
				if ($hasEvent = TiendaHelperTools::hasEvent( $item, 'onListConfigTienda' )) {
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
			$required->text = JText::_( 'Required' );
			$required->image = "<img src='".JURI::root()."/media/com_tienda/images/required_16.png' alt='{$required->text}'>";
			$this->assign('required', $required );
			
		// Elements
		$elementArticleModel 	= JModel::getInstance( 'ElementArticle', 'TiendaModel' );
			// default
			$elementArticle_default 		= $elementArticleModel->_fetchElement( 'article_default', @$row->get('article_default') );
			$resetArticle_default			= $elementArticleModel->_clearElement( 'article_default', '0' );
			$this->assign('elementArticle_default', $elementArticle_default);
			$this->assign('resetArticle_default', $resetArticle_default);
			// potential
			$elementArticle_potential 		= $elementArticleModel->_fetchElement( 'article_potential', @$row->get('article_potential') );
			$resetArticle_potential			= $elementArticleModel->_clearElement( 'article_potential', '0' );
			$this->assign('elementArticle_potential', $elementArticle_potential);
			$this->assign('resetArticle_potential', $resetArticle_potential);
			// suspended
			$elementArticle_disabled 		= $elementArticleModel->_fetchElement( 'article_disabled', @$row->get('article_disabled') );
			$resetArticle_disabled			= $elementArticleModel->_clearElement( 'article_disabled', '0' );
			$this->assign('elementArticle_disabled', $elementArticle_disabled);
			$this->assign('resetArticle_disabled', $resetArticle_disabled);
			// unapproved
			$elementArticle_unapproved 		= $elementArticleModel->_fetchElement( 'article_unapproved', @$row->get('article_unapproved') );
			$resetArticle_unapproved			= $elementArticleModel->_clearElement( 'article_unapproved', '0' );
			$this->assign('elementArticle_unapproved', $elementArticle_unapproved);
			$this->assign('resetArticle_unapproved', $resetArticle_unapproved);
			// application
			$elementArticle_application 		= $elementArticleModel->_fetchElement( 'article_application', @$row->get('article_application') );
			$resetArticle_application			= $elementArticleModel->_clearElement( 'article_application', '0' );
			$this->assign('elementArticle_application', $elementArticle_application);
			$this->assign('resetArticle_application', $resetArticle_application);
    }
    
}
