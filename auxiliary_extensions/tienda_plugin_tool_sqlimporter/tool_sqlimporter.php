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

class plgTiendaTool_SqlImporter extends TiendaToolPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element   = 'tool_sqlimporter';
        
    var $_uploaded_file		= '';
    var $_manufacturer = ''; // name of manufacturer
    var $_information = array(); // categories of their products
    var $deliminer_primary = '-- Dumping data for table'; // to cut sql dump into separate INSERT query sections
    var $deliminer_secondary = 'DROP TABLE IF EXISTS'; // to cut-off creating tables
    var $deliminer_insert = 'INSERT INTO '; // to cut out all INSERT queries separately
    var $deliminer_unlock = "\n/*!"; // to cut out the last INSERT query
    
   	function plgTiendaTool_SqlImporter(& $subject, $config) 
   	{
   		parent::__construct($subject, $config);
   		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
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
     * Verifies the SQL dump file (our DB in this case)
     */
    function _verifyDB()
    {
    	$state = $this->_getState();
    	$first_time = false; // we do not need to parse the SQL DUMP
    	
    	// Uploads the file
    	Tienda::load( 'TiendaFile', 'library.file' );
    	$upload = new TiendaFile();
    	
    	// we have to upload the file
    	if(@$state->uploaded_file == '')
    	{
    		$first_time = true; // we  need to parse the SQL dump
				// handle upload creates upload object properties
				$success = $upload->handleUpload( 'file' );
				
				if($success)
				{
		    	if( strtolower($upload->getExtension()) != 'sql' )
					{
						$this->setError(JText::_('This is not a SQL dump'));
						return false;
					}
					
					// Move the file to let us reuse it 
					$upload->setDirectory(JFactory::getConfig()->get('tmp_path', JPATH_SITE.DS.'tmp'));
					$upload->upload();
					
					if(!$success)
					{
						$this->setError($upload->getError());
						return false;
					}
					
					$upload->file_path = $upload->getFullPath();
				}
		    else
				{
					$this->setError(JText::_('Could Not Upload SQL File: '.$upload->getError()));
					return false;
				}

				// Set the uploaded file as the file to use during the real import
				$this->_uploaded_file = $upload->getFullPath();
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
			if($first_time)
				return $this->_loadSqlDump($content);
			else
				return true;
		}
		else
		{
			$this->setError(JText::_('Could Not Upload SQL File: '.$upload->getError()));
			return false;
		}
    }

    /*
     * Prepares a string to be a db query
     * 
     * @param $str  String to be prepared
     * @param $cut  If the last character should be cut-off (usually ';')
     * 
     * @return Prepared db query
     */
    function _prepareSubstrQuery($str, $cut)
    {
    		$str = trim($str);
    		if($cut)
    			return substr($str,0, strlen($str)-1);
    		else
    			return substr($str,0, strlen($str));
    }

    /*
     * Cuts out sql queries separated by ';'
     * Copied from Joomla! installation script
     * 
     * @param $sql
     * 
     * @return Array with sql queries
     */
    function _splitQueries($sql)
		{
			// Initialise variables.
			$buffer		= array();
			$queries	= array();
			$in_string	= false;
	
			// Trim any whitespace.
			$sql = trim($sql);
	
			// Remove comment lines.
			$sql = preg_replace("/\n\#[^\n]*/", '', "\n".$sql);
	
			// Parse the schema file to break up queries.
			for ($i = 0; $i < strlen($sql) - 1; $i ++)
			{
				if ($sql[$i] == ";" && !$in_string) {
					$queries[] = substr($sql, 0, $i);
					$sql = substr($sql, $i +1);
					$i = 0;
				}
	
				if ($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\") {
					$in_string = false;
				}
				elseif (!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset ($buffer[0]) || $buffer[0] != "\\")) {
					$in_string = $sql[$i];
				}
				if (isset ($buffer[1])) {
					$buffer[0] = $buffer[1];
				}
				$buffer[1] = $sql[$i];
			}
	
			// If the is anything left over, add it to the queries.
			if (!empty($sql)) {
				$queries[] = $sql;
			}
	
			return $queries;
		}
    
    /**
     * Loads SQL dump into DB (faster than phpMyAdmin)
     * 
     * @params $dump SQL dump to load
     */
    function _loadSqlDump($dump)
    {
    	$skip_tbl = array(1,3,5,6,7,8,10); // skip useless tables
    	$this->_dropTmpTables(); // drop temporary tables in case there were any left
    	$this->_createTmpTables(); // create all temporary tables
    	$db = JFactory::getDbo();
    	$results = array(); // parts with INSERT queries
    	$arr = explode($this->deliminer_primary, $dump); // array with a raw parts    	

    	for($i = 1, $c = count($arr); $i < $c; $i++)
		  {  
		  	if(strpos($arr[$i], $this->deliminer_secondary) !== false) // if we find 'create table' query -> we get rid of it
		  	{
		  		$tmp = explode($this->deliminer_secondary, $arr[$i]);
		  		$results[$i-1] = '--'.$tmp[0];
		  	}
		  	else
		  	{
		  		$results[$i-1] = '--'.$arr[$i];
		  	}
		  }

		  for($i = 0,$c = count($results); $i < $c; $i++)
		  {
		  	if(array_search($i, $skip_tbl) !== false) // skip useless tables
		  		continue;
		  		
			 	$tmp = explode($this->deliminer_insert,$results[$i]);
				$count = count($tmp);
				
				// split the initial commands and execute them
				$tmp2= $this->_splitQueries($tmp[0]);
				for($j = 0, $cj = count($tmp2); $j < $cj; $j++)
				{
					$q = $this->_prepareSubstrQuery($tmp2[$j], $j == $cj-1);
					$db->setQuery($q);
					if(!$db->query())
					{
						$this->_dropTmpTables();
						$this->setError(JText::_('THIS IS NOT A SQL DUMP').$q);
						return false;
					}
				}
				
				// execute all INSERT queries except for the last one (which is "special")
				for($j = 1; $j < $count-1; $j++)
				{
					$db->setQuery($this->deliminer_insert.$this->_prepareSubstrQuery($tmp[$j], true));
					if(!$db->query())
					{
//						$this->_dropTmpTables();						
						$this->setError(JText::_('THIS IS NOT A SQL DUMP').$db->getErrorMsg());
						return false;
					}
				}
				
				// split the last INSERT query into INSERT query and the rest
				$tmp2 = explode($this->deliminer_unlock,$tmp[$count-1]);
				// execute the INSERT query
				$db->setQuery($this->deliminer_insert.$this->_prepareSubstrQuery($tmp2[0], false));
				if(!$db->query())
				{
					$this->_dropTmpTables();
					$this->setError(JText::_('THIS IS NOT A SQL DUMP').$this->deliminer_insert.$this->_prepareSubstrQuery($tmp2[0], false));
					return false;
				}
				
				// split the rest into separate commands and execute them
				$tmp3 = $this->_splitQueries('/*!'.$tmp2[1]);
				for($j = 0, $cj = count($tmp3); $j < $cj-1; $j++)
				{
					$db->setQuery($this->_prepareSubstrQuery($tmp3[$j], false));
					if(!$db->query())
					{
						$this->_dropTmpTables();
						$this->setError(JText::_('THIS IS NOT A SQL DUMP').$this->_prepareSubstrQuery($tmp3[$j], false));
						return false;
					}
				}
		  }
		  return true;
    }
    
    /*
     * Drops all temporary tables
     * 
     */
    function _dropTmpTables()
    {
    	$db = JFactory::getDbo();
/*    	$q = 'DROP TABLE `merchandise`, `order_item`, `pass`, `shop_product`, `skier`, '.
    			 '`ski_pass`, `solitude_order`, `state`, `unique_number`, `user`, `user_role`,'.
    			 '`user_user_role`;';
*/
    	$q = 'DROP TABLE `merchandise`, `pass`, `ski_pass`, `user`, `user_role`, `user_user_role`;';    	
     	$db->setQuery($q);
    	$db->query();
    }

    /*
     * Creates all temporary tables
     * 
     */
    function _createTmpTables()
    {
    	// we do not need `state`,`unique_number`,`user_role` for importing data
    	$db = JFactory::getDbo();
    	$queries = array();
    	$queries[] = 'CREATE TABLE IF NOT EXISTS `merchandise` (
									  `id` int(10) unsigned NOT NULL,
									  `name` varchar(255) NOT NULL,
									  `description` text NOT NULL,
									  `price` decimal(8,2) NOT NULL,
									  `created_at` datetime DEFAULT NULL,
									  `updated_at` timestamp NULL DEFAULT NULL,
									  `image` varchar(255) NOT NULL,
									  PRIMARY KEY (`id`)
									) ENGINE=MyISAM  DEFAULT CHARSET=latin1';

