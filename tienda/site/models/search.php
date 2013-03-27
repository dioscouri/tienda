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

Tienda::load( 'TiendaModelProducts', 'models.products' );

class TiendaModelSearch extends TiendaModelProducts
{
	/*
	 * Required the Table of products  it will return products table object
	 *  
	 */	
  	function &getTable($name='products', $prefix='TiendaTable', $options = array())
    {
        if (empty($name)) {
            $name = $this->getName();
        }
        
        DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
        if ($table = $this->_createTable( $name, $prefix, $options ))  {
            return $table;
        }

        JError::raiseError( 0, 'Table ' . $name . ' not supported. File not found.' );
        $null = null;
        return $null;
    }
}
