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

class plgTiendaEavFields extends TiendaPluginBase
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element   = 'eavfields';
    
    
	function plgTiendaEavFields(& $subject, $config) 
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
	 * Get the custom fields for the given entity
	 * @param string $entity
	 * @param int $id
	 */
	function getCustomFields($entity, $id)
	{
		$model = JModel::getInstance('EavAttributes', 'TiendaModel');
    	$model->setState('filter_entitytype', $entity);
    	$model->setState('filter_entityid', $id);
    	$model->setState('filter_published', '1');
    	
    	$eavs = $model->getList();
    	
    	$fields = array();
		foreach(@$eavs as $eav)
    	{
    		$key = $eav->eavattribute_alias;
    		
    		// get the value table
    		$table = JTable::getInstance('EavValues', 'TiendaTable');
    		// set the type based on the attribute
    		$table->setType($eav->eavattribute_type);
    	
    		// load the value based on the entity id
    		$keynames = array();
    		$keynames['eavattribute_id'] = $eav->eavattribute_id; 
    		$keynames['eaventity_id'] = $id;
    		$table->load($keynames);
    		
    		$value = $table->eavvalue_value;
   			
   			$fields[] = array('alias' => $key, 'value' => $value, 'label' => $eav->eavattribute_label);
   		}
   		
   		return $fields;
	}
	
}