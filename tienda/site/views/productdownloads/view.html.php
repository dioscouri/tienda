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

JLoader::import( 'com_tienda.views._base', JPATH_SITE.DS.'components' );

class TiendaViewProductDownloads extends TiendaViewBase  
{
	/**
	 * 
	 * @param $tpl
	 * @return unknown_type
	 */
	function display($tpl=null) 
	{
		parent::display($tpl);
  }

  /*
   * Generates select field with all products the user has ever bought and have downloable files
   */
  function productSelect()
  {
  	$db = &JFactory::getDbo();
  	$model = $this->getModel();
  	$q = new TiendaQuery();
  	$state = $model->getState();

  	$q->from( '#__tienda_productdownloads AS tbl' );
  	$q->join( 'left', '#__tienda_products tbl_products ON tbl_products.product_id = tbl.product_id ' );
  	$q->select( 'tbl_products.product_name, tbl.product_id' );
  	$q->where( 'tbl_products.product_enabled = 1' );
  	$q->group( 'tbl_products.product_id' );
  	$q->order( 'tbl_products.product_id ASC' );
  	$db->setQuery($q);
  	$products = $db->loadObjectList();
  	$options = array();
  	$options[] = JHTML::_( 'select.option', 0, JText::_( 'Select Product' ) );
  	if( count($products) )
  	{
	  	foreach($products as $product)  	
				$options[] = JHTML::_('select.option',$product->product_id, $product->product_name );
  	}
  	return JHTML::_('select.genericlist',$options, 'filter_product_id', 'onchange="this.form.submit();"','value','text',@$state->filter_product_id);
  }
}