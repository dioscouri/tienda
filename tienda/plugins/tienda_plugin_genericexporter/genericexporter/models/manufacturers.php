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

Tienda::load( 'TiendaGenericExporterModelBase', 'genericexporter.models._base',  array( 'site'=>'site', 'type'=>'plugins', 'ext'=>'tienda' ));

class TiendaGenericExporterModelManufacturers extends TiendaGenericExporterModelBase
{
	public $_model = 'manufacturers';
	public $_modelone = 'manufacturer';
}
