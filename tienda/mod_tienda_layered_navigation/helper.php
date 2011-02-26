<?php
/**
 * @version    1.5
 * @package    Tienda
 * @author     Dioscouri Design
 * @link     http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.application.component.model' );

class modTiendaLayeredNavigationFiltersHelper extends JObject
{
	private $_db 					= null;
	private $_params 				= null;
	private $_multi_mode 			= true;		
	private $_catfound 				= false;
	private $_manufound				= false;
	private $_pricefound			= false;
	private $_attrifound			= false;
	private $_link 					= 'index.php?option=com_tienda&view=products';
	private $_itemid				= null;
	private $_trackcatcount 		= 0;
	private $_products				= null;
	private $_pids					= array();
	private $_view					= '';
	private $_filter_category		= '';
	private $_filter_manufacturer_set	= '';
	private $_filter_price_from		= '';
	private $_filter_price_to			= '';	
	private $_filter_attribute_set	= '';	
	private $_filter_attributeoptionname = array();
	private $_options				= array();	
	
    /**
     * Sets the modules params as a property of the object
     * @param object $params     
     *  
     */
    function __construct( $params )    
    {    	
        $this->_params 		= $params;          
        $this->_db 			= JFactory::getDBO();
        $this->_multi_mode 	= $params->get('multi_mode', 1); 
    	$this->_itemid 		= JRequest::getInt('Itemid');    	
    	$this->_view 		= JRequest::getVar('view');    	
    	$this->_products = $this->getProducts();    		
  	
    	$session	=& JFactory::getSession();
		$registry	=& $session->get('registry');	
    }      
    
    /**
     * Method to get condition to know if we have available either
     * categories, manufacturers, price ranges, attributes
     * @return boolean
     */
    function getCondition()
    {
    	return $this->_catfound ||$this->_manufound || $this->_pricefound || $this->_attrifound ? true : false;  	
    }    
    
    /**
     * 
     * Enter description here ...
     * @return unknown_type
     */
    function getTrackCatCount()
    {
    	return $this->_trackcatcount;
    }
        
    /**      
     * Method to get the categories based on the current view
     * @return array
     */
    function getCategories()
    {
    	$items = array();    	
    	//filter category found so we display child categories and products inside
    	if(!empty($this->_filter_category))
    	{
    		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		    $model  = JModel::getInstance( 'Categories', 'TiendaModel' );
	    	$model->setState('filter_enabled', '1');
			$model->setState('order', 'tbl.lft');
			$model->setState('filter_parentid', $this->_filter_category);
			$items = $model->getList();
			
    		if (!empty($items))
		    {
		    	$this->_catfound = true;
		    	$catids = array();
		    	$total = 0;
		    	foreach ($items as $item)
		    	{		    		
		    	  	$pmodel = JModel::getInstance('Products', 'TiendaModel');
	                $pmodel->setState('filter_category', $item->category_id);
	                //make sure that it is enabled
	                $pmodel->setState('filter_enabled', '1');
	                $pmodel->setState('filter_quantity_from', '1');	               
		            $item->product_total = $pmodel->getTotal();	   	    	       
		    	   	$item->link = JRoute::_($this->_link.'&filter_category='.$item->category_id.'&Itemid='.$this->_itemid);		    		
		    		
		    		if($item->category_id != $this->_filter_category)
		    		{
		    			$total = $total + $item->product_total;
		    		}
		    	}  
		    	$this->_trackcatcount = $total;		    	
		    }
    	}

    	return $items;
    }
    
    /**      
     * Method to get the manufacturers based on the current view
     * @return array
     */
    function getManufacturers()
    {    	
    	$brandA = array();
    	$view = JRequest::getVar('view');    	
    	if($view != 'products') return $brandA;
    	
    			       
    	if(!empty($this->_filter_manufacturer) && !$this->_multi_mode)
    	{
    		return $brandA;
    	}    		      
    	
    	
    	if(empty($this->_products))
	    {  
	        $this->_products = $this->getProducts();
	    }
	    
    	$setA = explode(',', $this->_filter_manufacturer_set);
    	$pids = array();
	    	foreach($this->_products as $item)
	    	{    		
	    		$pids[] = $item->product_id;
	    		if(!empty($item->manufacturer_id))
	    		{
	    			$brandA[$item->manufacturer_id] = $item->manufacturer_name;
	    		}     		
	    	}    
	    	$this->_pids = $pids;
    	
    	asort($brandA);
		$this->_brands = $brandA;	
	    	
    	$brands = array();   
    	
		if(!empty($brandA))
		{			
			foreach($brandA as $key=>$value)
			{
				
				$link = $this->_link.'&filter_category='.$this->_filter_category;	
				
				if($this->_multi_mode)				
				{	
					if(in_array($key, $setA))
					{
						continue;
					}
					$link .= '&filter_manufacturer_set=';
					$link .= empty($this->_filter_manufacturer_set) ? $key : $this->_filter_manufacturer_set.','.$key;			
				}
				else 
				{
					$link .= '&filter_manufacturer='.$key;
				}
				
				$link .= '&Itemid='.$this->_itemid;
				
				$brandObj = new stdClass();
				$brandObj->manufacturer_name = $value;
				$brandObj->link = JRoute::_( $link );	
				
				$total = 0;
				foreach($this->_products as $item)
		    	{    		
		    		if($item->manufacturer_id == $key && $item->manufacturer_name == $value) 
		    		{ 
		    			$total++;
		    		}
		    	}
				
				$brandObj->total = $total;
				$brands[] = $brandObj;			
			}
		}
		
	    return $brands;    	
    }
        
    /**      
     * Method to get the prices based on the current view
     * @return array
     */
    function getPriceRanges()
    {
    	$ranges = array();
    	
    	$view = JRequest::getVar('view');
    	$price_from = JRequest::getVar('filter_price_from');
    	$price_to = JRequest::getVar('filter_price_to');
    	
    	if($price_from || $price_to)
    	{
    		return $ranges;
    	}
    	
    	if(empty($this->_filter_category) && $view != 'manufacturers')
    	{
    		return $ranges;
    	} 
    	$items = $this->_products;

    	if(empty($items))    	
    	{
    		$items = $this->getProducts();
    		$this->_products = $items;
    	}

        if(!empty($items))
        {
        	$this->_pricefound = true;
        	$priceHigh = abs( $items['0']->price );   
        	
        	$glueZero = '';
       	
  			for( $i = 1; $i < strlen($priceHigh); $i++ )
        	{
        		$glueZero .= '0'; 
        	}
        	
        	$priceHigh = (substr($priceHigh, 0, 1) + 1).$glueZero;  			
        	$range = "1{$glueZero}";
      	  	
			$link = $this->_link.'&filter_category='.$this->_filter_category;
										
			$ranges = array();
			for($i = 0; $i <= (substr($priceHigh, 0, 1) - 1); $i++)
			{
				$rangeObj = new stdClass();	
				$rangeObj->price_from = $i == 0 ? 0 : (int) $range * $i;
				$rangeObj->price_to = $i == 0 ? (int) $range : (int) $range * ( $i + 1 );
				
				$pids = array();
			    $total_product = 0;
			    foreach($items as $item)
			    {
			    	$pids[] = $item->product_id;
			    	if(($item->price >= $rangeObj->price_from) && ($item->price <= $rangeObj->price_to))
			    	{
			    		$total_product++;
			    	}	
			    }
			    
			    $this->_pids = $pids;		
	           
				$rangeObj->total = $total_product;		
				$rangeObj->link = $link.'&filter_price_from='.$rangeObj->price_from.'&filter_price_to='.$rangeObj->price_to.'&Itemid='.$this->_itemid;			
				$ranges[] = $rangeObj;		
			}        	
        }
         
    	return $ranges;
    }   

    /**
     * 
     * Enter description here ...
     * @return unknown_type
     */
    function getAttributes()
    {
        Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
        
    	$items = $this->_products;
    	if(empty($items))
    	{
    		$items = $this->getProducts();
    		$this->_products = $items;	
    	}
    	    	
    	if(empty($this->_pids))
    	{
	    	$pids = array();
	    	foreach($items as $item)
	    	{
	    		$pids[] = $item->product_id;
	    	}
	    	$this->_pids = $pids;
    	}    	
 	
    	$query = new TiendaQuery();
		$query->select( 'tbl.product_id' );	
		$query->select( 'tbl.productattribute_name' );	
		$query->select( 'tbl.productattribute_id' );	
		$query->from('#__tienda_productattributes AS tbl');  
		if (!empty($this->_pids))
		{
		    $query->where( "tbl.product_id IN ('" . implode("', '", $this->_pids) . "')" );
		}
					
		$this->_db->setQuery( (string) $query );
		$attributes = $this->_db->loadObjectList(); 

		if(empty($attributes)) return array();
		
		$optionsA = array();
		$attriNameA = array();
		$trackAttri = array();		
    	foreach($attributes as $attribute)
		{									
			$options = TiendaHelperProduct::getAttributeOptionsObjects($attribute->productattribute_id);			
			$optionKey = array();		
			foreach($options as $option)
			{
				$optionKey["{$attribute->productattribute_id}|{$option->productattributeoption_id}"] = "{$attribute->productattribute_name}:::{$option->productattributeoption_name}";				
			}					
			$optionsA = array_merge($optionsA, $optionKey);							
			$attribute->productattribute_options = $options;	
		}
		
		//passed for filters	
		$this->_options = $optionsA;

		$count_values = array_count_values($optionsA);

		//track so that we will not showing same attribute
		$trackA = array();	
		$newAttributes = array();
		
		$app = JFactory::getApplication();
		$ns = $app->getName().'::'.'com.tienda.model';    	
		$this->_filter_attributeoptionname = array_filter($app->getUserStateFromRequest($ns.'.productsattributeoptionname', 'filter_productsattributeoptionname', array(), 'array'), 'strlen');
	
		foreach($attributes as $attribute)
		{
			if(!in_array($attribute->productattribute_name, $trackA))
			{
				$newAttriObj = new stdClass();
				$newAttriObj->productattribute_name = $attribute->productattribute_name;
				$newAttriObj->productattribute_id = $attribute->productattribute_id;
				foreach($attribute->productattribute_options as $option)
				{					
					//make it unique => :::
					$index = "{$attribute->productattribute_name}:::{$option->productattributeoption_name}";
					$option->total = $count_values[$index];
					$link = $this->_link.'&filter_category='.$this->_filter_category;
				
					//get the attribute_id
					$attriA = array();
					foreach($optionsA as $key=>$value)
					{					
						if($value == $index)
						{
							$explode = explode('|', $key);
							$attriA[]= $explode[0];	
						}
					}					
					//if $this->_filter_attribute_set is not empty
					$setA = array();
					if(!empty($this->_filter_attribute_set))
					{
						$setA = explode(',', $this->_filter_attribute_set);
						$attriA = array_unique(array_merge($setA, $attriA));
					}			
										
					$link .= '&filter_attribute_set='.implode(',', $attriA);	
									
					if(!empty($this->_filter_attributeoptionname))
					{
						foreach($this->_filter_attributeoptionname as $k=>$val)
						{
							if(!empty($val))
							{
								$link .= '&filter_attributeoptionname['.$k.']='.$val;
							}												
						}
					}
										
					$link .= '&filter_attributeoptionname['.$option->productattributeoption_id.']='.$option->productattributeoption_name;					
					
					$option->link = $link;
				}
				
				$newAttriObj->productattribute_options = $attribute->productattribute_options;
				
				$newAttriObj->link = $this->_link.'&filter_category='.$this->_filter_category;
				
				$newAttributes[] = $newAttriObj;
			}			
			$trackA[] = $attribute->productattribute_name;
		}
	
		return $newAttributes;    	
    }  
    
    /**
     * 
     * Enter description here ...
     * @return unknown_type
     */
 	private function getProducts()
    {    	    	
    	$items = array();
    	
    	$option = JRequest::getVar('option');    	
    	if($option != 'com_tienda' && $this->_view != 'products')
    	{
    		return $items;
    	}
    	
    	$app = JFactory::getApplication(); 
    	$ns = $app->getName().'::'.'com.tienda.model.products';
    	$this->_filter_category = $app->getUserStateFromRequest($ns.'.category', 'filter_category', '', '');
    	    	
        $this->_filter_attribute_set = $app->getUserStateFromRequest($ns.'.attribute_set', 'filter_attribute_set', '', '');      
        $this->_filter_price_from = $app->getUserStateFromRequest($ns.'.price_from', 'filter_price_from', '0', 'int');
        $this->_filter_price_to = $app->getUserStateFromRequest($ns.'.price_to', 'filter_price_to', '', '');        
           	
    	$model = JModel::getInstance( 'Products', 'TiendaModel' );   
        $model->setState('filter_category', $this->_filter_category);  
        
    	if($this->_multi_mode)
    	{    		
    		$this->_filter_manufacturer_set = $app->getUserStateFromRequest($ns.'.manufacturer_set', 'filter_manufacturer_set', '', '');
    		$model->setState('filter_manufacturer_set',  $this->_filter_manufacturer_set);       
    	}
    	else 
    	{    		       
    		$this->_filter_manufacturer = $app->getUserStateFromRequest($ns.'.manufacturer', 'filter_manufacturer', '', '');
    		$model->setState('filter_manufacturer',  $this->_filter_manufacturer);       
    	}        
       
       	$model->setState('filter_attribute_set', $this->_filter_attribute_set);      
     	$model->setState('filter_price_from', $this->_filter_price_from);           
        $model->setState('filter_price_to', $this->_filter_price_to);        
	    $model->setState( 'order', 'price' );
        $model->setState( 'direction', 'DESC' ); 
        $model->setState('filter_enabled', '1');
	    $model->setState('filter_quantity_from', '1');	
        $items = $model->getList();  
   
        return $items;
    }
    
    function getFilters()
    {
		$filters = array();
		
		if(!empty($this->_filter_price_from) || !empty($this->_filter_price_to))
		{
			$priceObj = new stdClass();
			$priceObj->label = JText::_('Price');
			$priceObj->value = TiendaHelperBase::currency($this->_filter_price_from).' - '.TiendaHelperBase::currency($this->_filter_price_to);
			$priceObj->link = $this->_link.'&filter_category='.$this->_filter_category.'&filter_price_from=0&filter_price_to=';		
			$filters[] = $priceObj;
		}
	
    	if(!empty($this->_filter_attribute_set))
		{
			$attriA = explode(',', $this->_filter_attribute_set);
			
			//remove atttributes
			$newOptionsA = array();
			foreach($this->_options as $op=>$option)
			{
				$opStr = explode('|', $op);
				$newOptionsA[$opStr[1]] = $option;
			}
		
			foreach($this->_filter_attributeoptionname as $key=>$value)
			{
				$labelA = explode(':::', $newOptionsA[$key]);
				$label = $labelA[0];				
			
				//loop to get attribute ids with the combi=$newOptionsA[$key] from $this->_options		
				$newSet = array();				
				foreach($this->_options as $op=>$option)
				{					
					if($option == $newOptionsA[$key])
					{
						$opA = explode('|', $op);
						$newSet[] = $opA[0];
					}
				}	
			
				$attriObj = new stdClass();
				$attriObj->label = $label;
				$attriObj->value = $value;
				
				//remove from filter set
				$origSet = explode(',', $this->_filter_attribute_set);				
				$sets = array_diff($origSet, $newSet);				
				
				$link = '';
				foreach($this->_filter_attributeoptionname as $k=>$val)
				{
					if($k == $key && !empty($val))
					{
						$link .= '&filter_attributeoptionname['.$k.']=';
					}
					else 
					{
						if(!empty($val))
						{
							$link .= '&filter_attributeoptionname['.$k.']='.$val;
						}		
					}																
				}
				
				$attriObj->link = $this->_link.'&filter_category='.$this->_filter_category.'&filter_attribute_set='.implode(',', $sets).$link;
				
				$filters[] = $attriObj;
			}			
		}
		
		if($this->_multi_mode)
		{
			if(!empty($this->_filter_manufacturer_set))
			{
				$brandSet = explode(',', $this->_filter_manufacturer_set);
				
				foreach($brandSet as $brand)
				{
					$brandObj = new stdClass();
					$brandObj->label = JText::_('Manufacturer');
					$brandObj->value = $this->_brands[$brand];
					$brandObj->link = $this->_link.'&filter_category='.$this->_filter_category.'&filter_manufacturer_set='.implode(',',array_diff($brandSet, array($brand)));		
					$filters[] = $brandObj;					
				}
			}
		}
		else 
		{
			if(!empty($this->_filter_manufacturer))
			{
				$brandObj = new stdClass();
				$brandObj->label = JText::_('Manufacturer');
				$brandObj->value = $this->_brands[$this->_filter_manufacturer];
				$brandObj->link = $this->_link.'&filter_category='.$this->_filter_category.'&filter_manufacturer=';		
				$filters[] = $brandObj;
			}
		}   	
		
    	return $filters;
    }    
    
    function getAttributeOptions()
    {
    	return $this->_filter_attributeoptionname;
    }
}
?>   
