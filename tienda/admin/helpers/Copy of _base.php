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

if ( !class_exists('Tienda') )
JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );

Tienda::load( 'Tienda', 'defines' );

class TiendaHelperBase extends DSCHelper
{
		static $added_strings = null;
	
	/**
	 * constructor
	 * make it protected where necessary
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Returns a reference to the a Helper object, only creating it if it doesn't already exist
	 *
	 * @param type 		$type 	 The helper type to instantiate
	 * @param string 	$prefix	 A prefix for the helper class name. Optional.
	 * @return helper The Helper Object
	 */
		/**
	 * Returns a reference to the a Helper object, only creating it if it doesn't already exist
	 *
	 * @param type 		$type 	 The helper type to instantiate
	 * @param string 	$prefix	 A prefix for the helper class name. Optional.
	 * @return helper The Helper Object
	 */
	public static function getInstance( $type = 'Base', $prefix = 'TiendaHelper' )
	{
		static $instances;

		if (!isset( $instances )) {
			$instances = array();
		}

		$type = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);

		// The Base helper is in _base.php, but it's named TiendaHelperBase
		if(strtolower($type) == 'Base'){
			$helperClass = $prefix.ucfirst($type);
			$type = '_Base';
		}

		$helperClass = $prefix.ucfirst($type);

		if (empty($instances[$helperClass]))
		{

			if (!class_exists( $helperClass ))
			{
				jimport('joomla.filesystem.path');
				if($path = JPath::find(TiendaHelperBase::addIncludePath(), strtolower($type).'.php'))
				{
					require_once $path;

					if (!class_exists( $helperClass ))
					{
						JError::raiseWarning( 0, 'Helper class ' . $helperClass . ' not found in file.' );
						return false;
					}
				}
				else
				{
					JError::raiseWarning( 0, 'Helper ' . $type . ' not supported. File not found.' );
					return false;
				}
			}

			$instance = new $helperClass();
				
			$instances[$helperClass] = & $instance;
		}

