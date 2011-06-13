<?php
/**
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

if ( !class_exists('Tienda') ) 
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );

Tienda::load( "TiendaHelperBase", 'helpers._base' );

class TiendaHelperEav extends TiendaHelperBase 
{
    /**
     * Gets an Attribute type based on its alias 
     * 
     * @param $alias
     * @return unknown_type
     */
    function getType( $alias )
    {
        static $sets;
        if (!is_array($sets)) { $sets = array(); }
        
        if (!isset($sets[$alias]))
        {
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $table = JTable::getInstance('EavAttributes', 'TiendaTable');
            $table->load(array('eavattribute_alias' => $alias));
            $sets[$alias] = $table->eavattribute_type;            
        }
        return $sets[$alias];
    }
    
	/**
	 * Get the Eav Attributes for a particular entity
	 * @param unknown_type $entity
	 * @param unknown_type $id
	 */
    function getAttributes( $entity, $id )
    {
        // $sets[$entity][$id]
        static $sets;
        if (!is_array($sets)) { $sets = array(); }
        
        if (!isset($sets[$entity][$id]))
        {
            JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
            $model = JModel::getInstance('EavAttributes', 'TiendaModel');
            $model->setState('filter_entitytype', $entity);
            $model->setState('filter_entityid', $id);
            $model->setState('filter_published', '1');            
            $sets[$entity][$id] = $model->getList();
        }
    	
        // Let the plugins change the list of custom fields
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onAfterGetCustomFields', array( &$sets[$entity][$id], $entity, $id ) );
        
    	return $sets[$entity][$id];
    }
    
    /**
     * Get the value of an attribute
     * @param EavAttribute $eav
     * @param string $entity_type
     * @param string $entity_id
     */
    function getAttributeValue($eav, $entity_type, $entity_id )
    {
        // $sets[$eav->eavattribute_type][$eav->eavattribute_id][$entity_type][$entity_id]
        static $sets;
        if (!is_array($sets)) { $sets = array(); }
        
        if (!isset($sets[$eav->eavattribute_type][$eav->eavattribute_id][$entity_type][$entity_id]))
        {
            Tienda::load('TiendaTableEavValues', 'tables.eavvalues');
            
            // get the value table
            $table = JTable::getInstance('EavValues', 'TiendaTable');
            // set the type based on the attribute
            $table->setType($eav->eavattribute_type);
            // load the value based on the entity id
            $keynames = array();
            $keynames['eavattribute_id'] = $eav->eavattribute_id; 
            $keynames['eaventity_id'] = $entity_id;
            $keynames['eaventity_type'] = $entity_type;
            
            $loaded = $table->load($keynames);
            
            if($loaded)
            {
                // Fetch the value from the value tables
                $value = $table->eavvalue_value;
            }
            else
            {
                $value = JRequest::getVar($eav->eavattribute_alias, null);
            }
            $sets[$eav->eavattribute_type][$eav->eavattribute_id][$entity_type][$entity_id] = $value;
        }
        
        return $sets[$eav->eavattribute_type][$eav->eavattribute_id][$entity_type][$entity_id];
    }
    
    /**
     * Show the correct edit field based on the eav type
     * @param EavAttribute $eav
     * @param unknown_type $value
     */
    function editForm($eav, $value = null)
    { 
    	// Type of the field
    	switch($eav->eavattribute_type)
    	{
    		case "bool":
    			Tienda::load('TiendaSelect', 'library.select');
    			return TiendaSelect::booleans($value, $eav->eavattribute_alias);
    			break;
    		case "datetime":
    			return JHTML::calendar( $value, $eav->eavattribute_alias, "eavattribute_alias_".$eav->eavattribute_id, "%Y-%m-%d %H:%M:%p" );
    			break;
    		case "text":
    			$editor = &JFactory::getEditor();
				$config = TiendaConfig::getInstance();
    			return $editor->display($eav->eavattribute_alias, $value, $config->get('eav_textarea_width', '300'), $config->get('eav_textarea_width', '200'), $config->get('eav_textarea_width', '50'), $config->get('eav_textarea_width', '20'));
    			break;
    		case "decimal":
    		case "int":	
    		case "varchar":
    		default:
    			return '<input type="text" name="'.$eav->eavattribute_alias.'" id="'.$eav->eavattribute_alias.'" value="'.$value.'" />';
    			break;
    	}
    	
    	return '';
    }
    
    /**
     * Show the field based on the eav type
     * @param EavAttribute $eav
     * @param unknown_type $value
     */
    function showValue($eav, $value = null)
    {
    	// Type of the field
    	switch($eav->eavattribute_type)
    	{
    		case "bool":
    			if($value)
    			{
    				echo JText::_('Yes');
    			}
    			else
    			{
    				echo JText::_('No');
    			}
    			break;
    		case "datetime":
    			return JHTML::date($value, TiendaConfig::getInstance()->get('date_format'));
    			break;
    		case "text":
    			$dispatcher =& JDispatcher::getInstance();
    			$item = new JObject();
		        $item->text = &$value;  
		        $item->params = array();
		        JPluginHelper::importPlugin('content'); 
		        $dispatcher->trigger('onPrepareContent', array (& $item, & $item->params, 0));
		        return $value;
    		case "decimal":
    		case "int":	
    			return self::number($value);
    		case "varchar":
    		default:
    			return $value;
    			break;
    	}
    	
    	return '';
    }
    
    /**
     * Show the edit form or the field value based on the eav status
     * @param EavAttribute $eav
     * @param unknown_type $value
     */
    function showField($eav, $value = null)
    {
    	$gid = JFactory::getUser()->gid;
    	if($gid >= 23)
    	{
    		$isAdmin = true;
    	}
    	else
    	{
    		$isAdmin = false;
    	}
    	
    	switch($eav->editable_by)
    	{
    		// No one
    		case "0":
    			return self::showValue($eav, $value);
    			break;
    		// Admin
    		case "1":
    			if($isAdmin)
    			{
    				return self::editForm($eav, $value);
    			}
    			else
    			{
    				return self::showValue($eav, $value);
    			}
    			break;
    		case "2":
    		default:
    			return self::editForm($eav, $value);
    			break;
    	}	
    	
    }
}