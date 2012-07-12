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
defined('_JEXEC') or die('Restricted access');

Tienda::load('TiendaToolPlugin', 'library.plugins.tool');

class plgTiendaTool_CsvImporter extends TiendaToolPlugin {
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
	var $_element = 'tool_csvimporter';

	/**
	 *
	 * @var $_keys	array	Contains the columns names
	 */
	var $_keys = array();

	var $_uploaded_file = '';

	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		$language = JFactory::getLanguage();
		$language -> load('plg_tienda_' . $this -> _element, JPATH_ADMINISTRATOR, 'en-GB', true);
		$language -> load('plg_tienda_' . $this -> _element, JPATH_ADMINISTRATOR, null, true);

		$this -> _keys = array('product_id', 'product_name', 'product_categories', 'manufacturer_id', 'product_description_short', 'product_description', 'product_full_image', 'product_images', 'product_ships', 'product_height', 'product_width', 'product_length', 'product_weight', 'product_price', 'product_quantity', 'product_attributes', 'product_sku', 'product_model', 'product_listprice', 'product_listprice_enabled', );
	}

	/**
	 * Overriding
	 *
	 * @param $options
	 * @return unknown_type
	 */
	function onGetToolView($row) {
		if (!$this -> _isMe($row)) {
			return null;
		}

		// go to a "process suffix" method
		// which will first validate data submitted,
		// and if OK, will return the html?
		$suffix = $this -> _getTokenSuffix();
		$html = $this -> _processSuffix($suffix);

		return $html;
	}

	/**
	 * Validates the data submitted based on the suffix provided
	 *
	 * @param $suffix
	 * @return html
	 */
	function _processSuffix($suffix = '') {
		$html = "";

		switch($suffix) {
			case"2" :
				if (!$verify = $this -> _verifyDB()) {
					JError::raiseNotice('_verifyDB', $this -> getError());
					$html .= $this -> _renderForm('1');
				} else {
					// migrate the data and output the results
					$html .= $this -> _doMigration($verify);
				}
				break;
			case"1" :
				if (!$verify = $this -> _verifyDB()) {
					JError::raiseNotice('_verifyDB', $this -> getError());
					$html .= $this -> _renderForm('1');
				} else {
					$suffix++;

					$vars = new JObject();
					$vars -> preview = $verify;
					$vars -> state = $this -> _getState();
					$vars -> state -> uploaded_file = $this -> _uploaded_file;
					$vars -> setError($this -> getError());

					// display a 'connection verified' message
					// and request confirmation before migrating data
					$html .= $this -> _renderForm($suffix, $vars);

					$html .= $this -> _renderView($suffix, $vars);
				}
				break;
			default :
				$html .= $this -> _renderForm('1');
				break;
		}

		return $html;
	}

	/**
	 * Prepares the 'view' tmpl layout
	 *
	 * @return unknown_type
	 */
	function _renderView($suffix = '', $vars = 0) {
		if (!$vars) {
			$vars = new JObject();
		}
		$layout = 'view_' . $suffix;
		$html = $this -> _getLayout($layout, $vars);

		return $html;
	}

	/**
	 * Prepares variables for the form
	 *
	 * @return unknown_type
	 */
	function _renderForm($suffix = '', $vars = 0) {
		if (!$vars) {
			$vars = new JObject();
			$vars -> state = $this -> _getState();
		}
		$vars -> token = $this -> _getToken($suffix);

		$layout = 'form_' . $suffix;
		$html = $this -> _getLayout($layout, $vars);

		return $html;
	}

	/*
	 * Verifies the CSV file (our DB in this case)
	 */
	function _verifyDB() {
		$state = $this -> _getState();

		// Uploads the file
		Tienda::load('TiendaFile', 'library.file');
		$upload = new TiendaFile();

		// we have to upload the file
		if (@$state -> uploaded_file == '') {
			// handle upload creates upload object properties
			$success = $upload -> handleUpload('file');

			if ($success) {
				if (strtolower($upload -> getExtension()) != 'csv') {
					$this -> setError(JText::_('COM_TIENDA_THIS_IS_NOT_A_CSV_FILE'));
					return false;
				}

				// Move the file to let us reuse it
				$upload -> setDirectory(JFactory::getConfig() -> get('tmp_path', JPATH_SITE . DS . 'tmp'));
				$success = $upload -> upload();

				if (!$success) {
					$this -> setError($upload -> getError());
					return false;
				}

				$upload -> file_path = $upload -> getFullPath();
			} else {
				$this -> setError(JText::_('COM_TIENDA_COULD_NOT_UPLOAD_CSV_FILE' . $upload -> getError()));
				return false;
			}
		}
		// File already uploaded
		else {
			$upload -> full_path = $upload -> file_path = @$state -> uploaded_file;
			$upload -> proper_name = TiendaFile::getProperName(@$state -> uploaded_file);
			$success = true;
		}

		if ($success) {
			// Get the file content
			$upload -> fileToText();
			$content = $upload -> fileastext;

			// Set the uploaded file as the file to use during the real import
			$this -> _uploaded_file = $upload -> getFullPath();

			$rows = explode("\n", $content);

			if (!count($rows)) {
				$this -> setError('No Rows in this file');
				return false;
			}

			$records = array();

			if (@$state -> skip_first) {
				$header = array_shift($rows);
				$header = explode(@$state -> field_separator, $header);
			} else {
				$header = $this -> _keys;
			}

			$records[] = $header;

			// Get the records
			foreach ($rows as $row) {
				// Get the columns
				$fields = explode(@$state -> field_separator, $row);
				if ($fields) {
					// Map them using an associative array
					$fields = $this -> _mapFields($fields);

					// explore possible multiple subfields

					// Categories
					$fields['product_categories'] = explode(@$state -> subfield_separator, $fields['product_categories']);

					// Images
					$fields['product_images'] = explode(@$state -> subfield_separator, $fields['product_images']);

					// Attributes
					$attributes = explode(@$state -> subfield_separator, $fields['product_attributes']);

					// Explode the Attribute options!
					$real_attributes = array();
					foreach ($attributes as $attribute) {
						// size:s|m|l|sx
						$att = explode(":", $attribute);

						$att_name = $att[0];

						$att_options = array();
						if (!empty($att[1])) {
							$att_options = explode("|", $att[1]);
						}
						$real_attributes[$att_name] = $att_options;
					}

					// Assign the parsed version!
					$fields['product_attributes'] = $real_attributes;

					$records[] = $fields;

				}
			}

			return $records;

		} else {
			$this -> setError(JText::_('COM_TIENDA_COULD_NOT_UPLOAD_CSV_FILE' . $upload -> getError()));
			return false;
		}

		return false;
	}

	/**
	 * Maps the parsed array to an associative array
	 * using the _keys var, for better usability
	 *
	 * @param array $fields
	 */
	function _mapFields($fields) {
		$mapped = array();
		$i = 0;
		foreach ($this->_keys as $key) {
			$mapped[$key] = @$fields[$i];
			$i++;
		}

		return $mapped;
	}

	/**
	 * Gets the appropriate values from the request
	 *
	 * @return JObject
	 */
	function _getState() {
		$state = new JObject();
		$state -> file = '';
		$state -> uploaded_file = '';
		$state -> field_separator = ';';
		$state -> subfield_separator = ',';
		$state -> skip_first = 0;

		foreach ($state->getProperties() as $key => $value) {
			$new_value = JRequest::getVar($key);
			$value_exists = array_key_exists($key, $_POST);
			if ($value_exists && !empty($key)) {
				$state -> $key = $new_value;
			}
		}
		return $state;
	}

	/**
	 * Perform the data migration
	 *
	 * @return html
	 */
	function _doMigration($data) {
		$html = "";
		$vars = new JObject();

		// perform the data migration
		// grab all the data and insert it into the tienda tables
		$state = $this -> _getState();

		if (@$state -> skip_first) {
			$header = array_shift($data);
		}
		// Insert the data in the fields
		$results = $this -> _migrate($data);

		$vars -> results = $results;

		$suffix = $this -> _getTokenSuffix();
		$suffix++;
		$layout = 'view_' . $suffix;

		$html = $this -> _getLayout($layout, $vars);
		return $html;
	}

	/**
	 * Migrate the images
	 *
	 * @param int $product_id
	 * @param array $images
	 * @param array $images
	 */
	private function _migrateImages($product_id, $images, $results) {
		Tienda::load('TiendaImage', 'library.image');

		foreach ($images as $image) {
			$check = false;
			$multiple = false;

			if (JURI::isInternal($image)) {
				$internal = true;
				$image = JPATH_SITE . DS . $image;
				if (is_dir($image)) {

					$check = JFolder::exists($image);
					$multiple = true;
				} else {
					$check = JFile::exists($image);
				}
			} else {
				$internal = false;
				$check = $this -> url_exists($image);
			}

			// Add a single image
			if (!$multiple) {
				$images_to_copy = array($image);
			} else {

				// Fetch the images from the folder and add them
				$images_to_copy = Tienda::getClass("TiendaHelperProduct", 'helpers.product') -> getGalleryImages($image);
				foreach ($images_to_copy as &$i) {
					$i = $image . DS . $i;
				}
			}

			if ($check) {
				foreach ($images_to_copy as $image_to_copy) {
					if ($internal) {
						$img = new TiendaImage($image_to_copy);
					} else {
						$tmp_path = JFactory::getApplication() -> getCfg('tmp_path');
						$file = fopen($image_to_copy, 'r');
						$file_content = stream_get_contents($file);
						fclose($file);

						$file = fopen($tmp_path . DS . $image_to_copy, 'w');

						fwrite($file, $file_content);

						fclose($file);

						$img = new TiendaImage($tmp_path . DS . $image_to_copy);
					}

					Tienda::load('TiendaTableProducts', 'tables.products');
					$product = JTable::getInstance('Products', 'TiendaTable');

					$product -> load($product_id);
					$path = $product -> getImagePath();
					$type = $img -> getExtension();

					$img -> load();
					$img -> setDirectory($path);
					// Save full Image
					$img -> save($path . $img -> getPhysicalName());

					// Save Thumb
					Tienda::load('TiendaHelperImage', 'helpers.image');
					$imgHelper = TiendaHelperBase::getInstance('Image', 'TiendaHelper');
					$imgHelper -> resizeImage($img, 'product');

				}
			}
		}

	}

	/**
	 * Do the migration
	 *
	 * @return array
	 */
	function _migrate($datas) {
		$queries = array();

		$results = array();
		$n = 0;

		// Loop though the rows
		foreach ($datas as $data) {
			// Check for product_name. Explode() could have generated an empty row
			if (!empty($data['product_name'])) {
				$isNew = false;

				if (!$data['product_id']) {
					$data['product_id'] = 0;
					$isNew = true;
				}

				JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables');
				$product = JTable::getInstance('Products', 'TiendaTable');

				if (!$isNew) {
					if (!$product -> load($data['product_id'])) {
						$isNew = true;
						$data['product_id'] = 0;
					}
				}

				// If is a new product, use product->create()
				if ($isNew) {
					$product -> product_price = 0;
					$product -> product_quantity = 0;
					$product -> bind($data);

					if ($product -> product_full_image) {
						Tienda::load('TiendaFile', 'library.file');
						// Do the same cleaning to the image title that the image helper does
						$name = explode('.', $product -> product_full_image);
						$name = TiendaFile::cleanTitle($name[0]) . '.' . $name[count($name) - 1];

						$product -> product_full_image = $name;
					}

					$product -> create();

					$this -> _migrateAttributes($product -> product_id, $data['product_attributes']);
				}
				// else use the save() method
				else {
					$product -> bind($data);

					//check if normal price exists
					Tienda::load("TiendaHelperProduct", 'helpers.product');
					$prices = TiendaHelperProduct::getPrices($product -> product_id);
					$quantities = TiendaHelperProduct::getProductQuantities($product -> product_id);

					if ($product -> save()) {
						$product -> product_id = $product -> id;

						// New price?
						if (empty($prices)) {
							// set price if new or no prices set
							$price = JTable::getInstance('Productprices', 'TiendaTable');
							$price -> product_id = $product -> id;
							$price -> product_price = $data['product_price'];
							$price -> group_id = Tienda::getInstance() -> get('default_user_group', '1');
							$price -> save();
						}
						// Overwrite price
						else {
							// set price if new or no prices set
							$price = JTable::getInstance('Productprices', 'TiendaTable');
							$price -> load($prices[0] -> product_price_id);
							$price -> product_price = $data['product_price'];
							$price -> group_id = Tienda::getInstance() -> get('default_user_group', '1');
							$price -> save();
						}

						// New quantity?
						if (empty($quantities)) {
							// save default quantity
							$quantity = JTable::getInstance('Productquantities', 'TiendaTable');
							$quantity -> product_id = $product -> id;
							$quantity -> quantity = $data['product_quantity'];
							$quantity -> save();
						}
						// Overwrite Quantity
						else {
							// save default quantity
							$quantity = JTable::getInstance('Productquantities', 'TiendaTable');
							$quantity -> load($quantities[0] -> productquantity_id);
							$quantity -> product_id = $product -> id;
							$quantity -> quantity = $data['product_quantity'];
							$quantity -> save();
						}

					}

				}

				// at this point, the product is saved, so now do additional relationships

				// such as categories
				if (!empty($product -> product_id) && !empty($data['product_categories'])) {
					foreach ($data['product_categories'] as $category_id) {
						// This is probably not the best way to do it
						// Numeric = id, string = category name
						if (!is_numeric($category_id)) {
							// check for existance
							JModel::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'models');
							$model = JModel::getInstance('Categories', 'TiendaModel');
							$model -> setState('filter_name', $category_id);
							$matches = $model -> getList();
							$matched = false;

							if ($matches) {
								foreach ($matches as $match) {
									// is a perfect match?
									if (strtolower($category_id) == strtolower($match -> category_name)) {
										$category_id = $match -> category_id;
										$matched = true;
									}
								}
							}

							// Not matched, create category
							if (!$matched) {
								$category = JTable::getInstance('Categories', 'TiendaTable');
								$category -> category_name = $category_id;
								$category -> parent_id = 1;
								$category -> category_enabled = 1;
								$category -> save();

								$category_id = $category -> category_id;
							}

						}

						// save xref in every case
						$xref = JTable::getInstance('ProductCategories', 'TiendaTable');
						$xref -> product_id = $product -> product_id;
						$xref -> category_id = $category_id;
						$xref -> save();
					}
				}

				$results[$n] -> title = $product -> product_name;
				$results[$n] -> query = "";
				$results[$n] -> error = implode('\n', $product -> getErrors());
				$results[$n] -> affectedRows = 1;

				$n++;

				$this -> _migrateImages($product -> product_id, $data['product_images'], $results);

			}

		}

		return $results;
	}

	/**
	 * Migrate a single product attributes
	 *
	 * @param TiendaTableProduct $product
	 * @param array $data
	 */
	private function _migrateAttributes($product_id, $attributes) {
		foreach ($attributes as $attribute_name => $options) {
			// Add the Attribute
			$table = JTable::getInstance('ProductAttributes', 'TiendaTable');
			$table -> product_id = $product_id;
			$table -> productattribute_name = $attribute_name;
			$table -> save();

			// Add the Options for this attribute
			$id = $table -> productattribute_id;
			foreach ($options as $option) {
				$otable = JTable::getInstance('ProductAttributeOptions', 'TiendaTable');
				$otable -> productattribute_id = $id;
				$otable -> productattributeoption_name = $option;
				$otable -> save();
			}
		}
	}

	/**
	 * Checks if the URL exists
	 * @param string $url
	 */
	private function url_exists($url) {
		$url = str_replace("http://", "", $url);
		if (strstr($url, "/")) {
			$url = explode("/", $url, 2);
			$url[1] = "/" . $url[1];
		} else {
			$url = array($url, "/");
		}

		$fh = fsockopen($url[0], 80);
		if ($fh) {
			fputs($fh, "GET " . $url[1] . " HTTP/1.1\nHost:" . $url[0] . "\n\n");
			if (fread($fh, 22) == "HTTP/1.1 404 Not Found") {
				return FALSE;
			} else {
				return TRUE;
			}

		} else {
			return FALSE;
		}
	}

}
