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

require_once dirname(__FILE__) . '/../renderer.php';

/**
 * Tienda PayPalPro Helper Renderer
 * 
 * Contains some useful methods for all other renderers
 *
 * @package		Joomla 
 * @since 		1.5
 */
class plgTiendaPayment_Paypalpro_Renderer_Helper extends plgTiendaPayment_Paypalpro_Renderer
{
	/**
	 * Public wrapper for the corresponding protected method
	 * 
	 * @param string $layout The name of  the layout file
	 * @param object $vars Variables to assign to
	 * @param string $plugin The name of the plugin
	 * @param string $group The plugin's group
	 * @return string
	 * @access public
	 */
	function getLayout($layout, $vars = false, $plugin = '', $group = 'tienda')
	{
		return $this->_getLayout($layout, $vars, $plugin, $group);
	}
	
	/**
	 * Wraps the given text in the HTML
	 *
	 * @param string $text
	 * @return string
	 * @access public
	 */
	function renderHtml($message = '')
	{
		$vars = new JObject();
		$vars->message = $message;
		
        $html = $this->_getLayout('message', $vars);
        
        return $html;
	}
	
	/**
	 * Renders an article
	 *
	 * @return html
	 * @access public
	 */
	function displayArticle()
	{
		$html = '';

		$articleid = $this->_params->get('articleid');
		if ($articleid)
		{
			global $mainframe;
			$dispatcher	   = JDispatcher::getInstance();

			$article = DSCTable::getInstance('content');
			$article->load( $articleid );
			$article->text = $article->introtext . chr(13).chr(13) . $article->fulltext;

			$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
			$params		=& $mainframe->getParams('com_content');
			$aparams	=& $article->parameters;
			$params->merge($aparams);

			// Fire Content plugins on the article so they change their tags
			/*
			 * Process the prepare content plugins
			 */
				JPluginHelper::importPlugin('content');
				$results = $dispatcher->trigger('onPrepareContent', array (& $article, & $params, $limitstart));

			/*
			 * Handle display events
			 */
				$article->event = new stdClass();
				$results = $dispatcher->trigger('onAfterDisplayTitle', array ($article, &$params, $limitstart));
				$article->event->afterDisplayTitle = trim(implode("\n", $results));

				$results = $dispatcher->trigger('onBeforeDisplayContent', array (& $article, & $params, $limitstart));
				$article->event->beforeDisplayContent = trim(implode("\n", $results));

				$results = $dispatcher->trigger('onAfterDisplayContent', array (& $article, & $params, $limitstart));
				$article->event->afterDisplayContent = trim(implode("\n", $results));

			// Use param for displaying article title
				if ($this->_params->get( 'display_article_title', '0' )) {
					$html .= "<h3>{$article->title}</h3>";
				}
				$html .= $article->event->afterDisplayTitle;
				$html .= $article->event->beforeDisplayContent;
				$html .= $article->text;
				$html .= $article->event->afterDisplayContent;
		}

		return $html;
	}
	
}
