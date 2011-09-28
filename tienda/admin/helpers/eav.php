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
            switch( $table->eavattribute_type )
            {
            	case 'hidden':
            			$type ='varchar';
            			break;
            	default:
            			$type = $table->eavattribute_type;
            			break;
            }
            $sets[$alias] = $type;            
        }
        return $sets[$alias];
    }
    
	/**
	 * Get the Eav Attributes for a particular entity
	 * @param unknown_type $entity
	 * @param unknown_type $id
	 * @param boolean $only_enabled
	 */
    function getAttributes( $entity, $id, $only_enabled = false, $editable_by = '' )
    {
        // $sets[$entity][$id]
        static $sets;
        if (!is_array($sets)) { $sets = array(); }
        
        if( is_array( $editable_by ) )
        	$editable_by = implode( ',', $editable_by );
        else
	        if( !strlen( $editable_by ) )
  	      	$editable_by = '-1';
        
        if (!isset( $sets[$entity][$id][$editable_by] ) )
        {
            JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
            $model = JModel::getInstance('EavAttributes', 'TiendaModel');
            $model->setState('filter_entitytype', $entity);
            $model->setState('filter_entityid', $id);
            $model->setState('filter_enabled', '1');
            if( $editable_by != '-1' )
            	$model->setState( 'filter_editable',$editable_by );
            	
            $sets[$entity][$id][$editable_by] = $model->getList();
        }
    	
        // Let the plugins change the list of custom fields
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onAfterGetCustomFields', array( &$sets[$entity][$id][$editable_by], $entity, $id ) );
        
    	return $sets[$entity][$id][$editable_by];
    }
    
    /**
     * Get the value of an attribute
     * @param EavAttribute $eav
     * @param string $entity_type
     * @param string $entity_id
     * @param bool $no_post - only value from db will be used
     * @param bool $cache_values - If the values should be cached in the static array
     */
    function getAttributeValue($eav, $entity_type, $entity_id, $no_post = false, $cache_values = true )
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
            	if( !$no_post ) // we allowed using post variables
            	{            		
								$value = JRequest::getVar($eav->eavattribute_alias, null);
            	}
							else
							{
								$value = null;
							}
            }
            if( $value !== null && $cache_values )
	            $sets[$eav->eavattribute_type][$eav->eavattribute_id][$entity_type][$entity_id] = $value;
	            
        }
				
				if( $cache_values )
	        return @$sets[$eav->eavattribute_type][$eav->eavattribute_id][$entity_type][$entity_id];
	      else
		      return @$value;
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
    			return JHTML::calendar( $value, $eav->eavattribute_alias, "eavattribute_alias", "%Y-%m-%d %H:%M:%p" );
    			break;
    		case "text":
    			$editor = &JFactory::getEditor();
    			return $editor->display($eav->eavattribute_alias, $value, '300', '200', '50', '20');
    			break;
    		case "hidden":
    			return '<input type="hidden" name="'.$eav->eavattribute_alias.'" id="'.$eav->eavattribute_alias.'" value="'.$value.'" />';
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
    			return JHTML::date(date('Y-m-d H:i:s', strtotime( $value)), TiendaConfig::getInstance()->get('date_format'));
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
    			return self::number( $value );
    		case "int":	
    			return self::number( $value, array( 'num_decimals' => 0 ) );
    		case "hidden":
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
