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

Tienda::load( 'TiendaViewBase', 'views._base' );

class TiendaViewTaxrates extends TiendaViewBase 
{
	/*
	 * Gets names of tax rates at the same level
	 * 
	 * @params $level						level of taxes
	 * @params $geozone_id 			ID of a geozone (null means all)
	 * @params $tax_class_id 		ID of a tax class (null means all)
	 * @params $tax_type				for the future use
	 * @params $update					update cached info
	 * 
	 * @return Array with names of tax rates at the same level
	 */
	function getAssociatedTaxRates( $level, $geozone_id = null, $tax_class_id = null, $tax_type = null, $update = false )
	{
		static $taxrates = null; // static array for caching results
		if( $taxrates === null )
			$taxrates = array();
			
		if( !$geozone_id )
			$geozone_id = -1;
		if( !$tax_class_id )
			$tax_class_id = -1;
		
		if( isset( $taxrates[$tax_class_id][$geozone_id][$level] ) && !$update )
			return $taxrates[$tax_class_id][$geozone_id][$level];

		$res = $this->getModel()->getTaxRatesAtLevel( ( int )$level, $geozone_id, $tax_class_id, $tax_type, $update );

		$result = array();
		for( $i = 0, $c = count( $res ); $i < $c; $i++ )
			$result []= $res[$i]->tax_rate_description;
		
		$taxrates[$tax_class_id][$geozone_id][$level] = $result;
		return $taxrates[$tax_class_id][$geozone_id][$level];
	}

	/*
	 * Generate list of levels in taxes
	 * 
	 * @param $selected				Selected tax rate level
	 * @param $taxrate_id			Taxrate ID
	 * @param $tax_class_id		Tax class ID
	 * 
	 * @return HTML of a select with list of levels of taxes
	 */
	function listRateLevels( $selected, $taxrate_id, $tax_class_id )
	{
		$list = array();
		Tienda::load( 'TiendaQuery', 'library.query' );
		$q = new TiendaQuery();
		$db = JFactory::getDbo();
		$q->select( 'max( level ) as `max_level`, min( level ) as `min_level`' );
		$q->from( '#__tienda_taxrates' );
		$q->where( 'tax_class_id = '.$tax_class_id );
		$db->setQuery( $q );
		$levels = $db->loadObject();
		if( !strlen( $levels->min_level )  )
			$levels->min_level = 0;
		for( $i = $levels->min_level; $i <= $levels->max_level + 1; $i++ )
  		$list[] = JHTML::_('select.option',  $i, 'Level - '.$i );
    return JHTML::_( 'select.genericlist', $list, 'levels['.$taxrate_id.']', array('class' => 'inputbox', 'size' => '1'), 'value', 'text', $selected );
	}
}
