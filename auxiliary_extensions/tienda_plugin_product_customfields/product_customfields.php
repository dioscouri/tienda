<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaPluginBase', 'library.plugins._base' );

class plgTiendaProduct_customfields extends TiendaPluginBase
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'product_customfields';
    
	function plgTiendaProduct_customfields(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
		
		//Check the installation integrity
        $helper = Tienda::getClass( 'TiendaHelperDiagnosticsProductCustomFields', 'product_customfields.diagnostic', array( 'site'=>'site', 'type'=>'plugins', 'ext'=>'tienda' ) );
        $helper->checkInstallation();
	}

	/**
	 * 
	 * This event is fired when a product is displayed to be added to the cart.
	 * It displays the custom fields form based on the product_params field
	 * @param $product_id
	 */
	function onDisplayProductAttributeOptions( $product_id )
	{
		$vars = new JObject();
		
	    $custom_fields = $this->getCustomFields( $product_id );
	    
	    $vars->custom_fields =  $custom_fields;
		
		echo $this->_getLayout( 'product_customfields_form', $vars );		
	}

	/**
	 * This event is fired when a cart item is displayed.
	 * It displays the custom fields for each cart item.
	 * @param $index
	 * @param $cartitem
	 */
    function onDisplayCartItem( $index, $cartitem )
    {
    	$this->displayCustomFields($index, $cartitem->product_id,
    					$cartitem->cartitem_customfields_id, 
    					$cartitem->cartitem_customfields, 
    					'view');
    }
    
    /**
     * This event is fired when a order item is displayed.
     * It displays the custom fields for each order item
     * @param $index
     * @param $orderitem
     */
    function onDisplayOrderItem( $index, $orderitem )
    {
    	if (empty($orderitem))return;
    		
		$this->displayCustomFields($index, $orderitem->product_id, 
						$orderitem->orderitem_customfields_id,
						$orderitem->orderitem_customfields,
						'view');
    }
    
    /**
     * 
     * Helper function that displays the custom fields and their values based on a template 
     * @param $product_id
     * @param $custom_field_values
     * @param $display_template
     */
    function displayCustomFields($index, $product_id, $custom_fields_id, $custom_field_values, $display_template)
    {	
		if (!empty($custom_field_values))
		{
		    $custom_fields = $this->getCustomFields( $product_id );
			if(!empty($custom_fields))
			{
				//put the customfields values from the cart into a JParameter
				$cartitem_param = new JParameter( trim($custom_field_values) );
				$cartitem_cf_values = $cartitem_param->toArray();
				
				//add the value to custom field
				foreach($custom_fields as $custom_field)
				{
					$custom_field->value = $cartitem_cf_values[$custom_field->id];
				}

				$vars = new JObject();
				$vars->index = $index;
		        $vars->product_id = $product_id;
		        $vars->custom_fields_id = $custom_fields_id;				
		        $vars->custom_fields = $custom_fields;

				echo $this->_getLayout( $display_template, $vars );		
			}		    
		}    	
    }

    function onAfterSaveOrderItem( $item )
    {
    	if (empty($item))
    	{
    		JError::raiseNotice('onAfterSaveOrderItem', JText::_('TIENDA_PRODUCT_CUSTOMFIELDS_UPDATEORDERITEM_FAILED').'(No item)');
    	}
    	
		$orderitem = JTable::getInstance( 'OrderItems', 'TiendaTable' );
		$orderitem->load( $item->orderitem_id );
		if (empty($orderitem) || empty($item))
    	{
    		JError::raiseNotice('onAfterSaveOrderItem', JText::_('TIENDA_PRODUCT_CUSTOMFIELDS_UPDATEORDERITEM_FAILED').'(No orderitem)');
    	}
    	
    	$custom_fields = $this->getCustomFields( $item->product_id );
    	if (!empty($custom_fields))
    	{
	    	$orderitem_param = new JParameter( trim( $item->orderitem_customfields ) );
	    	$orderitem_cf_values = $orderitem_param->toArray();

	    	$user_id = JFactory::getUser()->id;
	    	
	    	foreach($custom_fields as $custom_field)
	    	{
	    		if ($custom_field->datatype == 'file')
	    		{
	    			$file_name = basename($orderitem_cf_values[$custom_field->id]); 
	    			$source = Tienda::getPath('cartitems_files').DS.$user_id.DS.$item->orderitem_customfields_id.DS.$file_name;

            		$destination_webfolder = Tienda::getUrl('orderitems_files').$user_id.'/'.$orderitem->orderitem_id.'/';
					$destination_folder = Tienda::getPath('orderitems_files').DS.$user_id.DS.$orderitem->orderitem_id.DS;		
					if (!JFolder::exists($destination_folder)) JFolder::create($destination_folder);
	    			
					if (!JFile::move($source, $destination_folder.$file_name))
					{
			    		JError::raiseNotice('onAfterSaveOrderItem', JText::_('TIENDA_PRODUCT_CUSTOMFIELDS_UPDATEORDERITEM_FAILED').'(Unable to copy file to orderitem)');
					}
	    			//set the new location to cartitem_customfields
	    			$orderitem_param->set($custom_field->id, $destination_webfolder.$file_name);
	    		}
	    	}
	    	
	    	//save the custom fields + values to the order item
	    	$orderitem->orderitem_customfields = $orderitem_param->toString();
	    	
	    	$date = JFactory::getDate();
	    	$orderitem->modified_date = $date->toMysql();
			if (!$orderitem->save())
			{
				JError::raiseNotice('saveOrderItem', $orderitem->getError());
			}
        }   
    }
    
    function onGetAdditionalCartKeys()
    {
		return array('cartitem_customfields_id' => 'cartitem_customfields_id');    	
    }
    
	/**
	 * Event to allow plugins to add keys to the loading of cart items
	 * to make the cartitem also unique based on extra carts column(s).
	 */
    function onGetAdditionalCartKeyValues($item, $posted_values, $index)
    {
    	if ($item != null)
    	{
			return array('cartitem_customfields_id' => $item->cartitem_customfields_id);
    	}
    	
    	$customfields_id = $posted_values["cartitem_customfields"][$index];
    	if (empty($customfields_id)) return array();
    	
    	return array('cartitem_customfields_id' => $customfields_id);
    }

    function onGetAdditionalOrderitemKeys($item)
    {
		return array(	'orderitem_customfields_id'=>$item->orderitem_customfields_id);    	
    }
    
    function onGetAdditionalOrderitemKeyValues($item)
    {
		return array(	'orderitem_customfields_id'=>$item->cartitem_customfields_id,
						'orderitem_customfields'=>$item->cartitem_customfields);    	
    }
    
    function onAfterCreateItemForAddToCart($item, $values, $files)
    {
		if (empty($values["hasCustomFields"])) return array();

		//generate a new custom fields id
		$newCustomFieldsID = $this->getNewCustomFieldsID();
		if ($newCustomFieldsID == false)
		{
    		JError::raiseNotice('onAfterCreateItemForAddToCart', JText::_('TIENDA_PRODUCT_CUSTOMFIELDS_AFTER_CREATE_ITEM_FAILED').'(No max custom_fields_id retrieved)');			
		}		
		
    	//get custom field values
        $params = new JParameter('');       
        foreach ($values as $key=>$value)
        {
            if (substr($key, 0, 13) == 'custom_field_')
            {
            	$params->set($key,$value);
            }
        }
        
        //custom fields of type 'file': files being uploaded
		if (!empty($files))
		{
			$user_id = JFactory::getUser()->id;
			jimport( 'joomla.filesystem.file' );
	        foreach($files as $key=>$file)
	        {
            	if (substr($key, 0, 13) == 'custom_field_')
            	{	        	
            		$destination_webfolder = Tienda::getUrl('cartitems_files').$user_id.'/'.$newCustomFieldsID.'/'; 
					$destination_folder = Tienda::getPath('cartitems_files').DS.$user_id.DS.$newCustomFieldsID.DS;		
					if (!JFolder::exists($destination_folder)) JFolder::create($destination_folder);
	
					$dest_file = $file['name'];
					if (!JFile::upload($file['tmp_name'], $destination_folder.$dest_file))
					{
			    		JError::raiseNotice('onAfterCreateItemForAddToCart', JText::_('TIENDA_PRODUCT_CUSTOMFIELDS_AFTER_CREATE_ITEM_FAILED').'(unable to upload)');
					}
					$params->set($key, $destination_webfolder.$dest_file);
            	}
	        }				
		}       		 

        return array( 	'cartitem_customfields_id' => $newCustomFieldsID, 
        				'cartitem_customfields' => trim( $params->toString() ) );
    }    
    
    //HELPER FUNCTIONS
	function getNewCustomFieldsID()
    {
        $return = false;
        
        Tienda::load( 'TiendaQuery', 'library.query' );
        $query = new TiendaQuery();
        $query->select( 'max(tbl.cartitem_customfields_id) as customfields_id' );
        $query->from( '#__tienda_carts AS tbl' );

        $db =& JFactory::getDBO();
        $db->setQuery( (string) $query );
        $cart = $db->loadObject();
 
        if (empty($cart->customfields_id)) return 1;
        
        if ($cart->customfields_id >= 0)
        {
            $return = $cart->customfields_id+1;
        }
        return $return;
    }
    
    /**
     * 
     * Retrieves a product's custom fields from the product_params column
     * in as an array of CustomField objects so that the rendering of the
     * custom fields can be on typed objects. 
     * @param $product_id
     */
    function getCustomFields( $product_id )
    {
        if (empty($product_id))
        {
            return array();
        }
        
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$model = JModel::getInstance( 'Products', 'TiendaModel' );
		$model->setId( $product_id );

		$item = $model->getItem();
        if (empty($item)) return null;
        
        $product_param = new JParameter( trim($item->product_params) ); //todo: remove the items that are not custom fields
        if (empty($product_param)) return array();

        $params = $product_param->toArray();
        $custom_fields = null;
        foreach($params as $key => $value)
    	{
    		if (strpos($key, 'custom_field') !== false)
    		{
    			if ($custom_fields == null)
    				$custom_fields = array();
    				
    			$custom_field = array();
    			
    			$field_properties = explode(",", $value);
    			foreach($field_properties as $field_property)
    			{
    				$property_pair = explode(":", $field_property);
    				if (!empty($property_pair))
    				{
						$custom_field[trim($property_pair[0])] = trim($property_pair[1]);    					
    				}
    			}
    			$custom_field["id"] = $key;
    			array_push($custom_fields, (object)$custom_field);
    		} 
    	}        
    	return $custom_fields;
    }
}