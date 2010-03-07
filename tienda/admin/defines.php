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
    static $_version 		= '0.3.0';
    static $_versiontype    = 'community';
    static $_copyrightyear 	= '2010';
    static $_name 			= 'tienda';

    /**
     * Get the version
     */
    public static function getVersion()
    {
        $version = self::$_version." ".JText::_( ucfirst(self::$_versiontype) );
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
	 * Method to dump the structure of a variable for debugging purposes
	 *
	 * @param	mixed	A variable
	 * @param	boolean	True to ensure all characters are htmlsafe
	 * @return	string
	 * @since	1.5
	 * @static
	 */
	public static function dump( &$var, $htmlSafe = true ) {
		$result = print_r( $var, true );
		return '<pre>'.( $htmlSafe ? htmlspecialchars( $result ) : $result).'</pre>';
	}
}

	// TODO Merge this class into base defines
class TiendaConfig extends Tienda
{

	var $show_linkback						= '1';
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
	var $display_shipping_tax               = '1';
	var $initial_order_state               = '15';
	var $defaultShippingMethod               = '2';

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

	/**
	 *
	 * @return unknown_type
	 */
	function &getFromXML( $needle='version' )
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.archive');
		jimport('joomla.filesystem.path');
		jimport('joomla.installer.installer' );
		jimport('joomla.installer.helper' );

		$success = "1.50";
		$pkg = strtolower( "com_Tienda" );
		// $row = new JObject();

		/* Get the component base directory */
		$adminDir = JPATH_ADMINISTRATOR .DS. 'components';
		$siteDir = JPATH_SITE .DS. 'components';

		/* Get the component folder and list of xml files in folder */
		$folder = $adminDir.DS.$pkg;
		if (JFolder::exists($folder)) {
			$xmlFilesInDir = JFolder::files($folder, '.xml$');
		} else {
			$folder = $siteDir.DS.$pkg;
			if (JFolder::exists($folder)) {
				$xmlFilesInDir = JFolder::files($folder, '.xml$');
			} else {
				$xmlFilesInDir = null;
			}
		}

		//if there were any xml files found
		if (count($xmlFilesInDir))
		{
			foreach ($xmlFilesInDir as $xmlfile)
			{

				if ($data = JApplicationHelper::parseXMLInstallFile($folder.DS.$xmlfile)) {
					foreach($data as $key => $value) {
						// $row->$key = $value;
						if (strtolower($key) == strtolower($needle)) {
							$success = $value;
						}
					}
				}
			}
		}

		return $success;
	}

	/**
	 *
	 * @param $fieldname
	 * @return unknown_type
	 */
	function getFieldname( $fieldname, $option='', $view='', $layout='' )
	{
		// use combo of option, view, and layout to find specific variable
		$o = $option ? $option : strtolower( "com_Tienda" );
		$v = $view ? $view : JRequest::getVar( 'view', 'default' );
		$l = $layout ? $layout : JRequest::getVar( 'layout', 'default' );
		$return = "{$o}_{$v}_{$l}_{$fieldname}";
		return $return;
	}
}
?>