<?php
/**
* @version		0.1.0
* @package		Tienda
* @copyright	Copyright (C) 2009 DT Design Inc. All rights reserved.
* @license		GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
* @link 		http://www.dioscouri.com
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class Tienda extends JObject
{
    static $_version 		= '0.5.1';
    static $_build          = 'r481';
    static $_versiontype    = 'community';
    static $_copyrightyear 	= '2010';
    static $_name 			= 'tienda';
    static $_min_php		= '5.2';

    /**
     * Get the version
     */
    public static function getVersion()
    {
        $version = self::$_version." ".JText::_( ucfirst(self::$_versiontype) )." ".self::$_build;
        return $version;
    }

    /**
     * Get the copyright year
     */
    public static function getCopyrightYear()
    {
        return self::$_copyrightyear;
    }

    /**
     * Get the Name
     */
    public static function getName()
    {
        return self::$_name;
    }

    /**
     * Get the Minimum Version of Php
     */
    public static function getMinPhp()
    {
	    //get version from PHP. Note this should be in format 'x.x.x' but on some systems will look like this: eg. 'x.x.x-unbuntu5.2'
    	$phpV = self::getServerPhp();
    	$minV = self::$_min_php; 
    	$passes = false;
	
	    if ($phpV[0] >= $minV[0]) {
	        if (empty($minV[2]) || $minV[2] == '*') {
	            $passes = true;
	        } elseif ($phpV[2] >= $minV[2]) {
	            if (empty($minV[4]) || $minV[4] == '*' || $phpV[4] >= $minV[4]) {
	                $passes = true;
	            }
	        }
	    }
	    //if it doesn't pass raise a Joomla Notice
	    if (!$passes) :
	    	JError::raiseNotice('VERSION_ERROR',sprintf(JText::_('ERROR_PHP_VERSION'),$minV,$phpV));
	    endif;

	    //return minimum PHP version
	    return self::$_min_php;
    }
    
    public static function getServerPhp()
    {
    	return PHP_VERSION;
    }

	/**
     * Get the URL to the folder containing all media assets
     *
     * @param string	$type	The type of URL to return, default 'media'
     * @return 	string	URL
     */
    public static function getURL($type = 'media')
    {
    	$url = '';

    	switch($type)
    	{
    		case 'media' :
    			$url = JURI::root(true).'/media/com_tienda/';
    			break;
    		case 'css' :
    			$url = JURI::root(true).'/media/com_tienda/css/';
    			break;
    		case 'images' :
    			$url = JURI::root(true).'/media/com_tienda/images/';
    			break;
    		case 'js' :
    			$url = JURI::root(true).'/media/com_tienda/js/';
    			break;
    		case 'categories_images' :
    			$url = JURI::root(true).'/images/com_tienda/categories/';
    			break;
    		case 'categories_thumbs' :
    			$url = JURI::root(true).'/images/com_tienda/categories/thumbs/';
    			break;
    		case 'products_images' :
    			$url = JURI::root(true).'/images/com_tienda/products/';
    			break;
    		case 'products_thumbs' :
    			$url = JURI::root(true).'/images/com_tienda/products/thumbs/';
    			break;
            case 'products_files' :
                $url = JURI::root(true).'/images/com_tienda/files/';
                break;
    		case 'manufacturers_images' :
    			$url = JURI::root(true).'/images/com_tienda/manufacturers/';
    			break;
    		case 'manufacturers_thumbs' :
    			$url = JURI::root(true).'/images/com_tienda/manufacturers/thumbs/';
    			break;
    	}

    	return $url;
    }

	/**
     * Get the path to the folder containing all media assets
     *
     * @param 	string	$type	The type of path to return, default 'media'
     * @return 	string	Path
     */
    public static function getPath($type = 'media')
    {
    	$path = '';

    	switch($type)
    	{
    		case 'media' :
    			$path = JPATH_SITE.DS.'media'.DS.'com_tienda';
    			break;
    		case 'css' :
    			$path = JPATH_SITE.DS.'media'.DS.'com_tienda'.DS.'css';
    			break;
    		case 'images' :
    			$path = JPATH_SITE.DS.'media'.DS.'com_tienda'.DS.'images';
    			break;
    		case 'js' :
    			$path = JPATH_SITE.DS.'media'.DS.'com_tienda'.DS.'js';
    			break;
    		case 'categories_images' :
    			$path = JPATH_SITE.DS.'images'.DS.'com_tienda'.DS.'categories';
    			break;
    		case 'categories_thumbs' :
    			$path = JPATH_SITE.DS.'images'.DS.'com_tienda'.DS.'categories'.DS.'thumbs';
    			break;
    		case 'products_images' :
    			$path = JPATH_SITE.DS.'images'.DS.'com_tienda'.DS.'products';
    			break;
    		case 'products_thumbs' :
    			$path = JPATH_SITE.DS.'images'.DS.'com_tienda'.DS.'products'.DS.'thumbs';
    			break;
            case 'products_files' :
                $path = JPATH_SITE.DS.'images'.DS.'com_tienda'.DS.'files';
                break;
    		case 'manufacturers_images' :
    			$path = JPATH_SITE.DS.'images'.DS.'com_tienda'.DS.'manufacturers';
    			break;
    		case 'manufacturers_thumbs' :
    			$path = JPATH_SITE.DS.'images'.DS.'com_tienda'.DS.'manufacturers'.DS.'thumbs';
    			break;
    	}

    	return $path;
    }

    /**
     * Method to intelligently load class files in the Tienda framework
     *
     * @param string $classname   The class name
     * @param string $filepath    The filepath ( dot notation )
     * @param array  $options
     * @return boolean
     */
    public static function load( $classname, $filepath, $options=array( 'site'=>'admin', 'type'=>'components', 'ext'=>'com_tienda' ) ) 
    {
        $classname = strtolower( $classname );
        $classes = JLoader::register();
        if ( class_exists($classname) || array_key_exists( $classname, $classes ) ) 
        {
            // echo "$classname exists<br/>";
            return true;
        }
        
        static $paths;

        if (empty($paths)) 
        {
            $paths = array();
        }
        
        if (empty($paths[$classname]) || !is_file($paths[$classname]))
        {
            // find the file and set the path
            if (!empty($options['base']))
            {
                $base = $options['base'];
            }
                else
            {
                // recreate base from $options array
                switch ($options['site'])
                {
                    case "site":
                        $base = JPATH_SITE.DS;
                        break;
                    default:
                        $base = JPATH_ADMINISTRATOR.DS;
                        break;
                }
                
                $base .= (!empty($options['type'])) ? $options['type'].DS : '';
                $base .= (!empty($options['ext'])) ? $options['ext'].DS : '';
            }
            
            $paths[$classname] = $base.str_replace( '.', DS, $filepath ).'.php';
        }
        
        // if invalid path, return false
        if (!is_file($paths[$classname]))
        {
            // echo "file does not exist<br/>";
            return false;
        }
        
        // if not registered, register it
        if ( !array_key_exists( $classname, $classes ) ) 
        {
            // echo "$classname not registered, so registering it<br/>";
            JLoader::register( $classname, $paths[$classname] );
            return true;
        }
        return false;
    }
    
    /**
     * Intelligently loads instances of classes in Tienda framework
     * 
     * Usage: $object = Tienda::getClass( 'TiendaHelperCarts', 'helpers.carts' );
     * Usage: $suffix = Tienda::getClass( 'TiendaHelperCarts', 'helpers.carts' )->getSuffix();
     * Usage: $categories = Tienda::getClass( 'TiendaSelect', 'library.select' )->category( $selected );
     * 
     * @param string $classname   The class name
     * @param string $filepath    The filepath ( dot notation )
     * @param array  $options
     * @return object of requested class (if possible), else a new JObject
     */
    public function getClass( $classname, $filepath='controller', $options=array( 'site'=>'admin', 'type'=>'components', 'ext'=>'com_tienda' )  )
    {
        if (Tienda::load( $classname, $filepath, $options ))
        {
            $instance = new $classname();
            return $instance;
        }
        
        $instance = new JObject();
        return $instance;
    }
    
	/**
	 * Method to dump the structure of a variable for debugging purposes
	 *
	 * @param	mixed	A variable
	 * @param	boolean	True to ensure all characters are htmlsafe
	 * @return	string
	 * @since	1.5
	 * @static
	 */
	public static function dump( $var, $ignore_underscore = false, $htmlSafe = true ) 
	{
	    if (!$ignore_underscore)
	    {
            $result = print_r( $var, true );
            return '<pre>'.( $htmlSafe ? htmlspecialchars( $result ) : $result).'</pre>';
	    }
	    
	    if (!is_object($var) && !is_array($var))
	    {
            $result = print_r( $var, true );
            return '<pre>'.( $htmlSafe ? htmlspecialchars( $result ) : $result).'</pre>';
	    }
	    
	    // TODO do a recursive remove of underscored keys
	    
	    if (is_object($var))
	    {
	        $keys = get_object_vars($var);
	        foreach ($keys as $key=>$value)
	        {
	            if (substr($key, 0, 1) == '_')
	            {
	                unset($var->$key);
	            }
	        }
            $result = print_r( $var, true );
            return '<pre>'.( $htmlSafe ? htmlspecialchars( $result ) : $result).'</pre>';
	    }
	    
	    if (is_array($var))
        {
            foreach ($var as $key=>$value)
            {
                if (substr($key, 0, 1) == '_')
                {
                    unset($var[$key]);
                }
            }
            $result = print_r( $var, true );
            return '<pre>'.( $htmlSafe ? htmlspecialchars( $result ) : $result).'</pre>';
        }

	}
}

