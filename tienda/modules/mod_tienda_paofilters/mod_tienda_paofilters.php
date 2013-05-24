<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

// if DSC is not loaded all is lost anyway
if (!defined('_DSC')) { return; }

// Check the registry to see if our Tienda class has been overridden
if ( !class_exists('Tienda') ) { 
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR."/components/com_tienda/defines.php" );
}
    
require_once( dirname(__FILE__).'/helper.php' );

// include lang files
$lang = JFactory::getLanguage();
$lang->load( 'com_tienda', JPATH_BASE );
$lang->load( 'com_tienda', JPATH_ADMINISTRATOR );

$helper = new modTiendaPAOFiltersHelper( $params );

$app = JFactory::getApplication();
$model = JModel::getInstance( 'Products', 'TiendaModel' );
$ns = $app->getName().'::'.'com.tienda.model.'.$model->getTable()->get('_suffix');
$filter_category = $app->getUserStateFromRequest($ns.'.category', 'filter_category', '', 'int');

$category_ids = array();
if ($filter_category) {
    $category_ids = array( $filter_category );
} 

$itemid = JRequest::getInt('Itemid');
$session = JFactory::getSession();
$app = JFactory::getApplication();
$ns = $app->getName().'::'.'com.tienda.products.state.'.$itemid;
$session_state = $session->get( $ns );

$helper->state = $session_state; 

$items = $helper->getItems( $category_ids );
FB::log($items, 'modTiendaPAOFilters.items');
FB::log($helper->state, 'modTiendaPAOFilters.$helper->state');

$filter_pao_id_groups = $helper->state['filter_pao_id_groups'];
$show_reset = false;
if (!empty($filter_pao_id_groups))
{
    foreach ($filter_pao_id_groups as $filter_pao_id_group)
    {
        if (!empty($filter_pao_id_group) && is_array($filter_pao_id_group))
        {
            $filter_id_set = implode("', '", $filter_pao_id_group);
            
            if (!empty($filter_id_set))
            {
                $show_reset = true;
                break;
            }
        }
    }
}

$optionnames = array();
if ($show_reset) 
{
    // a filter exists, so get its name to display in the option-group
    $model = Tienda::getClass('TiendaModelProductAttributeOptions', 'models.productattributeoptions');
    foreach ($filter_pao_id_groups as $key=>$filter_pao_id_group)
    {
        if (!empty($filter_pao_id_group) && is_array($filter_pao_id_group))
        {
            $optionnames[$key] = $model->getNames($filter_pao_id_group);            
        }
    }
}

FB::log($filter_pao_id_groups, '$filter_pao_id_groups');
FB::log($optionnames, '$$optionnames');

require JModuleHelper::getLayoutPath('mod_tienda_paofilters', $params->get('layout', 'default'));