/*    	$queries[] = 'CREATE TABLE IF NOT EXISTS `order_item` (
									  `id` int(10) unsigned NOT NULL,
									  `name` varchar(80) NOT NULL,
									  `parameters` text NOT NULL,
									  `order_id` int(10) unsigned NOT NULL,
									  `created_at` datetime DEFAULT NULL,
									  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
									) ENGINE=MyISAM  DEFAULT CHARSET=latin1';
*/    	
    	$queries[] = 'CREATE TABLE IF NOT EXISTS `pass` (
									  `id` int(10) unsigned NOT NULL,
									  `name` varchar(80) NOT NULL,
									  `created_at` datetime DEFAULT NULL,
									  `updated_at` timestamp NULL DEFAULT NULL,
									  `price` decimal(8,2) NOT NULL,
									  `info` text NOT NULL
									) ENGINE=MyISAM  DEFAULT CHARSET=latin1';
    	
/*    	$queries[] = 'CREATE TABLE IF NOT EXISTS `shop_product` (
									  `id` int(10) unsigned NOT NULL,
									  `name` varchar(255) NOT NULL,
									  `description` text NOT NULL,
									  `price` decimal(8,2) NOT NULL,
									  `ski_pass_id` int(10) unsigned DEFAULT NULL,
									  `created_at` datetime DEFAULT NULL,
									  `updated_at` timestamp NULL DEFAULT NULL,
									  `image` varchar(255) NOT NULL
									) ENGINE=MyISAM  DEFAULT CHARSET=latin1';
    	
    	$queries[] = 'CREATE TABLE IF NOT EXISTS `skier` (
									  `id` int(10) unsigned NOT NULL,
									  `first_name` varchar(80) NOT NULL,
									  `last_name` varchar(80) NOT NULL,
									  `birth_date` date DEFAULT NULL,
									  `user_id` int(10) unsigned DEFAULT NULL,
									  `wtp_number` varchar(30) NOT NULL,
									  `updated_at` datetime DEFAULT NULL,
									  `created_at` datetime DEFAULT NULL,
									  `pass_holder_id` int(10) unsigned NOT NULL
									) ENGINE=MyISAM  DEFAULT CHARSET=latin1';
*/    	
    	$queries[] = 'CREATE TABLE IF NOT EXISTS `ski_pass` (
									  `id` int(10) unsigned NOT NULL,
									  `created_at` datetime DEFAULT NULL,
									  `upated_at` timestamp NULL DEFAULT NULL,
									  `name` varchar(255) NOT NULL
									) ENGINE=MyISAM  DEFAULT CHARSET=latin1';
    	
