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

/**
 * Tienda PayPalPro Base Renderer
 *
 * @package		Joomla 
 * @since 		1.5
 */
class plgTiendaPayment_Paypalpro_Renderer extends JObject
{
	/**
	 * @var string
	 */
	var $_plugin_type;
	
	/**
	 * @var object
	 */
	var $_params;
	
	
	/**
	 * Class constructor
	 * 
	 * @param object $params
	 * @param string $plugin_type
	 * @return void
	 * @access public
	 */
	function plgTiendaPayment_Paypalpro_Renderer(&$params, $plugin_type)
	{
		$this->setParams($params);
		$this->setPluginType($plugin_type);
	}

	/**
	 * Gets the parsed layout file
	 * 
	 * @param string $layout The name of  the layout file
	 * @param object $vars Variables to assign to
	 * @param string $plugin The name of the plugin
	 * @param string $group The plugin's group
	 * @return string
	 * @access protected
	 */
	function _getLayout($layout, $vars = false, $plugin = '', $group = 'tienda')
	{
		if ( ! $plugin) {
			$plugin = $this->_plugin_type;
		}
		
		ob_start();
        $layout = $this->_getLayoutPath( $plugin, $group, $layout ); 
        include($layout);
        $html = ob_get_contents(); 
        ob_end_clean();
		
		return $html;
	}
		
    /**
     * Get the path to a layout file
     *
     * @param   string  $plugin The name of the plugin file
     * @param   string  $group The plugin's group
     * @param   string  $layout The name of the plugin layout file
     * @return  string  The path to the plugin layout file
     * @access protected
     */
    function _getLayoutPath($plugin, $group, $layout = 'default')
    {
        $app = JFactory::getApplication();

        // get the template and default paths for the layout
        $templatePath = JPATH_BASE.'/templates/'.$app->getTemplate().'/html/plugins/'.$group.DS.$plugin.DS.$layout.'.php';
        $defaultPath = JPATH_BASE.'/plugins/'.$group.DS.$plugin.'/tmpl/'.$layout.'.php';

        // if the site template has a layout override, use it
        jimport('joomla.filesystem.file');
        if (JFile::exists( $templatePath )) {
            return $templatePath;
        } 
        else {
            return $defaultPath;
        }
    }
    
    /**
     * Public setter for $_params
     * 
     * @param object $params
     * @return void
     * @access public
     */
    function setParams(&$params)
    {
    	$this->_params =& $params;
    }
    
	/**
     * Public setter for $_plugin_type
     * 
     * @param string $params
     * @return void
     * @access public
     */
    function setPluginType($plugin_type)
    {
    	$this->_plugin_type = $plugin_type;
    }    
}

if ( ! function_exists('plg_tienda_escape')) {
	
	/**
	 * Escapes a value for output in a view script
	 * 
	 * @param mixed $var
	 * @return mixed
	 */
	function plg_tienda_escape($var)
	{
		return htmlspecialchars($var, ENT_COMPAT, 'UTF-8');
	}
}