/**
 * 
 * @author Rafael Diaz-Tushman
 *
 */
class TiendaConfig extends JObject
{

	// View Options
	var $show_linkback						= '1';
	var $amigosid                           = '';
	var $page_tooltip_dashboard_disabled	= '0';
	var $page_tooltip_config_disabled		= '0';
	var $page_tooltip_tools_disabled		= '0';
	var $page_tooltip_accounts_disabled		= '0';
	var $page_tooltip_payouts_disabled		= '0';
	var $page_tooltip_logs_disabled			= '0';
	var $page_tooltip_payments_disabled		= '0';
	var $page_tooltip_commissions_disabled	= '0';
	var $page_tooltip_users_view_disabled   = '0';
	var $article_default					= '0';
	var $article_potential					= '0';
	var $article_unapproved					= '0';
	var $article_disabled					= '0';
	var $article_application				= '0';
	var $approve_new						= '0';
	var $enable_unregistered				= '0';
	var $enable_payouttype_choice			= '0';
	var $company_information				= null;
	var $display_dashboard_thismonth_commissions	= '1';
	var $display_dashboard_thismonth_logs	= '1';
	var $display_dashboard_conversions		= '1';
	var $display_dashboard_statistics		= '1';
	var $currency_num_decimals				= '2';
	var $currency_thousands					= ',';
	var $currency_decimal					= '.';
	var $currency_symbol_pre				= '$';
	var $currency_symbol_post				= null;
	var $default_currencyid					= '1'; // USD
	var $login_url_redirect					= 'index.php';
	var $logout_url_redirect				= 'index.php';
	var $login_redirect						= '1';
	var $orderstates_csv                    = '2, 3, 5, 17';
	// Other Info
	var $display_shipping_tax               = '1';
	var $initial_order_state                = '15';
	var $pending_order_state                = '1';
	var $defaultShippingMethod              = '2';
	var $guest_checkout_enabled             = '1';
	// Shop Info
	var $shop_enabled                       = '1';
	var $shop_name							= '';
	var $shop_company_name					= '';
	var $shop_address_1						= '';
	var $shop_address_2						= '';
	var $shop_city							= '';
	var $shop_country						= '';
	var $shop_zone							= '';
	var $shop_zip							= '';
	var $shop_tax_number_1					= '';
	var $shop_tax_number_2					= '';
	var $shop_phone							= '';
	var $shop_owner_name					= '';
	// Default Dimensions for the images
	var $product_img_height 		        = 128;
	var $product_img_width 			        = 96;
	var $category_img_height 		        = 48;
	var $category_img_width			        = 48;
	var $manufacturer_img_width		        = 128;
	var $manufacturer_img_height	        = 96;
	// Unit measures
	var $dimensions_unit					= 'cm';
	var $weight_unit						= 'kg';
    var $date_format                        = '%a, %d %b %Y, %I:%M%p';
    var $use_default_category_image         = '1';
    var $lightbox_width                     = '';
    var $require_terms                      = '0';
    var $article_terms                      = '';
    var $order_number_prefix                = '';
	var $article_shipping                   = '0';
	var $display_prices_with_shipping       = '0';
	var $display_prices_with_tax            = '0';
    var $display_taxclass_lineitems         = '0';
    var $addtocartaction                    = 'redirect'; 	
    var $cartbutton                         = 'image';
    var $include_root_pathway               = '0';
    var $display_tienda_pathway             = '1';
    var $display_out_of_stock               = '1';
    var $global_handling                    = '';
    var $default_tax_geozone                = '';
	