		return $instances[$helperClass];
	}

	/**
	 * Formats and converts a number according to currency rules
	 * As of v0.5.0 is a wrapper
	 *
	 * @param unknown_type $amount
	 * @param unknown_type $currency
	 * @return unknown_type
	 */
	public static function currency($amount, $currency='', $options='')
	{
		$currency_helper =& TiendaHelperBase::getInstance( 'Currency' );
		$amount = $currency_helper->_($amount, $currency, $options);
		return $amount;
	}

	/**
	 * Add a directory where TiendaHelper should search for helper types. You may
	 * either pass a string or an array of directories.
	 *
	 * @access	public
	 * @param	string	A path to search.
	 * @return	array	An array with directory elements
	 * @since 1.5
	 */
	/*function addIncludePath( $path=null )
	{
		static $tiendaHelperPaths;

		if (!isset($tiendaHelperPaths)) {
			$tiendaHelperPaths = array( dirname( __FILE__ ) );
		}

		// just force path to array
		settype($tiendaHelperPath, 'array');

		if (!empty( $tiendaHelperPath ) && !in_array( $tiendaHelperPath, $tiendaHelperPaths ))
		{
			// loop through the path directories
			foreach ($tiendaHelperPath as $dir)
			{
				// no surrounding spaces allowed!
				$dir = trim($dir);

				// add to the top of the search dirs
				// so that custom paths are searched before core paths
				array_unshift($tiendaHelperPaths, $dir);
			}
		}
		return $tiendaHelperPaths;
	}*/

	


	/**
	 * Nicely format a number
	 *
	 * @param $number
	 * @return unknown_type
	 */
	public static function number($number, $options='' )
	{
		static $default_currency = null;
		$config = Tienda::getInstance();
		$options = (array) $options;
  	  if ( !$default_currency ) {
		JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
		$model = JModel::getInstance('Currencies', 'TiendaModel');
     	$model->SetId(Tienda::getInstance()->get('default_currencyid', '1'));
     	$default_currency = $model->getItem();
		}
		
		$thousands = isset($options['thousands']) ? $options['thousands'] : $default_currency->thousands_separator;
		$decimal = isset($options['decimal']) ? $options['decimal'] : $default_currency->decimal_separator;
		$num_decimals = isset($options['num_decimals']) ? $options['num_decimals'] : $default_currency->currency_decimals;
		$return = number_format($number, $num_decimals, $decimal, $thousands);
		return $return;
	}

	



	
	
	/**
	 * includeJQueryUI function.
	 *
	 * @access public
	 * @return void
	 */
	function includeJQueryUI()
	{
		self::includeJQuery();
		JHTML::_('script', 'jquery-ui-1.7.2.min.js', 'media/com_tienda/js/');
		JHTML::_('stylesheet', 'jquery-ui.css', 'media/com_tienda/css/');
	}

	/**
	 * includeJQuery function.
	 *
	 * @access public
	 * @return void
	 */
	function includeJQuery()
	{
		JHTML::_('script', 'jquery-1.3.2.min.js', 'media/com_tienda/js/');
	}

	/**
	 * Include JQueryMultiFile script
	 */
	function includeMultiFile()
	{
		JHTML::_('script', 'Stickman.MultiUpload.js', 'media/com_tienda/js/');
		JHTML::_('stylesheet', 'Stickman.MultiUpload.css', 'media/com_tienda/css/');
	}

	

	/**
	 * Set the document format
	 */
	function setFormat( $format = 'html' )
	{
		// 	Default to raw output
		$doc = JFactory::getDocument();
		$document = JDocument::getInstance($format);

		$doc = $document;
	}

	/**
	 * convert Local data to GMT data
	 */
	function local_to_GMT_data( $local_data )
	{
		$GMT_data=$local_data ;
		if(!empty($local_data))
		{
			$config = JFactory::getConfig();
			$offset = $config->getValue('config.offset');
			$offset=0-$offset;
			$date = date_create($local_data);
			date_modify($date,  $offset.' hour');
			$GMT_data= date_format($date, 'Y-m-d H:i:s');
		}
		return $GMT_data;
	}

	/**
	 * convert GMT data to Local data
	 */
	function GMT_to_local_data( $GMT_data )
	{
		$local_data=$GMT_data ;
		if(!empty($local_data))
		{
			$config = JFactory::getConfig();
			$offset = $config->getValue('config.offset');
			$date = date_create($GMT_data);
			date_modify($date,  $offset.' hour');
			$local_data= date_format($date, 'Y-m-d H:i:s');
		}
		return $local_data;
	}

	/**
	 * Generates a validation message
	 *
	 * @param unknown_type $text
	 * @param unknown_type $type
	 * @return unknown_type
	 */
	function validationMessage( $text, $type='fail' )
	{
		switch (strtolower($type))
		{
			case "success":
				$src = Tienda::getUrl( 'images' ).'accept_16.png';
				$html = "<div class='tienda_validation'><img src='$src' alt='".JText::_('COM_TIENDA_SUCCESS')."'><span class='validation-success'>".JText::_( $text )."</span></div>";
				break;
			default:
				$src = Tienda::getUrl( 'images' ).'remove_16.png';
				$html = "<div class='tienda_validation'><img src='$src' alt='".JText::_('COM_TIENDA_ERROR')."'><span class='validation-fail'>".JText::_( $text )."</span></div>";
				break;
		}
		return $html;
	}

	/**
	 * Generates a new secret key for Tienda
	 * 
	 * @param $length		Length of the word
	 * @return 					Secret key as a string
	 */
	function generateSecretWord( $length = 32 )
	{
		$salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+}{|:<>?,. ";
		$len = strlen( $salt );
		$sw = '';

		for ($i = 0; $i < $length; $i++ )
			$sw .= $salt[mt_rand( 0, $len -1 )];

		return $sw; 
	}

	/**
	 * Method which gets a correct time of beginning of a day with respect to the current time zone
	 * 
	 * @param $date	Joomla JDate object
	 * 
	 * @return Correct Datetime with respect to the current time zone
	 */
	function getCorrectBeginDayTime( $date )
	{
		$date_gmt = $date;
		if( is_object( $date_gmt ) )
			$date_gmt = $date_gmt->toFormat( "%Y-%m-%d %H:%M:%S" );

		$date_local = TiendaHelperBase::GMT_to_local_data( ( string ) $date_gmt );
		$startdate_gmt = date_format( date_create( $date_local ), 'Y-m-d 00:00:00' );
		return TiendaHelperBase::local_to_GMT_data( $startdate_gmt );
		
	}

	/**
	 * Method to add translation strings to JS translation object
	 * 
	 * @param $strings	Associative array with list of strings to translate
	 * 
	 */
	function addJsTranslationStrings( $strings )
	{
		if( self::$added_strings === null )
			self::$added_strings = array();
		
		JHTML::_('script', 'tienda_lang.js', 'media/com_tienda/js/');  
		$js_strings = array();
		for( $i = 0, $c = count( $strings ); $i < $c; $i++ )
		{
			if( in_array( strtoupper( $strings[$i] ), self::$added_strings ) === false )
			{
				$js_strings []= '"'.strtoupper( $strings[$i] ).'":"'.JText::_( $strings[$i] ).'"';
				self::$added_strings []= strtoupper( $strings[$i] );
			}
		}
		
		if( count( $js_strings ) )
		{
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration( 'Joomla.JText.load({'.implode( ',', $js_strings ).'});' );
		}
	}
}