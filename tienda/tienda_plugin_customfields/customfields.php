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

Tienda::load( 'TiendaPluginBase', 'library.plugins._base' );

class plgTiendaCustomFields extends TiendaPluginBase
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element   = 'customfields';
    
    
	function plgTiendaCustomFields(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}
	
	/**
	 * adds a tab with Extra Fields on products if needed
	 * Enter description here ...
	 * @param unknown_type $tabs
	 * @param unknown_type $row
	 */
	function onAfterDisplayProductFormTabs($tabs, $row)
	{
		$vars = new JObject();
        $vars->tabs = $tabs;
        $vars->row = $row;
        
        // Get extra fields for products
        $fields = $this->getCustomFields('products', $row->product_id);
        
        // If there are any extra fields, show them as an extra tab
        if(count($fields))
        {
        	$vars->fields = $fields;
        	$html = $this->_getLayout('product', $vars);
        	echo $html;
        }
	}
	
	/**
	 * Displays the custom fields on the site product view
	 * @param int $product_id
	 */
	function onAfterDisplayProduct( $product_id )
	{
		$vars = new JObject();
		
        // Get extra fields for products
        $fields = $this->getCustomFields('products', $product_id);
        
        // If there are any extra fields, show them as an extra tab
        if(count($fields))
        {
        	$vars->fields = $fields;
        	$html = $this->_getLayout('product_site', $vars);
        	echo $html;
        }
	}
	
	/**
	 * Get the custom fields for the given entity
	 * @param string $entity
	 * @param int $id
	 */
	function getCustomFields($entity, $id)
	{
		Tienda::load('TiendaModelEavAttributes', 'models.eavattributes');
		$model = JModel::getInstance('EavAttributes', 'TiendaModel');
    	$model->setState('filter_entitytype', $entity);
    	$model->setState('filter_entityid', $id);
    	$model->setState('filter_published', '1');
    	
    	$eavs = $model->getList();
    	
    	$fields = array();
		foreach(@$eavs as $eav)
    	{
    		$key = $eav->eavattribute_alias;
    		
    		$value = TiendaHelperEav::getAttributeValue($eav, $entity, $id);
   			
   			$fields[] = array('attribute' => $eav, 'value' => $value);
   		}
   		
   		return $fields;
	}
	
}