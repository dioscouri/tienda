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
defined( '_JEXEC' ) or die( 'Restricted access' );

Tienda::load('TiendaControllerProducts', 'controllers.products');
Tienda::load('TiendaControllerProtocolJson', 'library.interfaces.protocols');

class TiendaControllerProductsJson extends TiendaControllerProducts implements TiendaControllerProtocolJson
{
	/**
	 * constructor
	 */
	function __construct()
	{
		parent::__construct();
		// Set the format to raw, no matter what
		Tienda::load('TiendaHelperBase', 'helpers._base');
		TiendaHelperBase::setFormat('raw');
	}
	
	/**
	 * Adds a product relationship
	 */
	function removeRelationship()
	{
		$response = array();
		$response['msg'] = '';
		$response['error'] = '';

		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance();

		// get elements from post
		$elements = json_decode( preg_replace('/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );

		// convert elements to array that can be binded
		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance();
		$submitted_values = $helper->elementsToArray( $elements );

		$product_id = $submitted_values['new_relationship_productid_from'];
		$productrelation_id = JRequest::getInt('productrelation_id');

		$table = JTable::getInstance('ProductRelations', 'TiendaTable');
		$table->delete( $productrelation_id );

		$response['error'] = '0';
		$response['msg'] = $this->getRelationshipsHtml( null, $product_id );

		echo ( json_encode( $response ) );
	}
	
	/**
	 *
	 * Adds a product relationship
	 */
	function addRelationship()
	{
		$response = array();
		$response['msg'] = '';
		$response['error'] = '';

		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance();

		// get elements from post
		$elements = json_decode( preg_replace('/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );

		// convert elements to array that can be binded
		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance();
		$submitted_values = $helper->elementsToArray( $elements );

		$product_id = $submitted_values['new_relationship_productid_from'];
		$product_to = $submitted_values['new_relationship_productid_to'];
		$relation_type = $submitted_values['new_relationship_type'];

		// verify product id exists
		$product = JTable::getInstance('Products', 'TiendaTable');
		$product->load( $product_to, true, false );
		if (empty($product->product_id) || $product_id == $product_to)
		{
			$response['error'] = '1';
			$response['msg'] = $helper->generateMessage( JText::_('COM_TIENDA_INVALID_PRODUCT') );
			$response['msg'] .= $this->getRelationshipsHtml( null, $product_id );
			echo ( json_encode( $response ) );
			return;
		}

		// and that relationship doesn't already exist
		$producthelper = TiendaHelperBase::getInstance( 'Product' );
		if ($producthelper->relationshipExists( $product_id, $product_to, $relation_type ))
		{
			$response['error'] = '1';
			$response['msg'] = $helper->generateMessage( JText::_('COM_TIENDA_RELATIONSHIP_ALREADY_EXISTS') );
			$response['msg'] .= $this->getRelationshipsHtml( null, $product_id );
			echo ( json_encode( $response ) );
			return;
		}

		switch ($relation_type)
		{
			case "child":
			case "required_by":
				// for these two, we must flip to/from
				switch ($relation_type)
				{
					case "child":
						$rtype = 'parent';
						break;
					case "required_by":
						$rtype = 'requires';
						break;
				}

				// check existence of required_by relationship
				if ($producthelper->relationshipExists( $product_to, $product_id, $rtype ))
				{
					$response['error'] = '1';
					$response['msg'] = $helper->generateMessage( JText::_('COM_TIENDA_RELATIONSHIP_ALREADY_EXISTS') );
					$response['msg'] .= $this->getRelationshipsHtml( null, $product_id );
					echo ( json_encode( $response ) );
					return;
				}

				// then add it, need to flip to/from
				$table = JTable::getInstance('ProductRelations', 'TiendaTable');
				$table->product_id_from = $product_to;
				$table->product_id_to = $product_id;
				$table->relation_type = $rtype;
				$table->save();
				break;
			default:
				$table = JTable::getInstance('ProductRelations', 'TiendaTable');
				$table->product_id_from = $product_id;
				$table->product_id_to = $product_to;
				$table->relation_type = $relation_type;
				$table->save();
				break;
		}

		$response['error'] = '0';
		$response['msg'] = $this->getRelationshipsHtml( null,  $product_id );

		echo ( json_encode( $response ) );
		return;
	}
	
	/**
	 * Change the default image
	 * @return unknown_type
	 */
	function updateDefaultImage()
	{
		$response = array();
		$response['default_image'] = '';
		$response['default_image_name'] = '';

		$product_id = JRequest::getInt('product_id');
		Tienda::load( 'TiendaUrl', 'library.url' );
		Tienda::load( "TiendaHelperProduct", 'helpers.product' );

		$row = JTable::getInstance('Products', 'TiendaTable');
		$row->load($product_id);

		$response['default_image'] = TiendaHelperProduct::getImage($row->product_id, 'id', $row->product_name, 'full', false, false, array( 'height'=>80 ) );
		$response['default_image_name'] = $row->product_full_image;

		echo ( json_encode( $response ) );
		return;
	}
	
	/**
	 * Get a json list of products
	 * @api
	 */
	function getList()
	{
		$response = array();
		
		$model 	= $this->getModel( $this->get('suffix') );
		parent::_setModelState();
		$products = $model->getList();
		
		echo json_encode($products);
		
		return;
	}

}

?>