<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaPluginBase', 'library.plugins._base' );

class plgTiendaIDevAffiliate extends TiendaPluginBase
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'idevaffiliate';
       
	function plgTiendaIDevAffiliate(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );	
	}

	function onDisplayProductFormIntegrations($row) 
	{
		if(@$row->product_id)
		{
			$vars = new JObject();			
			$vars->row = $row;			
			$vars->fields = $fields;
			$html = $this->_getLayout( 'form', $vars );
			echo $html;			
		}
	}
	
	function onDisplayConfigFormSliders($item, $row) 
	{
		
	}
}
