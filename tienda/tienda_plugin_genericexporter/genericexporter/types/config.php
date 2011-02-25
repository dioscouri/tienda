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

//JLoader::register( "TiendaGenericExporterBase", JPATH_SITE.DS."plugins".DS."tienda".DS."genericexporter"."types"."_base.php" );
Tienda::load( 'TiendaGenericExporterBase', 'genericexporter.types._base',  array( 'site'=>'site', 'type'=>'plugins', 'ext'=>'tienda' ));

class TiendaGenericExporterConfig extends TiendaGenericExporterBase
{
	public $_model = 'config';
}
