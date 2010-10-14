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

Tienda::load( 'TiendaToolPlugin', 'library.plugins.tool' );

class plgTiendaTool_CsvImporter extends TiendaToolPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element   = 'tool_csvimporter';
    
    /**
     * 
     * @var $_keys	array	Contains the columns names
     */
    var $_keys		= array();
    
    var $_uploaded_file		= '';
    
	function plgTiendaTool_CsvImporter(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
		
		$this->_keys = array(
							'id',
							'name',
							'categories',
							'manufacturer',
							'short_description',
							'long_description',
							'images',
							'ship',
							'height',
							'width',
							'length',
							'weight',
							'price',
							'quantity',
							'attributes'						
						);
	}
	
    /**
     * Overriding 
     * 
     * @param $options
     * @return unknown_type
     */
    function onGetToolView( $row )
    {
        if (!$this->_isMe($row)) 
        {
            return null;
        }
        
        // go to a "process suffix" method
        // which will first validate data submitted,
        // and if OK, will return the html?
        $suffix = $this->_getTokenSuffix();
        $html = $this->_processSuffix( $suffix );

        return $html;
    }
    
    /**
     * Validates the data submitted based on the suffix provided
     * 
     * @param $suffix
     * @return html
     */
    function _processSuffix( $suffix='' )
    {
        $html = "";
        
        switch($suffix)
        {
            case"2":
                if (!$verify = $this->_verifyDB())
                {
                    JError::raiseNotice('_verifyDB', $this->getError());
                    $html .= $this->_renderForm( '1' );
                }
                    else
                {
                    // migrate the data and output the results
                    $html .= $this->_doMigration($verify);
                }
                break;
            case"1":
                if (!$verify = $this->_verifyDB())
                {
                    JError::raiseNotice('_verifyDB', $this->getError());
                    $html .= $this->_renderForm( '1' );
                }
                    else
                {
                    $suffix++;
                    
                    $vars = new JObject();
                    $vars->preview = $verify;
                    $vars->state = $this->_getState();
                    $vars->state->uploaded_file = $this->_uploaded_file;
                    $vars->setError($this->getError());
                    
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
    function _renderView( $suffix='', $vars = 0 )
    {
    	if(!$vars)
    	{
        	$vars = new JObject();
    	}
        $layout = 'view_'.$suffix;
        $html = $this->_getLayout($layout, $vars);
        
        return $html;
    }
    
    /**
     * Prepares variables for the form
     * 
     * @return unknown_type
     */
    function _renderForm( $suffix='', $vars = 0 )
    {
    	if(!$vars)
    	{
        	$vars = new JObject();
        	$vars->state = $this->_getState();
    	}
        $vars->token = $this->_getToken( $suffix );
        
        $layout = 'form_'.$suffix;
        $html = $this->_getLayout($layout, $vars);
        
        return $html;
    }
    
    
    /*
     * Verifies the CSV file (our DB in this case)
     */
    function _verifyDB()
    {
    	$state = $this->_getState();
    	
    	// Uploads the file
		Tienda::load( 'TiendaFile', 'library.file' );
		$upload = new TiendaFile();
		
    	// we have to upload the file
    	if(@$state->uploaded_file == '')
    	{
			// handle upload creates upload object properties
			$success = $upload->handleUpload( 'file' );
			
			if($success)
			{
	    		if( strtolower($upload->getExtension()) != 'csv' )
				{
					$this->setError(JText::_('This is not a CSV file'));
					return false;
				}
				
				// Move the file to let us reuse it 
				$upload->setDirectory(JFactory::getConfig()->get('tmp_path', JPATH_SITE.DS.'tmp'));
				$success = $upload->upload();
				
				if(!$success)
				{
					$this->setError($upload->getError());
					return false;
				}
				
				$upload->file_path = $upload->getFullPath();
			}
	    	else
			{
				$this->setError(JText::_('Could Not Upload CSV File: '.$upload->getError()));
				return false;
			}
    	}
    	// File already uploaded
    	else
    	{
    		$upload->full_path = $upload->file_path = @$state->uploaded_file;
    		$upload->proper_name = TiendaFile::getProperName(@$state->uploaded_file);
    		$success = true;
    	}
    	
		if( $success )
		{			
			// Get the file content
			$upload->fileToText();
			$content = $upload->fileastext;			
			
			// Set the uploaded file as the file to use during the real import
			$this->_uploaded_file = $upload->getFullPath();
			
			$rows = explode( "\n", $content  );
			
			if(!count($rows))
			{
				$this->setError('No Rows in this file');
				return false;
			}
			
			$records = array();
			
			if(@$state->skip_first)
			{
				$header = array_shift($rows);
				$header = explode( @$state->field_separator, $header );
			}
			else
			{
				$header = $this->_keys;
			}
			
			$records[] = $header;
			
			// Get the records
			foreach( $rows as $row )
			{				
				// Get the columns
				$fields = explode( @$state->field_separator, $row );
				if( $fields )
				{
					// Map them using an associative array
					$fields = $this->_mapFields($fields);
					
					// explore possible multiple subfields

					// Categories
					$fields['categories'] = explode( @$state->subfield_separator, $fields['categories'] );

					// Images
					$fields['images'] = explode( @$state->subfield_separator, $fields['images'] );
					
					// Attributes
					$attributes = explode( @$state->subfield_separator, $fields['attributes'] );
					
					// Explode the Attribute options!
					$real_attributes = array();
					foreach( $attributes  as $attribute )
					{
						// size:s|m|l|sx
						$att = explode( ":", $attribute );
						
						$att_name = $att[0];
						
						$att_options = explode( "|", $att[1] );
						$real_attributes[$att_name] = $att_options;
					}

					// Assign the parsed version!
					$fields['attributes'] = $real_attributes;
					
					$records[] = $fields;
					
				}
			}
			
			return $records;
			
		}
		else
		{
			$this->setError(JText::_('Could Not Upload CSV File: '.$upload->getError()));
			return false;
		}
		
		return false;
    }
    
    function _mapFields($fields)
    {
    	$mapped = array();
    	$i = 0;
    	foreach($this->_keys as $key)
    	{
    		$mapped[$key] = @$fields[$i];
    		$i++;
    	}
    	
    	return $mapped;
    }

    /**
     * Gets the appropriate values from the request
     * 
     * @return unknown_type
     */
    function _getState()
    {
        $state = new JObject();
        $state->file = '';
        $state->uploaded_file = '';
        $state->field_separator = ';';
        $state->subfield_separator = ',';
        $state->skip_first = 0;
        
        foreach ($state->getProperties() as $key => $value)
        {
            $new_value = JRequest::getVar( $key );
            $value_exists = array_key_exists( $key, $_POST );
            if ( $value_exists && !empty($key) )
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
    function _doMigration($data)
    {
        $html = "";
        $vars = new JObject();
        
        // perform the data migration
        // grab all the data and insert it into the tienda tables
        $state = $this->_getState();
        
        if(@$state->skip_first)
        {
        	$header = array_shift($data);	
        }
        // Insert the data in the fields
        $results = $this->_migrate($data); 
                       
        $vars->results = $results;
        
        $suffix = $this->_getTokenSuffix();
        $suffix++;
        $layout = 'view_'.$suffix;
                
        $html = $this->_getLayout($layout, $vars);
        return $html;
    }
    
    private function _migrateImages($data, &$results)
    {    	
    	$n = count($results);
    	
    	$results[$n]->title = 'Product Images';
        $results[$n]->query = 'Copy Product Images & Resize';
        $results[$n]->error = '';
        $results[$n]->affectedRows = 0;
    	
    	foreach( $data as $result )
    	{
    		$check = false;
    		if($internal)	
    		{
    			$check = JFile::exists($vm_image_path.$result['image']);
    		}
    		else
    		{
    			$check = $this->url_exists($vm_image_path) && $result['image'];
    		}
    		
    		if($check)
    		{
    			if($internal)
    			{
	    			$img = new TiendaImage($vm_image_path.$result['image']);
    			}
    			else
    			{
    				$tmp_path = JFactory::getApplication()->getCfg('tmp_path');
    				$file = fopen($vm_image_path.$result['image'], 'r');
    				$file_content = stream_get_contents($file);
    				fclose($file);
    				
    				$file = fopen($tmp_path.DS.$result['image'], 'w');
    				
    				fwrite($file, $file_content);
    				
    				fclose($file);
    				 				    				
    				$img = new TiendaImage($tmp_path.DS.$result['image']);
    			}
	    		
	    		Tienda::load( 'TiendaTableProducts', 'tables.products' );
	        	$product = JTable::getInstance( 'Products', 'TiendaTable' );
	        	
	    		$product->load($result['id']);
	    		$path = $product->getImagePath();
	    		$type = $img->getExtension();	    		
	    		
	            $img->load();
	    		// Save full Image
	    		if(!$img->save($path.$result['image']))
	    		{
	    			$results[$n]->error .= '::Could not Save Product Image- From: '.$vm_image_path.$result['image'].' To: '.$path.$result['image'];
	    		}
	    		
	    		// Save Thumb
	    		Tienda::load( 'TiendaHelperImage', 'helpers.image' );
				$imgHelper = TiendaHelperBase::getInstance('Image', 'TiendaHelper');
				if (!$imgHelper->resizeImage( $img, 'product'))
				{
					$results[$n]->error .= '::Could not Save Product Thumb';
				}
				$results[$n]->affectedRows++;
	    				
	    	}
    	}
    	
    	$n++;
    	
    	// CATEGORIES
    	
    	// Fetch the VM full image
    	$query = "SELECT category_id as id, category_full_image as image FROM {$p}category";
    	$db->setQuery($query);
    	$products = $db->loadAssocList();
    	
    	Tienda::load('TiendaImage', 'library.image');
    	
   		if($internal)
    		$vm_image_path = JPATH_SITE.DS."components".DS."com_virtuemart".DS."shop_image".DS."category".DS;
    	else
    	{
    		$state = $this->_getState();
    		$url = $state->external_site_url;
    		$vm_image_path = $url."/components/com_virtuemart/shop_image/category/";
    	}
    	
    	$results[$n]->title = 'Category Images';
        $results[$n]->query = 'Copy Category Images & Resize';
        $results[$n]->error = '';
        $results[$n]->affectedRows = 0;
    
    	foreach( $products as $result )
    	{
    		$check = false;
    		if($internal)	
    		{
    			$check = JFile::exists($vm_image_path.$result['image']);
    		}
    		else
    		{
    			$check = $this->url_exists($vm_image_path) && $result['image'];
    		}
    		
    		if($check)
    		{
    			if($internal)
    			{
	    			$img = new TiendaImage($vm_image_path.$result['image']);
    			}
    			else
    			{
    				$tmp_path = JFactory::getApplication()->getCfg('tmp_path');
    				$file = fopen($vm_image_path.$result['image'], 'r');
    				$file_content = stream_get_contents($file);
    				fclose($file);
    				
    				$file = fopen($tmp_path.DS.$result['image'], 'w');
    				
    				fwrite($file, $file_content);
    				
    				fclose($file);
    				 				    				
    				$img = new TiendaImage($tmp_path.DS.$result['image']);
    			}	    		
	            
	            $img->load();
	    		
	    		// Save full Image
	    		if(!$img->save($path.$result['image']))
	    		{
	    			$results[$n]->error .= '::Could not Save Category Image - From: '.$vm_image_path.$result['image'].' To: '.$path.$result['image'];
	    		}
	    		
	    		// Save Thumb
	    		Tienda::load( 'TiendaHelperImage', 'helpers.image' );
				$imgHelper = TiendaHelperBase::getInstance('Image', 'TiendaHelper');
				if (!$imgHelper->resizeImage( $img, 'category'))
				{
					$results[$n]->error .= '::Could not Save Category Thumb';
				}
		
				$results[$n]->affectedRows++;
	    	}
    	}
    	
    }

    /**
     * Do the migration
     * 
     * @return array
     */
    function _migrate($datas)
    {
        $queries = array();
        
        foreach($datas as $data)
        {
			foreach($data as &$field)
			{
				$field = $jDBO->Quote( $field );
			}
	        
	        if(!$data['id'])
	        {
		        // migrate products
		        $query->title = "PRODUCT";
		        $query->insert = "
		            INSERT IGNORE INTO #__tienda_products ( product_name, product_weight, product_description, product_width, product_length, product_height, product_full_image, product_enabled, manufacturer_id = {$data["manufacturer"]} )
		            VALUES ";
		        $query->type = "insert";
		       	$query->insert .= "('{$data["name"]}', '{$data["weight"]}', '{$data["long_description"]}', '{$data["width"]}', '{$data["length"]}', '{$data["height"]}', '{$data["full_image"]}', 1 ), ";

	        }
	        else
	        {
	        	// migrate products
		        $query->title = "PRODUCT";
		        $query->insert = "";
		        $query->type = "update";
		        
		        $query->insert .= "UPDATE #__tienda_products SET product_name = '{$data["name"]}', product_weight = '{$data["weight"]}', product_description = '{$data["long_description"]}', product_width = '{$data["width"]}', product_length = '{$data["length"]}', product_height = '{$data["height"]}', '{$data["full_image"]}', product_enabled = 1 WHERE product_id = '{$data["id"]}' ;";
	        }
	                
	        $results = array();        
	        $n=0;
	        
	        $errors = array();
	
            $jDBO->setQuery( $query->insert );
            if (!$jDBO->query())
            {
            	$errors[] = $jDBO->getErrorMsg();
            }
            else
            {
            	// add the product id
                if($query->type == "insert")
                {
                	$jDBO->setQuery("SELECT LAST_INSERT_ID();");
                    $data["id"] = $jDBO->loadResult();
                }
             }

            $results[$n]->title = $query->title;
            $results[$n]->query = $query->insert;
            $results[$n]->error = implode('\n', $errors);
            $results[$n]->affectedRows = count( $rows );
            $n++; 
            
            // Now that we have the product id, we can also add quantities, prices and categories
            
        }

        
        //$this->_migrateImages($data, $results);
        
        return $results;
    }

    
	private function url_exists($url){
        $url = str_replace("http://", "", $url);
        if (strstr($url, "/")) {
            $url = explode("/", $url, 2);
            $url[1] = "/".$url[1];
        } else {
            $url = array($url, "/");
        }

        $fh = fsockopen($url[0], 80);
        if ($fh) {
            fputs($fh,"GET ".$url[1]." HTTP/1.1\nHost:".$url[0]."\n\n");
            if (fread($fh, 22) == "HTTP/1.1 404 Not Found") { return FALSE; }
            else { return TRUE;    }

        } else { return FALSE;}
    }
   
}
