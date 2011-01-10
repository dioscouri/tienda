<?php
/**
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

/** Import library dependencies */
jimport('joomla.plugin.plugin');

class plgButtonTienda extends JPlugin
{
    /**
     * @var $_element  string  Should always correspond with the plugin's filename,
     *                         forcing it to be unique
     */
    var $_element    = 'tienda';
    
    
    function plgButtonTienda(& $subject, $config)
    {
        parent::__construct($subject, $config);
    }

    /**
     * Fired whenever a Joomla WYSIWYG Editor is displayed
     * @param $name
     * @return unknown_type
     */
    function onDisplay($name)
    {
        $doc =& JFactory::getDocument();
        $doc->addStyleSheet( JURI::root(true).'/plugins/editors-xtd/tienda/css/stylesheet.css');

        $js = "
		function jSelectProducts(id, title, object) {
			jInsertEditorText('{tiendaproduct id='+id+'}', '".$name."');
			document.getElementById('sbox-window').close();
		}";
		
		$doc->addScriptDeclaration($js);
        
        $form_id = JRequest::getVar('id');
        $getContent = $this->_subject->getContent($name);   
        $link = 'index.php?option=com_tienda&amp;task=elementproduct&amp;tmpl=component&amp;e_name='.$name;        
        JHTML::_('behavior.modal');

        $button = new JObject();
        $button->set('modal', true);
        $button->set('link', $link);
        $button->set('text', JText::_('Tienda Product'));
        $button->set('name', 'tiendaproduct');
        $button->set('options', "{handler: 'iframe', size: {x: 800, y: 500}}");
        return $button;
    }
}