	/**
	 * constructor
	 * @return void
	 */
	function __construct() {
		parent::__construct();

		$this->setVariables();
	}

	/**
	 * Returns the query
	 * @return string The query to be used to retrieve the rows from the database
	 */
	function _buildQuery()
	{
		$query = "SELECT * FROM #__tienda_config";
		return $query;
	}

	/**
	 * Retrieves the data
	 * @return array Array of objects containing the data from the database
	 */
	function getData() {
		// load the data if it doesn't already exist
		if (empty( $this->_data )) {
			$database = &JFactory::getDBO();
			$query = $this->_buildQuery();
			$database->setQuery( $query );
			$this->_data = $database->loadObjectList();
		}

		return $this->_data;
	}

	/**
	 * Set Variables
	 *
	 * @acces	public
	 * @return	object
	 */
	function setVariables() {
		$success = false;

		if ( $data = $this->getData() )
		{
			for ($i=0; $i<count($data); $i++)
			{
				$title = $data[$i]->config_name;
				$value = $data[$i]->value;
				if (!empty($title)) {
					$this->$title = $value;
				}
			}

			$success = true;
		}

		return $success;
	}

	/**
	 * Get component config
	 *
	 * @acces	public
	 * @return	object
	 */
	function &getInstance() {
		static $instance;

		if (!is_object($instance)) {
			$instance = new TiendaConfig();
		}

		return $instance;
	}
}
?>