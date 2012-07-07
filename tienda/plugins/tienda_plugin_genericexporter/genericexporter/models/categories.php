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

if(version_compare(JVERSION,'1.6.0','ge')) {
    // Joomla! 1.6+ code here
Tienda::load( 'TiendaGenericExporterModelBase', 'genericexporter.genericexporter.models._base',  array( 'site'=>'site', 'type'=>'plugins', 'ext'=>'tienda' ));
} else {
    // Joomla! 1.5 code here
Tienda::load( 'TiendaGenericExporterModelBase', 'genericexporter.models._base',  array( 'site'=>'site', 'type'=>'plugins', 'ext'=>'tienda' ));
}

class TiendaGenericExporterModelCategories extends TiendaGenericExporterModelBase
{
	public $_model = 'categories';
	public $_modelone = 'category';
}
