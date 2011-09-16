<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

/** Import library dependencies */
jimport('joomla.plugin.plugin');

/**
 * plgTiendaCharts_fusioncharts class.
 *
 * @extends JPlugin
 */
class plgTiendaChange_layout extends JPlugin
{

	/*
	 * This method changes the selected product layout
	 * 
	 * @param $product 			TiendaTableProduct object
	 * @param $new_layout		Layout, which is currently selected
	 * 
	 * @return Name of the selected layout (i.e. "view")
	 */
	function onGetLayoutProduct( $product, $new_layout )
	{
		return 'view';
	}

	/*
	 * This method changes the selected category layout
	 * 
	 * @param $category 			TiendaTableCategory object
	 * @param $new_layout		Layout, which is currently selected
	 * 
	 * @return Name of the selected layout (i.e. "default")
	 */
	function onGetLayoutCategory( $category, $new_layout )
	{
		return 'default';
	}
}