/*    	$queries[] = 'CREATE TABLE IF NOT EXISTS `solitude_order` (
									  `id` int(11) unsigned NOT NULL,
									  `first_name` varchar(80) NOT NULL,
									  `last_name` varchar(80) NOT NULL,
									  `address1` varchar(120) NOT NULL,
									  `address2` varchar(120) NOT NULL,
									  `city` varchar(80) NOT NULL,
									  `state` varchar(80) NOT NULL,
									  `country` varchar(80) NOT NULL,
									  `postal_code` varchar(80) NOT NULL,
									  `phone` varchar(30) NOT NULL,
									  `email` varchar(255) NOT NULL,
									  `buyer_status` varchar(80) NOT NULL,
									  `order_pickup_location` varchar(60) NOT NULL,
									  `cc_number` varchar(60) NOT NULL,
									  `cc_exp_date` varchar(20) NOT NULL,
									  `price` decimal(8,2) NOT NULL,
									  `user_id` int(10) unsigned DEFAULT NULL,
									  `created_at` datetime DEFAULT NULL,
									  `updated_at` timestamp NULL DEFAULT NULL
									) ENGINE=MyISAM  DEFAULT CHARSET=latin1';
*/    	
    	$queries[] = 'CREATE TABLE IF NOT EXISTS `user` (
									  `id` int(10) unsigned NOT NULL,
									  `username` varchar(60) NOT NULL,
									  `password` varchar(255) NOT NULL,
									  `email` varchar(255) NOT NULL,
									  `created_at` datetime DEFAULT NULL,
									  `updated_at` datetime DEFAULT NULL,
									  `last_login` datetime DEFAULT NULL,
									  `salt` varchar(255) NOT NULL DEFAULT \'\',
									  `first_name` varchar(80) NOT NULL,
									  `last_name` varchar(80) NOT NULL,
									  `address1` varchar(255) NOT NULL,
									  `address2` varchar(255) NOT NULL,
									  `city` varchar(80) NOT NULL,
									  `state` varchar(80) NOT NULL,
									  `postal_code` varchar(30) NOT NULL,
									  `country_code` char(2) NOT NULL,
									  `phone` varchar(30) NOT NULL
									) ENGINE=MyISAM  DEFAULT CHARSET=latin1';
    	
    	$queries[] = 'CREATE TABLE IF NOT EXISTS `user_user_role` (
									  `id` int(10) unsigned NOT NULL,
									  `user_id` int(10) unsigned NOT NULL,
									  `user_role_id` int(10) unsigned NOT NULL
									) ENGINE=MyISAM  DEFAULT CHARSET=latin1';

    	$c = count($queries);
    	for($i = 0; $i < $c; $i++)
    	{
    		$db->setQuery($queries[$i]);
    		$db->query();
    	}
    }
    
    /**
     * Gets the appropriate values from the request
     * 
     * @return JObject
     */
    function _getState()
    {
        $state = new JObject();
        $state->file = '';
        $state->uploaded_file = '';
        $state->manufacturer = '';
        $state->categories_products = array();
        
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
     * @param $file SQL dump
     * 
     * @return html
     */
    function _doMigration($file)
    {
        $html = "";
        $vars = new JObject();
        $state = $this->_getState();
        
				// migrade data
        $results = $this->_migrate();

        $vars->results = $results;
        
        $suffix = $this->_getTokenSuffix();
        $suffix++;
        $layout = 'view_'.$suffix;
                
        $html = $this->_getLayout($layout, $vars);
        return $html;
    }
    
    /**
     * Do the migration
     * 
     * @return array
     */
    function _migrate()
    {
			JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables');
    	$results = array();
    	$results[0] = $this->_migrateMerchandise();
    	$results[1] = $this->_migratePass();
    	$results[2] = $this->_migrateSkiPass();
    	$results[3] = $this->_migrateUsers();
    	return $results;
    }
    
    /*
     * Creates an  category of product
		 *
     * @param $name Name of category of product
     * 
     * @return ID of category of product
     */
    private function _getCategory($name)
    {
			$category = JTable::getInstance('Categories', 'TiendaTable');
			$category->category_name = $name;
			$category->parent_id = 1;
			$category->category_enabled = 1;
			$category->save();
			return $category->category_id;
    }

    /*
     * Gets an ID of product
     * @param $name Name of of product
     * 
     * @return ID of product
     */
    private function _getProduct($name)
    {
			$name_tmp = strtolower($name);
    	JModel::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models');
			$model = JModel::getInstance('Products', 'TiendaModel');
			$model->setState('filter_name', $name);
			$matches = $model->getList();
			$matched = false;
			
			if($matches)
			{
				foreach($matches as $match)
				{
					// is a perfect match?
					if($name_tmp == strtolower($match->product_name))
						return $match->product_id;
				}
			}
			return false; // no match
    }

    /**
     * Does the migration of 'merchandise' table
     * 
     * @return array
     */
    function _migrateMerchandise()
    {
    	$result = array();
    	$result['num'] = 0;
    	$result['title'] = 'Merchandise';
    	$db = JFactory::getDbo();    	
    	$state = $this->_getState();
    	$state->categories_products['merchandise'] = $this->_getCategory('Merchandise'); // get ID of category
			$product = JTable::getInstance('Products', 'TiendaTable');
			
    	$q = 'SELECT * FROM `merchandise`';
    	$db->setQuery($q);
    	$list = $db->loadObjectList();
    	$c = count($list);
    	for($i = 0; $i<$c; $i++)
    	{
    		$data['product_name'] = $list[$i]->name;
    		$data['product_description_short'] = $data['product_description'] = $list[$i]->description;
    		
				$product = JTable::getInstance('Products', 'TiendaTable');		                
				$product->product_price = $list[$i]->price;
				$product->product_quantity = 0;
				$product->bind($data);
				$product->create(); // create a new product
						
        // set category of this product as 'merchandise'
        $xref = JTable::getInstance( 'ProductCategories', 'TiendaTable' );
        $xref->product_id = $product->product_id;
        $xref->category_id = $state->categories_products['merchandise'];
        $xref->save();
        $result['num']++;
    	}
    	return $result;
    }

    /**
     * Does the migration of 'pass' table
     * 
     * @return array
     */
    function _migratePass()
    {
    	$result = array();
    	$result['title'] = 'Pass';
    	$result['num'] = 0;
    	$db = JFactory::getDbo();    	
    	$state = $this->_getState();
    	$state->categories_products['pass'] = $this->_getCategory('Pass'); // get ID of category
			$product = JTable::getInstance('Products', 'TiendaTable');
			
    	$q = 'SELECT * FROM `pass`';
    	$db->setQuery($q);
    	$list = $db->loadObjectList();
    	$c = count($list);
    	for($i = 0; $i<$c; $i++)
    	{
    		$data['product_name'] = $list[$i]->name;
    		$data['product_description_short'] = $data['product_description'] = $list[$i]->info;
    		
				$product = JTable::getInstance('Products', 'TiendaTable');		                
				$product->product_price = $list[$i]->price;
				$product->product_quantity = 0;
				$product->bind($data);
				$product->create(); // create a new product
						
        // set category of this product as 'pass'
        $xref = JTable::getInstance( 'ProductCategories', 'TiendaTable' );
        $xref->product_id = $product->product_id;
        $xref->category_id = $state->categories_products['pass'];
        $xref->save();
        $result['num']++;
    	}
    	return $result;
    }

    /**
     * Does the migration of 'ski_pass' table
     * 
     * @return array
     */
    function _migrateSkiPass()
    {
    	$result = array();
    	$result['title'] = 'Ski pass';
    	$result['num'] = 0;
    	$db = JFactory::getDbo();    	
    	$state = $this->_getState();
    	$state->categories_products['ski_pass'] = $this->_getCategory('Ski Pass'); // get ID of category
			$product = JTable::getInstance('Products', 'TiendaTable');
			
    	$q = 'SELECT * FROM `ski_pass`';
    	$db->setQuery($q);
    	$list = $db->loadObjectList();
    	$c = count($list);
    	for($i = 0; $i<$c; $i++)
    	{
    		$data['product_name'] = $list[$i]->name;
    		$data['product_description_short'] = $data['product_description'] = $list[$i]->name;
    		
				$product = JTable::getInstance('Products', 'TiendaTable');		                
				$product->product_price = 0;
				$product->product_quantity = 0;
				$product->bind($data);
				$product->create(); // create a new product
						
        // set category of this product as 'pass'
        $xref = JTable::getInstance( 'ProductCategories', 'TiendaTable' );
        $xref->product_id = $product->product_id;
        $xref->category_id = $state->categories_products['ski_pass'];
        $xref->save();
        $result['num']++;
    	}
    	return $result;
    }

    /**
     * Does the migration of 'users' table
     * 
     * @return array
     */
    function _migrateUsers()
    {
    	$result = array();
    	$result['title'] = 'Users';
    	$db = JFactory::getDbo();
    	$this->_createUserGroups(); // create user groups ('admin','registered')
			Tienda::load( 'TiendaHelperUser', 'helpers.user' );
			jimport('joomla.user.helper');
    	
    	$result['num'] = 0;
    	$result['error'] = 0;
			$q = 'SELECT * FROM `user`';
    	$db->setQuery($q);
    	$list = $db->loadObjectList();
    	
    	$c = count($list);
    	for($i = 0; $i<$c; $i++)
    	{
    		if($list[$i]->username == 'testuser')
    			continue;
    			
        $result['num']++;
        $q = 'SELECT `user_role_id` FROM `user_user_role` WHERE `user_id` = '.$list[$i]->id;
        $db->setQuery($q);
        $role = $db->loadObjectList();
    		
        $details['name']        = $list[$i]->last_name.' '.$list[$i]->first_name;
        $details['username']    = $list[$i]->username;
        $details['email']       = $list[$i]->email;
        $details['block']       = 0;
        $user =&TiendaHelperUser::createNewUser($details); // create a new user
        if($user == false)
        {
        	$result['error']++;
        	continue;
        }

        $user->activation = ''; // no need for activation
        $user->usertype = $role[0]->user_role_id == 1 ? 'Administrator' : 'Registered'; // set up correct role in system
        $user->save(); // save new information
        
        // add user's info
        $userInfo = JTable::getInstance('UserInfo','TiendaTable');
        $userInfo->user_id = $user->get('id');
        $userInfo->fist_name = $list[$i]->first_name;
        $userInfo->last_name = $list[$i]->last_name;
        $userInfo->phone_1 = $list[$i]->phone;
        $userInfo->email = $list[$i]->email;
        $userInfo->store();
        
        // connects user to his group
        $userGroup = JTable::getInstance('UserGroups','TiendaTable');
        $userGroup->user_id = $user->get('id');
        $userGroup->group_id = $role[0]->user_role_id == 1 ? 2 : 3;
        $userGroup->store();
        
        // add user's address
        $address = JTable::getInstance('Addresses','TiendaTable');
        $address->user_id = $user->get('id');
        $address->first_name = $list[$i]->first_name;
        $address->last_name = $list[$i]->last_name;
        $address->phone_1 = $list[$i]->phone;
        $address->address_1 = $list[$i]->address1;
        $address->address_2 = $list[$i]->address2;
        $address->city = $list[$i]->city;
        $address->postal_code = $list[$i]->postal_code;
        $address->country_id = $this->_findCountry($list[$i]->country_code);
        $address->zone_id = $this->_findZone($list[$i]->state,$address->country_id);
        $address->store();        
    	}
    	return $result;
    }

    /**
     * Finds a country
     */
    function _findCountry($old_country)
    {
    	$db = JFactory::getDbo();
    	$q = 'SELECT `country_id` FROM `#__tienda_countries` WHERE `country_isocode_2` = \''.$old_country.'\'';
    	$db->setQuery($q);
    	$res = $db->loadObjectList();
    	return $res[0]->country_id;
    }
    
    /**
     * Finds zone in a country
     */
    function _findZone($old_zone, $country)
    {
    	$old_zone = strtolower($old_zone);
    	$db = JFactory::getDbo();
    	$q = 'SELECT `zone_id` FROM `#__tienda_zones` WHERE `country_id` = '.$country.' AND LOWER(`zone_name`) = \''.$old_zone.'\'';
    	$db->setQuery($q);
    	$res = $db->loadObjectList();

    	return @$res[0]->zone_id;
    }
    
    /**
     * Creates user groups for importing admins and registered users
     */
    function _createUserGroups()
    {
    	// create admin group
    	$group1 = JTable::getInstance('Groups', 'TiendaTable');
    	$group1->group_name = $group1->group_description = 'Admin';
    	$group1->save();
    	
    	// create registered group
     	$group1 = JTable::getInstance('Groups', 'TiendaTable');
    	$group1->group_name = $group1->group_description = 'Registered';
    	$group1->save();
    }
}