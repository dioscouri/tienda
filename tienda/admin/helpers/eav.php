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

if ( !class_exists('Tienda') ) 
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );

Tienda::load( "TiendaHelperBase", 'helpers._base' );

class TiendaHelperEav extends TiendaHelperBase 
{
	/**
	 * Get the Eav Attributes for a particular entity
	 * @param unknown_type $entity
	 * @param unknown_type $id
	 */
    function getAttributes( $entity, $id )
    {
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
    	$model = JModel::getInstance('EavAttributes', 'TiendaModel');
    	$model->setState('filter_entitytype', $entity);
    	$model->setState('filter_entityid', $id);
    	$model->setState('filter_published', '1');
    	
    	$eavs = $model->getList();
    	
    	return $eavs;
    }
}