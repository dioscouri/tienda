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

Tienda::load( 'TiendaModelBase', 'models._base' );

class TiendaModelEav extends TiendaModelBase
{	
	function __construct($config = array())
    {
    	//set the model state
		if (array_key_exists('state', $config))  
		{
			// Search for eav special states
			if (!array_key_exists('_eav', $config['state']))  
			{
				$config['state']['_eav'] = new JObject();
			}
		}
		else
		{
			// Add the _eav state
			$config['state']['_eav'] = new JObject();
		}
        parent::__construct($config);
    }
    
    /**
     * Sets a Eav special state
     * 
     * @param $property
     * @param $value
     */
    public function setEavState($property, $value = null)
    {
    	return $this->_state->_eav->set($property, $value);
    }
    
    /**
     * Gets a Eav special state
     * @param $property
     */
    public function getEavState($property = null)
    {
    	return $property === null ? $this->getState('_eav') : $this->getState('_eav')->get($property);
    }
    
    public function getItem( $emptyState = true, $getEav = true )
    {
     	if (empty( $this->_item ))
        {
            $item = parent::getItem( $emptyState );
            if (empty($item))
            {
                return $item;
            }
	
            // load extra fields?
	    	if($getEav)
	    	{
	    		Tienda::load('TiendaModelEavAttributes', 'models.eavattributes');
	    		Tienda::load('TiendaTableEavValues', 'tables.eavvalues');
	    		
	    		$entity = $this->getTable()->get('_suffix');
	    		
	    		$model = JModel::getInstance('EavAttributes', 'TiendaModel');
	    		$model->setState('filter_entitytype', $entity);
	    		$model->setState('filter_entityid', $this->getId());
	    		$model->setState('filter_published', '1');
	    		
	    		// add the custom fields as properties
	    		$eavs = $model->getList();
	    		foreach($eavs as $eav)
	    		{
	    			$key = $eav->eavattribute_alias;
	    			
	    			// get the value table
	    			$table = JTable::getInstance('EavValues', 'TiendaTable');
	    			// set the type based on the attribute
	    			$table->setType($eav->eavattribute_type);
	    			
	    			// load the value based on the entity id
	    			$keynames = array();
	    			$keynames['eavattribute_id'] = $eav->eavattribute_id; 
	    			$keynames['eaventity_id'] = $this->getId();
	    			$table->load($keynames);
	    			
	    			$value = $table->eavvalue_value;
	    			
	    			$item->$key = $value;
	    		}
	    	}
        }
    }
	
}