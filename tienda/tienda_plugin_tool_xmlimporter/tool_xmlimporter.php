<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Daniele Rosario
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

Tienda::load( 'TiendaToolPlugin', 'library.plugins.tool' );

class plgTiendaTool_XmlImporter extends TiendaToolPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
	var $_element = 'tool_xmlimporter';
	
	/**
	 * The xml file 
	 * @var unknown_type
	 */
	var $_uploaded_file = '';
	
	/**
	 * The zip file 
	 * @var unknown_type
	 */
	var $_uploaded_images_zip_file = '';
	
	/**
	 * The zip file 
	 * @var unknown_type
	 */
	var $_uploaded_files_zip_file = '';
	
	var $_temp_dir = '';
	
	function plgTiendaTool_XmlImporter( &$subject, $config )
	{
		parent::__construct( $subject, $config );
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
		$this->_temp_dir = JFactory::getConfig( )->get( 'tmp_path', JPATH_SITE . DS . 'tmp' ) . DS . 'tienda_xml_import' . DS;
	}
	
	/**
	 * Overriding 
	 * 
	 * @param $options
	 * @return unknown_type
	 */
	function onGetToolView( $row )
	{
		if ( !$this->_isMe( $row ) )
		{
			return null;
		}
		
		// go to a "process suffix" method
		// which will first validate data submitted,
		// and if OK, will return the html?
		$suffix = $this->_getTokenSuffix( );
		$html = $this->_processSuffix( $suffix );
		
		return $html;
	}
	
	/**
	 * Validates the data submitted based on the suffix provided
	 * 
	 * @param $suffix
	 * @return html
	 */
	function _processSuffix( $suffix = '' )
	{
		$html = "";
		
		switch ( $suffix )
		{
			case "2":
				if ( !$verify = $this->_verifyDB( ) )
				{
					JError::raiseNotice( '_verifyDB', $this->getError( ) );
					$html .= $this->_renderForm( '1' );
				}
				else
				{
					// migrate the data and output the results
					$html .= $this->_doMigration( $verify );
				}
				break;
			case "1":
				if ( !$verify = $this->_verifyDB( ) )
				{
					JError::raiseNotice( '_verifyDB', $this->getError( ) );
					$html .= $this->_renderForm( '1' );
				}
				else
				{
					$suffix++;
					
					$vars = new JObject( );
					$vars->preview = $verify;
					$vars->state = $this->_getState( );
					$vars->state->uploaded_file = $this->_uploaded_file;
					$vars->state->uploaded_images_zip_file = $this->_uploaded_images_zip_file;
					$vars->state->uploaded_files_zip_file = $this->_uploaded_files_zip_file;
					$vars->setError( $this->getError( ) );
					
					// display a 'connection verified' message
					// and request confirmation before migrating data
					$html .= $this->_renderForm( $suffix, $vars );
					
					$html .= $this->_renderView( $suffix, $vars );
				}
				break;
			default:
				$html .= $this->_renderForm( '1' );
				break;
		}
		
		return $html;
	}
	
	/**
	 * Prepares the 'view' tmpl layout
	 *  
	 * @return unknown_type
	 */
	function _renderView( $suffix = '', $vars = 0 )
	{
		if ( !$vars )
		{
			$vars = new JObject( );
		}
		$layout = 'view_' . $suffix;
		$html = $this->_getLayout( $layout, $vars );
		
		return $html;
	}
	
	/**
	 * Prepares variables for the form
	 * 
	 * @return unknown_type
	 */
	function _renderForm( $suffix = '', $vars = 0 )
	{
		if ( !$vars )
		{
			$vars = new JObject( );
			$vars->state = $this->_getState( );
		}
		$vars->token = $this->_getToken( $suffix );
		
		$layout = 'form_' . $suffix;
		$html = $this->_getLayout( $layout, $vars );
		
		return $html;
	}
	
	/*
	 * Verifies the CSV file (our DB in this case)
	 */
	function _verifyDB( )
	{
		$state = $this->_getState( );
		
		// Uploads the file
		Tienda::load( 'TiendaFile', 'library.file' );
		$upload = new TiendaFile( );
		
		$zip_images_upload = new TiendaFile( );
		$zip_files_upload = new TiendaFile( );
		
		// we have to upload the file
		if ( @$state->uploaded_file == '' )
		{
			// handle upload creates upload object properties
			$success = $upload->handleUpload( 'file' );
			
			$zip_images_success = $zip_images_upload->handleUpload( 'images_zip_file' );
			$zip_files_success = $zip_files_upload->handleUpload( 'files_zip_file' );
			
			if ( $success )
			{
				if ( strtolower( $upload->getExtension( ) ) != 'xml' )
				{
					$this->setError( JText::_( 'This is not an XML file' ) );
					return false;
				}
				
				// Move the file to let us reuse it 
				$upload->setDirectory( $this->_temp_dir );
				$success = $upload->upload( );
				
				if ( !$success )
				{
					$this->setError( $upload->getError( ) );
					return false;
				}
				
				$upload->file_path = $upload->getFullPath( );
				
				// Now for the zips
				// Check if it's a supported archive
				
				if ( $zip_images_success )
				{
					
					$allowed_archives = array(
						'zip', 'tar', 'tgz', 'gz', 'gzip', 'tbz2', 'bz2', 'bzip2'
					);
					
					if ( !in_array( strtolower( $zip_images_upload->getExtension( ) ), $allowed_archives ) )
					{
						$this->setError( JText::_( 'This is not a supported archive file' ) );
						return false;
					}
					
					// Move the file to let us reuse it 
					$zip_images_upload->setDirectory( $this->_temp_dir );
					$success = $zip_images_upload->upload( );
					
					if ( !$success )
					{
						$this->setError( $zip_images_upload->getError( ) );
						return false;
					}
					
					$zip_images_upload->file_path = $zip_images_upload->getFullPath( );
				}
				
				if ( $zip_files_success )
				{
					
					$allowed_archives = array(
						'zip', 'tar', 'tgz', 'gz', 'gzip', 'tbz2', 'bz2', 'bzip2'
					);
					
					if ( !in_array( strtolower( $zip_files_upload->getExtension( ) ), $allowed_archives ) )
					{
						$this->setError( JText::_( 'This is not a supported archive file' ) );
						return false;
					}
					
					// Move the file to let us reuse it 
					$zip_files_upload->setDirectory( $this->_temp_dir );
					$success = $zip_files_upload->upload( );
					
					if ( !$success )
					{
						$this->setError( $zip_files_upload->getError( ) );
						return false;
					}
					
					$zip_files_upload->file_path = $zip_files_upload->getFullPath( );
				}
			}
			else
			{
				$this->setError( JText::_( 'Could Not Upload XML File: ' . $upload->getError( ) ) );
				return false;
			}
		}
		// File already uploaded
		else
		{
			$upload->full_path = $upload->file_path = @$state->uploaded_file;
			$upload->proper_name = TiendaFile::getProperName( @$state->uploaded_file );
			if ( @$state->uploaded_images_zip_file )
			{
				$zip_images_upload->full_path = $zip_images_upload->file_path = @$state->uploaded_images_zip_file;
				$zip_images_upload->proper_name = TiendaFile::getProperName( @$state->uploaded_images_zip_file );
			}
			if ( @$state->uploaded_files_zip_file )
			{
				$zip_files_upload->full_path = $zip_files_upload->file_path = @$state->uploaded_files_zip_file;
				$zip_files_upload->proper_name = TiendaFile::getProperName( @$state->uploaded_files_zip_file );
			}
			$success = true;
		}
		
		if ( $success )
		{
			// Get the file content
			$upload->fileToText( );
			$content = $upload->fileastext;
			
			// Set the uploaded file as the file to use during the real import
			$this->_uploaded_file = $upload->getFullPath( );
			$this->_uploaded_images_zip_file = @$zip_images_upload->getFullPath( );
			$this->_uploaded_files_zip_file = @$zip_files_upload->getFullPath( );
			
			$xml = simplexml_load_string( $content );
			
			$products = $xml->children( );
			
			if ( !count( $products ) )
			{
				$this->setError( 'No Products in this file' );
				return false;
			}
			
			return $products;
			
		}
		else
		{
			$this->setError( JText::_( 'Could Not Upload CSV File: ' . $upload->getError( ) ) );
			return false;
		}
		
		return false;
	}
	
	/**
	 * Gets the appropriate values from the request
	 * 
	 * @return JObject
	 */
	function _getState( )
	{
		$state = new JObject( );
		$state->file = '';
		$state->uploaded_file = '';
		$state->uploaded_images_zip_file = '';
		$state->uploaded_files_zip_file = '';
		
		foreach ( $state->getProperties( ) as $key => $value )
		{
			$new_value = JRequest::getVar( $key );
			$value_exists = array_key_exists( $key, $_POST );
			if ( $value_exists && !empty( $key ) )
			{
				$state->$key = $new_value;
			}
		}
		return $state;
	}
	
	/**
	 * Perform the data migration
	 * 
	 * @return html
	 */
	function _doMigration( $data )
	{
		$html = "";
		$vars = new JObject( );
		
		// perform the data migration
		// grab all the data and insert it into the tienda tables
		$state = $this->_getState( );
		
		// Insert the data in the fields
		$results = $this->_migrate( $data );
		
		$vars->results = $results;
		
		$suffix = $this->_getTokenSuffix( );
		$suffix++;
		$layout = 'view_' . $suffix;
		
		$html = $this->_getLayout( $layout, $vars );
		return $html;
	}
	
	private function _migrateProduct( $data )
	{
		// Check for product_name.
		if ( !empty( $data->product_name ) )
		{
			$isNew = false;
			
			if ( !empty( $data->product_id ) )
			{
				if ( ( int ) $data->product_id )
				{
					$data['product_id'] = 0;
					$isNew = true;
				}
			}
			
			JTable::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables' );
			$product = JTable::getInstance( 'Products', 'TiendaTable' );
			
			if ( !$isNew )
			{
				if ( !$product->load( $data['product_id'] ) )
				{
					$isNew = true;
					$data['product_id'] = 0;
				}
			}
			
			// If is a new product, use product->create()
			if ( $isNew )
			{
				$product->product_price = 0;
				$product->product_quantity = 0;
				$product->bind( $this->simpleXml2Array( $data ) );
				
				if ( $product->product_full_image )
				{
					Tienda::load( 'TiendaFile', 'library.file' );
					// Do the same cleaning to the image title that the image helper does
					$name = explode( '.', $product->product_full_image );
					$name = TiendaFile::cleanTitle( $name[0] ) . '.' . $name[count( $name ) - 1];
					
					$product->product_full_image = $name;
				}
				
				$product->create( );
				
				
				
				$this->_migrateAttributes( $product->product_id, $data->product_attributes );
				$this->_migrateCategories( $product->product_id, $data->product_categories );
				$this->_migrateFiles( $product->product_id, $data->product_files );
				$this->_migrateImages( $product->product_id, $data->product_images );
				$this->_migrateChildren( $product->product_id, $data->product_children );
				$this->_migrateRelated( $product->product_id, $data->related_products );
				$this->_migrateCustomFields( $product->product_id, $data->product_custom_fields );
			}
			// else use the save() method
			else
			{
				$product->bind( $this->simpleXml2Array( $data ) );
				
				//check if normal price exists
				Tienda::load( "TiendaHelperProduct", 'helpers.product' );
				$prices = TiendaHelperProduct::getPrices( $product->product_id );
				$quantities = TiendaHelperProduct::getProductQuantities( $product->product_id );
				
				if ( $product->save( ) )
				{
					$product->product_id = $product->id;
					
					// New price?
					if ( empty( $prices ) )
					{
						// set price if new or no prices set
						$price = JTable::getInstance( 'Productprices', 'TiendaTable' );
						$price->product_id = $product->id;
						$price->product_price = ( string ) $data->product_price;
						$price->group_id = TiendaConfig::getInstance( )->get( 'default_user_group', '1' );
						$price->save( );
					}
					// Overwrite price
					else
					{
						// set price if new or no prices set
						$price = JTable::getInstance( 'Productprices', 'TiendaTable' );
						$price->load( $prices[0]->product_price_id );
						$price->product_price = ( string ) $data->product_price;
						$price->group_id = TiendaConfig::getInstance( )->get( 'default_user_group', '1' );
						$price->save( );
					}
					
					// New quantity?
					if ( empty( $quantities ) )
					{
						// save default quantity
						$quantity = JTable::getInstance( 'Productquantities', 'TiendaTable' );
						$quantity->product_id = $product->id;
						$quantity->quantity = ( string ) $data->product_quantity;
						$quantity->save( );
					}
					// Overwrite Quantity
					else
					{
						// save default quantity
						$quantity = JTable::getInstance( 'Productquantities', 'TiendaTable' );
						$quantity->load( $quantities[0]->productquantity_id );
						$quantity->product_id = $product->id;
						$quantity->quantity = ( string ) $data->product_quantity;
						$quantity->save( );
					}
					
				}
				
			}
			return $product;
			
		}
		return null;
	}
	
	/**
	 * Do the migration
	 * 
	 * @return array
	 */
	function _migrate( $datas )
	{
		$queries = array( );
		
		$results = array( );
		$n = 0;
		
		// Explode the archives
		if ( JFile::exists( $this->_uploaded_images_zip_file ) )
		{
			$zip = $this->_uploaded_images_zip_file;
			$dir = $this->_temp_dir . DS . 'images' . DS;
			
			jimport( 'joomla.filesystem.archive' );
			JArchive::extract( $zip, $dir );
			JFile::delete( $zip );
		}
		if ( JFile::exists( $this->_uploaded_files_zip_file ) )
		{
			$zip = $this->_uploaded_files_zip_file;
			$dir = $this->_temp_dir . DS . 'files' . DS;
			
			jimport( 'joomla.filesystem.archive' );
			JArchive::extract( $zip, $dir );
			JFile::delete( $zip );
		}
		
		// Loop though the rows
		foreach ( $datas as $data )
		{
			
			$product = $this->_migrateProduct( $data );
			
			$results[$n]->title = $product->product_name;
			$results[$n]->query = "";
			$results[$n]->error = implode( '\n', $product->getErrors( ) );
			$results[$n]->affectedRows = 1;
			
			$n++;
			
		}
		
		// Remove created files
		JFolder::delete( $this->_temp_dir );
		
		return $results;
	}
	
	/**
	 * Migrate a single product attributes
	 * 
	 * @param TiendaTableProduct $product
	 * @param array $data
	 */
	private function _migrateAttributes( $product_id, $attributes )
	{
		foreach ( $attributes->children( ) as $attribute )
		{
			// Add the Attribute
			$table = JTable::getInstance( 'ProductAttributes', 'TiendaTable' );
			$table->product_id = $product_id;
			$table->productattribute_name = ( string ) $attribute->productattribute_name;
			$table->save( );
			
			// Add the Options for this attribute
			$id = $table->productattribute_id;
			foreach ( $attribute->productattribute_options->children( ) as $option )
			{
				$otable = JTable::getInstance( 'ProductAttributeOptions', 'TiendaTable' );
				$otable->bind( $this->simpleXml2Array( $option ) );
				$otable->productattribute_id = $id;
				$otable->save( );
			}
		}
	}
	
	/**
	 * Migrate a single product categories
	 * 
	 * @param TiendaTableProduct $product
	 * @param array $data
	 */
	private function _migrateCategories( $product_id, $categories )
	{
		foreach ( $categories->children( ) as $category )
		{
			// load category
			if ( !empty( $category->category_id ) )
			{
				$category_id = ( int ) $category->category_id;
			}
			else
			{
				// check for existance
				JModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models' );
				$model = JModel::getInstance( 'Categories', 'TiendaModel' );
				$model->setState( 'filter_name', ( string ) $category->category_name );
				$matches = $model->getList( );
				$matched = false;
				
				if ( $matches )
				{
					foreach ( $matches as $match )
					{
						// is a perfect match?
						if ( strtolower( ( string ) $category->category_name ) == strtolower( $match->category_name ) )
						{
							$category_id = $match->category_id;
							$matched = true;
						}
					}
				}
				
				// Not matched, create category
				if ( !$matched )
				{
					$tcategory = JTable::getInstance( 'Categories', 'TiendaTable' );
					$tcategory->category_name = ( string ) $category->category_name;
					$tcategory->parent_id = 1;
					$tcategory->category_enabled = 1;
					$tcategory->save( );
					
					$category_id = $tcategory->category_id;
				}
				
			}
			
			// save xref in every case
			$xref = JTable::getInstance( 'ProductCategories', 'TiendaTable' );
			$xref->product_id = $product_id;
			$xref->category_id = $category_id;
			$xref->save( );
		}
	}
	
	/**
	 * Migrate a single product files
	 * 
	 * @param TiendaTableProduct $product
	 * @param array $data
	 */
	private function _migrateFiles( $product_id, $files )
	{
		foreach ( $files->children( ) as $file )
		{
			// Add the File
			$table = JTable::getInstance( 'ProductFiles', 'TiendaTable' );
			$table->bind( $this->simpleXml2Array( $file ) );
			$table->product_id = $product_id;
			
			// Now the files on the zip have to be linked			
			$dest_dir = TiendaHelperProduct::getFilePath( $product_id );
			$source_dir = $this->_temp_dir . DS . 'files' . DS;
			$filename = $table->productfile_name;
			
			$path = $dest_dir . DS . $filename;
			$namebits = explode( '.', $filename );
			$extension = $namebits[count( $namebits ) - 1];
			
			$table->productfile_extension = $extension;
			$table->productfile_path = $path;
			
			// If the files exists & the copy is successfull, save the file
			if ( JFile::exists( $source_dir . $filename ) )
			{
				if ( JFile::copy( $source_dir . $filename, $dest_dir . DS . $filename ) )
				{
					$table->save( );
				}
			}
			
		}
	}
	
	/**
	 * Migrate a single product children
	 * 
	 * @param TiendaTableProduct $product
	 * @param array $data
	 */
	private function _migrateChildren( $product_id, $children )
	{
		if( !$children )
		{
			return;
		}
		
		foreach ( $children->children( ) as $child )
		{
			$product = $this->_migrateProduct( $child );
			
			// Parent-Child Relation
			// Add the Attribute
			$table = JTable::getInstance( 'ProductRelations', 'TiendaTable' );
			$table->product_id_from = $product_id;
			$table->product_id_to = $product->product_id;
			$table->relation_type = 'parent';
			$table->save( );
		}
	}
	
	/**
	 * Migrate a single product relations
	 * 
	 * @param TiendaTableProduct $product
	 * @param array $data
	 */
	private function _migrateRelated( $product_id, $related )
	{
		if( !$related )
		{
			return;
		}
		
		foreach ( $related->children( ) as $r )
		{
			$product = $this->_migrateProduct( $r );
			
			// Relation
			// Add the Attribute
			$table = JTable::getInstance( 'ProductRelations', 'TiendaTable' );
			$table->product_id_from = $product_id;
			$table->product_id_to = $product->product_id;
			$table->relation_type = 'relates';
			$table->save( );
		}
	}
	
	/**
	 * Migrate Custom Fields
	 * 
	 * @param TiendaTableProduct $product
	 * @param array $data
	 */
	private function _migrateCustomFields( $product_id, $fields )
	{
		// Required tables
		Tienda::load( 'TiendaTableEavAttributes', 'tables.eavattributes' );
		Tienda::load( 'TiendaTableEavAttributeEntities', 'tables.eavattributeentities' );
		Tienda::load( 'TiendaTableEavValues', 'tables.eavvalues' );
		
		foreach ( $fields->children( ) as $field )
		{
			$attribute_name = ( string ) $field->field_name;
			$attribute_alias = ( string ) $field->field_alias;
			$attribute_type = strtolower( ( string ) $field->field_type );
			$editable_by = strtolower( $field->field_editableby );
			$enabled = $field->field_enabled;
			$value = ( string ) $field->field_value;
			
			// Check if it already exists
			$keynames = array( );
			$keynames['eavattribute_label'] = $attribute_name;
			$keynames['eaventity_type'] = 'products';
			$keynames['eavattribute_type'] = $attribute_type;
			$keynames['eavattribute_alias'] = $attribute_alias;
			
			switch( $editable_by )
			{
				case "none":
					$keynames['editable_by'] = 0;
					break;
				case "admin":
					$keynames['editable_by'] = 1;
					break;
				case "user":
					$keynames['editable_by'] = 2;
					break;
				default:
					$keynames['editable_by'] = 1;
					break;
			}
			
			if( empty($enabled) || $enabled=='1' )
			{
				$keynames['enabled'] = 1;
			}
			else if( $enabled == '0' )
			{
				$keynames['enabled'] = 0;
			}
			
			$eav = JTable::getInstance( 'EavAttributes', 'TiendaTable' );
			$loaded = $eav->load( $keynames );
			
			if ( !$loaded )
			{
				// save it as new
				$eav->bind( $keynames );
				$eav->save( );
			}
			
			// Link it to a product
			$table = JTable::getInstance( 'EavAttributeEntities', 'TiendaTable' );
			$table->eavattribute_id = $eav->eavattribute_id;
			$table->eaventity_id = $product_id;
			$table->eaventity_type = 'products';
			
			$table->save( );
			
			// Save the value
			// get the value table
			$table = JTable::getInstance( 'EavValues', 'TiendaTable' );
			// set the type based on the attribute
			$table->setType( $attribute_type );
			
			// load the value based on the entity id
			$keynames = array( );
			$keynames['eavattribute_id'] = $eav->eavattribute_id;
			$keynames['eaventity_id'] = $product_id;
			$loaded = $table->load( $keynames );
			
			// Add the value if it's a first time save
			if ( !$loaded )
			{
				$table->eavattribute_id = $eav->eavattribute_id;
				$table->eaventity_id = $product_id;
			}
			
			// Store the value
			$table->eavvalue_value = $value;
			$table->eaventity_type = 'products';
			$stored = $table->store( );
		}
	}
	
	/**
	 * Migrate the images
	 * 
	 * @param int $product_id
	 * @param string $images
	 */
	private function _migrateImages( $product_id, $images )
	{
		Tienda::load( 'TiendaImage', 'library.image' );
		
		foreach ( $images->children( ) as $image )
		{
			$check = false;
			$multiple = false;
			
			$image = ( string ) $image;
			
			if ( JURI::isInternal( $image ) )
			{
				$internal = true;
				$int_image = JPATH_SITE . DS . $image;
				if ( is_dir( $int_image ) )
				{
					$check = JFolder::exists( $int_image );
					$multiple = true;
				}
				else
				{
					$check = JFile::exists( $int_image );
				}
				// Now check the extracted images path
				if ( !$check )
				{
					$dir = $this->_temp_dir . DS . 'images' . DS;
					if ( is_dir( $dir . $image ) )
					{
						$check = JFolder::exists( $dir . $image );
						$multiple = true;
					}
					else
					{
						$check = JFile::exists( $dir . $image );
					}
					
					if ( $check )
					{
						$image = $dir . $image;
					}
				}
				else
				{
					$image = $int_image;
				}
			}
			else
			{
				$internal = false;
				$check = $this->url_exists( $image );
			}
			
			// Add a single image
			if ( !$multiple )
			{
				$images_to_copy = array(
					$image
				);
			}
			else
			{
				// Fetch the images from the folder and add them
				$images_to_copy = Tienda::getClass( "TiendaHelperProduct", 'helpers.product' )->getGalleryImages( $image );
				foreach ( $images_to_copy as &$i )
				{
					$i = $image . DS . $i;
				}
			}
			
			if ( $check )
			{
				foreach ( $images_to_copy as $image_to_copy )
				{
					if ( $internal )
					{
						$img = new TiendaImage( $image_to_copy);
					}
					else
					{
						$tmp_path = JFactory::getApplication( )->getCfg( 'tmp_path' );
						$file = fopen( $image_to_copy, 'r' );
						$file_content = stream_get_contents( $file );
						fclose( $file );
						
						$file = fopen( $tmp_path . DS . $image_to_copy, 'w' );
						
						fwrite( $file, $file_content );
						
						fclose( $file );
						
						$img = new TiendaImage( $tmp_path . DS . $image_to_copy);
					}
					
					Tienda::load( 'TiendaTableProducts', 'tables.products' );
					$product = JTable::getInstance( 'Products', 'TiendaTable' );
					
					$product->load( $product_id );
					$path = $product->getImagePath( );
					$type = $img->getExtension( );
					
					$img->load( );
					$img->setDirectory( $path );
					// Save full Image
					$img->save( $path . $img->getPhysicalName( ) );
					
					// Save Thumb
					Tienda::load( 'TiendaHelperImage', 'helpers.image' );
					$imgHelper = TiendaHelperBase::getInstance( 'Image', 'TiendaHelper' );
					$imgHelper->resizeImage( $img, 'product' );
					
				}
			}
		}
		
	}
	
	/**
	 * Checks if the URL exists
	 * @param string $url
	 */
	private function url_exists( $url )
	{
		$url = str_replace( "http://", "", $url );
		if ( strstr( $url, "/" ) )
		{
			$url = explode( "/", $url, 2 );
			$url[1] = "/" . $url[1];
		}
		else
		{
			$url = array(
				$url, "/"
			);
		}
		
		$fh = fsockopen( $url[0], 80 );
		if ( $fh )
		{
			fputs( $fh, "GET " . $url[1] . " HTTP/1.1\nHost:" . $url[0] . "\n\n" );
			if ( fread( $fh, 22 ) == "HTTP/1.1 404 Not Found" )
			{
				return FALSE;
			}
			else
			{
				return TRUE;
			}
			
		}
		else
		{
			return FALSE;
		}
	}
	
	private function simpleXml2Array( $xml )
	{
		if ( @get_class( $xml ) == 'SimpleXMLElement' )
		{
			$attributes = $xml->attributes( );
			foreach ( $attributes as $k => $v )
			{
				if ( $v ) $a[$k] = ( string ) $v;
			}
			$x = $xml;
			$xml = get_object_vars( $xml );
		}
		if ( is_array( $xml ) )
		{
			if ( count( $xml ) == 0 ) return ( string ) $x;
			// for CDATA
			foreach ( $xml as $key => $value )
			{
				$r[$key] = $this->simplexml2array( $value );
			}
			if ( isset( $a ) ) $r['@'] = $a;
			// Attributes
			return $r;
		}
		return ( string ) $xml;
	}
	
}
