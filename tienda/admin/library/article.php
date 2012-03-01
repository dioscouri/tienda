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

class TiendaArticle 
{
	/**
	 * Takes a simple product description an formats it like an article
	 * to trick payment plugins into acting on it
	 * 
	 * @param string $text
	 * @return string HTML
	 */
	function fromString( $text )
	{
        $mainframe = JFactory::getApplication();
        $params       =& $mainframe->getParams('com_content');
        
        $dispatcher    =& JDispatcher::getInstance();
        
		$article =& JTable::getInstance('content');
		$article->text = $text;
		
		$limitstart = 0;
		
        /*
         * Process the prepare content plugins
         */
            JPluginHelper::importPlugin('content');
            $results = $dispatcher->trigger('onPrepareContent', array (& $article, & $params, $limitstart));

        /*
         * Handle display events
         */
            $article->event = new stdClass();
            
            // TODO Since there is no title, do we include this event?
            $results = $dispatcher->trigger('onAfterDisplayTitle', array (& $article, &$params, $limitstart));
            $article->event->afterDisplayTitle = trim(implode("\n", $results));
    
            $results = $dispatcher->trigger('onBeforeDisplayContent', array (& $article, & $params, $limitstart));
            $article->event->beforeDisplayContent = trim(implode("\n", $results));
    
            $results = $dispatcher->trigger('onAfterDisplayContent', array (& $article, & $params, $limitstart));
            $article->event->afterDisplayContent = trim(implode("\n", $results));

        // collect $html
        $html = '';
        $html .= $article->event->afterDisplayTitle;
        $html .= $article->event->beforeDisplayContent;
        $html .= $article->text;
        $html .= $article->event->afterDisplayContent;
        
        return $html;
		
	}
	
	/**
	 * 
	 * @return unknown_type
	 */
	function display( $articleid )
	{
		global $mainframe;
		$html = '';
		
		$dispatcher	   =& JDispatcher::getInstance();
			
		$article =& JTable::getInstance('content');
		$article->load( $articleid );
		// Return html if the load fails
		if (!$article->id)
		{
			return $html;
		}
		$article->text = $article->introtext . chr(13).chr(13) . $article->fulltext;

		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
		
		//get the com_content application param
		if($mainframe->isAdmin())
		{
			 jimport( 'joomla.application.component.helper' );
			 $params = JComponentHelper::getParams("com_content");
		}
		else 
		{
			$application = JFactory::getApplication();
			$params		=& $application->getParams('com_content');
		}
		
		$aparams	=& $article->attribs;
		$params->merge($aparams);		
	
		// merge isn't overwriting the global component params, so using this
		$article_params = new JParameter( $article->attribs );

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
			$results = $dispatcher->trigger('onAfterDisplayTitle', array (& $article, &$params, $limitstart));
			$article->event->afterDisplayTitle = trim(implode("\n", $results));
	
			$results = $dispatcher->trigger('onBeforeDisplayContent', array (& $article, & $params, $limitstart));
			$article->event->beforeDisplayContent = trim(implode("\n", $results));
	
			$results = $dispatcher->trigger('onAfterDisplayContent', array (& $article, & $params, $limitstart));
			$article->event->afterDisplayContent = trim(implode("\n", $results));

		// Use param for displaying article title
			$show_title = $article_params->get('show_title', $params->get('show_title') );
			if ($show_title)
			{
				$html .= "<h3>{$article->title}</h3>";	
			}
			$html .= $article->event->afterDisplayTitle;
			$html .= $article->event->beforeDisplayContent;
			$html .= $article->text;
			$html .= $article->event->afterDisplayContent;
		
		return $html;
	}
}