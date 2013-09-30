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

if ( !class_exists('Tienda') ) 
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR."/components/com_tienda/defines.php" );



if(!class_exists('JFakeElementBase')) {
	if(version_compare(JVERSION,'1.6.0','ge')) {
		class JFakeElementBase extends JFormField {
			// This line is required to keep Joomla! 1.6/1.7 from complaining
			public function getInput() {
			}
		}
	} else {
		class JFakeElementBase extends JElement {}
	}
}

class JFakeElementTiendaProduct extends JFakeElementBase
{
var	$_name = 'TiendaProduct';
	
	public function getInput() 
	{
		return JFakeElementTiendaProduct::fetchElement($this->name, $this->value, $this->element, $this->options['control']);
	}
	

	public function fetchElement($name, $value, &$node, $control_name)
	{
	    
		$html = "";
		$doc 		= JFactory::getDocument();
		$fieldName	= $control_name ? $control_name.'['.$name.']' : $name;
		$title = JText::_('COM_TIENDA_SELECT_PRODUCTS');
		if ($value) {
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_tienda/tables');
			$table = JTable::getInstance('Products', 'TiendaTable');
			$table->load($value);
			$title = $table->product_name;
		}
		else
		{
			$title=JText::_('COM_TIENDA_SELECT_A_PRODUCT');
		}

 		$js = "
			Dsc.selectelementproduct = function(id, title, object) {
			document.getElementById(object + '_id').value = id;
			document.getElementById(object + '_name').value = title;";
		if(version_compare(JVERSION,'1.6.0','ge')) {
			$js .= 'window.parent.SqueezeBox.close()';
		}
		else {
			$js .= 'document.getElementById(\'sbox-window\').close()';
		}
	$js.=	"}";
		
		$doc->addScriptDeclaration($js);

		$link = 'index.php?option=com_tienda&controller=elementproduct&view=elementproduct&tmpl=component&object='.$name;

		JHTML::_('behavior.modal', 'a.modal');
		$html = "\n".'<div style="float: left;"><input style="background: #ffffff;" type="text" id="'.$name.'_name" value="'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'" disabled="disabled" /></div>';
		$html .= '<div class="button2-left"><div class="blank"><a class="modal" title="'.JText::_('COM_TIENDA_SELECT_A_PRODUCT').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 800, y: 500}}">'.JText::_('COM_TIENDA_SELECT').'</a></div></div>'."\n";
		$html .= "\n".'<input type="hidden" id="'.$name.'_id" name="'.$fieldName.'" value="'.(int)$value.'" />';
		
		return $html;
	}
	
	
	
}

if(version_compare(JVERSION,'1.6.0','ge')) {
	class JFormFieldTiendaProduct extends JFakeElementTiendaProduct {}
} else {
	class JElementTiendaProduct extends JFakeElementTiendaProduct {}
}


?>