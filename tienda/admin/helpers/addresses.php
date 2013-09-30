<?php
/**
 * @version 1.5
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaHelperBase', 'helpers._base' );

class TiendaHelperAddresses extends TiendaHelperBase
{
    /**
    * Gets data about which address fields should be visible and validable on a form
    *
    * @params $address_type	Address type
    *
    * @return 2-dimensional associative array with data
    */
    public static function getAddressElementsData( $address_type )
    {
        $config = Tienda::getInstance();
        $address_fields = array( 'address_name', 'title', 'name', 'middle',
                'last', 'address1', 'address2', 'country', 'city',
                'zip', 'zone', 'phone', 'company', 'tax_number' );
        $elements = array();
        for( $i = 0, $c = count( $address_fields ); $i < $c; $i++ )
        {
            $f = $address_fields[$i];
            $show = $config->get('show_field_'.$f, '3');
            $valid = $config->get('validate_field_'.$f, '3');
            $elements[ $f ] = array(
                    $show == '3' || $show == $address_type,
                    $valid == '3' || $valid == $address_type,
            );
        }
        return $elements;
    }
}