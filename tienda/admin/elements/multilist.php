<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/
 
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();
 
/**
 * Renders a multiple item select element
 *
 */
 
class JElementMultiList extends JElement
{
        /**
        * Element name
        *
        * @access       protected
        * @var          string
        */
        var    $_name = 'MultiList';
 
        function fetchElement($name, $value, &$node, $control_name)
        {
                // Base name of the HTML control.
                $ctrl  = $control_name .'['. $name .']';
 
                // Construct an array of the HTML OPTION statements.
                $options = array ();
                foreach ($node->children() as $option)
                {
                        $val   = $option->attributes('value');
                        $text  = $option->data();
                        $options[] = JHTML::_('select.option', $val, JText::_($text));
                }
 
                // Construct the various argument calls that are supported.
                $attribs       = ' ';
                if ($v = $node->attributes( 'size' )) {
                        $attribs       .= 'size="'.$v.'"';
                }
                if ($v = $node->attributes( 'class' )) {
                        $attribs       .= 'class="'.$v.'"';
                } else {
                        $attribs       .= 'class="inputbox"';
                }
                if ($m = $node->attributes( 'multiple' ))
                {
                        $attribs       .= ' multiple="multiple"';
                        $ctrl          .= '[]';
                }
 
                // Render the HTML SELECT list.
                return JHTML::_('select.genericlist', $options, $ctrl, $attribs, 'value', 'text', $value, $control_name.$name );
        }
}