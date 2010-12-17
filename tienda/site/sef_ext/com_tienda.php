<?php
/**
 * <p>sh404SEF support for com_tienda component.</p>
 * 
 * @version	1.0.0
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

########################################################################################
 #---------------= standard plugin initialize function - don't change =---------------#
global $sh_LANG, $sefConfig;
$shLangName = '';
$shLangIso = '';
$title = array();
$shItemidString = '';
$dosef = shInitializePlugin( $lang, $shLangName, $shLangIso, $option);
if ($dosef == false) return;
 #---------------=  standard plugin initialize function - don't change =---------------#
#########################################################################################

//load our own lang iso since we dont user sh404sef translation
$langJoomla =& JFactory::getLanguage();
$langJoomla->load('com_tienda');
$langXplode = explode("-",$langJoomla->getTag()); 
$shLangIso = !empty($langXplode[0]) ? $langXplode[0] : "en";

//load tienda config for sh404sef
// Check the registry to see if our Tienda class has been overridden
if ( !class_exists('Tienda') ) 
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );

// load the config class
Tienda::load( 'TiendaConfig', 'defines' );
$tiendaConfig = TiendaConfig::getInstance();

// remove common URL from GET vars list, so that they don't show up as query string in the URL
shRemoveFromGETVarsList('option');
shRemoveFromGETVarsList('view');
shRemoveFromGETVarsList('lang');
if (!empty($Itemid))
  shRemoveFromGETVarsList('Itemid');
if (!empty($limit))
shRemoveFromGETVarsList('limit');
if (isset($limitstart))
  shRemoveFromGETVarsList('limitstart'); // limitstart can be zero

if (!function_exists( 'shSefGetTiendaProductName')) {
	function shSefGetTiendaProductName($id, $option, $shLangName, $shLangIso, $tiendaConfig) {
		if (empty($id)) return null;
    
    	$sefConfig = & shRouter::shGetConfig();     	
    	
	    $database =& JFactory::getDBO();
	    $query = "SELECT `product_id`, `product_sku`, `product_alias` FROM `#__tienda_products`";
	   	$query .= "\n WHERE `product_id` = ".$database->Quote( $id);
	    $database->setQuery( $query );
	
	    if (!shTranslateUrl($option, $shLangName)) {
		    $result = $database->loadObject( false );
		}else{
		    $result = $database->loadObject();
		}    	
    	
		//product not available
	    if(empty($result)) return JText::_('product').$sefConfig->replacement.$id;
	          	
	    $shName = '';
	    if ( $tiendaConfig->get('insert_product_id') ) {
	    	$shName .= $result->product_id;
	    }
	    
    	if ($tiendaConfig->get('insert_product_name') ) {
	    	 $shName .= (empty($shName) ? '':$sefConfig->replacement).$result->product_alias;  
	    }   
	    
	    if ( $tiendaConfig->get('insert_product_sku') ) {
	    	$product_sku = str_replace(" ","", trim($result->product_sku));
	    	$shName .= (empty($shName) ? '':$sefConfig->replacement).$product_sku;	    	
	    }

    	return $shName;
	}
}

if (!function_exists( 'shSefGetTiendaCategoryName')) {
	function shSefGetTiendaCategoryName($filter_category, $option, $shLangName, $tiendaConfig) {		
		if(empty($filter_category)) return null;
		static $cat = null;
		$shName = '';
	    $sefConfig = & shRouter::shGetConfig();
	  
	    $database =& JFactory::getDBO();
	      
	    $query = "SELECT `category_id`, `category_name` FROM #__tienda_categories";
	    $query .= "\n WHERE `category_id` = ".$database->Quote( $filter_category );
	    $database->setQuery( $query);
	    if (!shTranslateUrl($option, $shLangName)) {
	       $cat = $database->loadObject(false);
	    } else {
	       $cat = $database->loadObject();
	    }         		    
	  	    
		$tiendaConfigCat = $tiendaConfig->get('insert_categories');
	    $tiendaConfigCatInsertid =$tiendaConfig->get('insert_category_id');

	    if($tiendaConfigCatInsertid) {
	    	$shName .= $cat->category_id;
	    }
		   
	    if($tiendaConfigCat && !empty($cat)) {	    	    	
	    	$shName .= (empty($shName) ? '':$sefConfig->replacement).$cat->category_name;	    	
	    }
	    //TODO: need to add category name of parent categories
   	       
	    return $shName;    	
	}	
}

if (!function_exists( 'shSefGetTiendaManufactureryName')) {
	function shSefGetTiendaManufactureryName($filter_manufaturer, $option, $shLangName, $tiendaConfig) {
		if(empty($filter_manufaturer)) return null;
		static $manufacturer = null;
		$shName = '';
	    $sefConfig = & shRouter::shGetConfig();
	    if (is_null( $manufacturer )) {
	      	$database =& JFactory::getDBO();
	      
	      	$query = "SELECT `manufacturer_id`, `manufacturer_name` FROM #__tienda_manufacturers";
	       	$query .= "\n WHERE `manufacturer_id` = ".$database->Quote( $filter_manufaturer );
	      	$database->setQuery( $query);
	      	if (!shTranslateUrl($option, $shLangName)) {
	       		$manufacturer = $database->loadObject(false);
	      	} else {
	        	$manufacturer = $database->loadObject();
	      	}         		    
	    }
	    
		$tiendaConfigM = $tiendaConfig->get('insert_manufacturer_name');
	    $tiendaConfigMInsertid =$tiendaConfig->get('insert_manufacturer_id');

	    if($tiendaConfigMInsertid) {
	    	$shName .= $manufacturer->manufacturer_id;
	    }
		   
	    if($tiendaConfigM && !empty($manufacturer)) {	    	    	
	    	$shName .= (empty($shName) ? '':$sefConfig->replacement).$manufacturer->manufacturer_name;	    	
	    }
	    	    	       
	    return $shName;  
	}	
}

$task = isset($task) ? @$task : null;
$Itemid = isset($Itemid) ? @$Itemid : null;

if($tiendaConfig->get('insert_menu_title')) {
	$shTiendaName = shGetComponentPrefix($option);
	$shTiendaName = empty($shTiendaName) ?
			getMenuTitle($option, $task, $Itemid, null, $shTiendaName) : $shTiendaName;
	$shTiendaName = (empty($shTiendaName) || $shTiendaName == '/') ? 'SHOP':$shTiendaName;
	$title[] = $shTiendaName;
}

if($tiendaConfig->get('insert_shop_name')) {
	$shopname = $tiendaConfig->get('shop_name');
	$shopname = ucwords(strtolower($shopname));
	$title[] = str_replace(" ", "", $shopname);
}

switch($view):
	case 'products':						
		//multiple product inside the category
		if(!empty($filter_category)) {
			if($tiendaConfig->get('insert_categories')) {
				$title[] = shSefGetTiendaCategoryName($filter_category, $option, $shLangName, $tiendaConfig);
			}
			shRemoveFromGETVarsList('filter_category');
		} elseif(!empty($filter_manufacturer)){
			if($tiendaConfig->get('insert_manufacturer_name')) {
				$title[] = shSefGetTiendaManufactureryName($filter_category, $option, $shLangName, $tiendaConfig);
			}
			shRemoveFromGETVarsList('filter_manufacturer');
		} else {
			//single product
			if(!empty($id)) $title[] = shSefGetTiendaProductName( $id, $option, $shLangName, $shLangIso, $tiendaConfig);
		}		
		break;
	case 'manufacturers':			
		if(!empty($filter_manufacturer)){
			if($tiendaConfig->get('insert_manufacturer_name')) {
				$title[] = shSefGetTiendaManufactureryName($filter_category, $option, $shLangName, $tiendaConfig);
			}
		}
		shRemoveFromGETVarsList('filter_manufacturer');
		break;
	case 'dashboard':		
		$title[] = JText::_('dashboard');		
		break;	
	case 'accounts':
		$title[] = JText::_('accounts');		
		break;
	case 'orders':		
		$title[] = JText::_('orders');		
		break;
	case 'subscriptions':		
		$title[] = JText::_('subscriptions');		
		break;
	case 'carts':		
		$title[] = JText::_('carts');		
		break;
	case 'check':		
		$title[] = JText::_('checks');		
		break;
	case 'addresses':		
		$title[] = JText::_('addresses');		
		break;
	default:
		$dosef = false;
		break;
endswitch;

if(!empty($task) && $task != 'validate' && $task !='reviewHelpfullness') {
  	shRemoveFromGETVarsList('task');
}
if(!empty($layout)) {
	shRemoveFromGETVarsList('layout');
}
if(isset($Itemid)) {
  	shRemoveFromGETVarsList('Itemid');
}
if(!empty($id)) {
  	shRemoveFromGETVarsList('id');
}
if(!empty($rangeselected)) {
	shRemoveFromGETVarsList('rangeselected');
}
if(!empty($filter_price_from)) {
	shRemoveFromGETVarsList('filter_price_from');
}
if(!empty($filter_price_to)) {
	shRemoveFromGETVarsList('filter_price_to');
}
if(!empty($filter_category)) {
	shRemoveFromGETVarsList('filter_category');
}

######################################################################################
 #---------------= standard plugin finalize function - don't change =---------------#
if ($dosef)
{
   $string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString, 
      (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null), 
      (isset($shLangName) ? @$shLangName : null));
}  
#---------------=  standard plugin finalize function - don't change =---------------#
######################################################################################
?>