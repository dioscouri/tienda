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
	private $_db 			= null;
	private $_params 		= null;
	private $_multi_mode 	= true;	
	private $_catid 		= '';
	private $_catfound 		= false;
	private $_manufound		= false;
	private $_pricefound	= false;
	private $_attrifound	= false;
	private $_link 			= 'index.php?option=com_tienda&view=products';
	private $_itemid		= null;
	private $_trackcatcount = 0;
	private $_products		= null;
	private $_pids			= array();
	private $_view			= '';
	
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
        $this->_catid 		= JRequest::getInt('filter_category');    	   	
    	$this->_itemid 		= JRequest::getInt('Itemid');    	
    	$this->_view 		= JRequest::getVar('view');
    	if($this->_view == 'products')
    	{
    		$this->_products = $this->getProducts();
    	} 	
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
    	if(!empty($this->_catid))
    	{
    		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		    $model  = JModel::getInstance( 'Categories', 'TiendaModel' );
	    	$model->setState('filter_enabled', '1');
			$model->setState('order', 'tbl.lft');
			$model->setState('filter_parentid', $this->_catid);
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
		    		
		    		if($item->category_id != $this->_catid)
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
    	
    	//if we have filter catergory
    	//we dont show manufacturers list
    	$filter_manufacturer = JRequest::getVar('filter_manufacturer');
    	if(strlen($filter_manufacturer)) return $brandA;
    	   			
    	if(empty($this->_products))
    	{  
        	$this->_products = $this->getProducts();
    	}
	
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
	
    	$brands = array();    	
		if(!empty($brandA))
		{			
			foreach($brandA as $key=>$value)
			{
				$brandObj = new stdClass();
				$brandObj->manufacturer_name = $value;
				$link = $this->_link.'&filter_category='.$this->_catid;				
				$link .= '&filter_manufacturer='.$key;
				$link .= '&Itemid='.$this->_itemid;
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
    	if(empty($this->_catid) && $view != 'manufacturers')
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
        	//$priceLow = ( count($items) == 1 ) ? 0 : abs( $items[count( $items ) - 1]->price );
        	//$range = ( abs( $priceHigh ) - abs( $priceLow ) )/4; 
        	$glueZero = '';
       	
  			for( $i = 1; $i < strlen($priceHigh); $i++ )
        	{
        		$glueZero .= '0'; 
        	}
        	
        	$priceHigh = (substr($priceHigh, 0, 1) + 1).$glueZero;  			
        	$range = "1{$glueZero}";
      	
        	//$roundRange = is_int($range) ? $range : ceil($range);                		
			//$roundPriceLow = floor($priceLow);				
			//$price_from = $roundRange + $roundPriceLow;
  	
			$link = $this->_link.'&filter_category='.$this->_catid;
										
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

    function getAttributes()
    {
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
		$query->where( 'tbl.product_id IN(' . implode(',', $this->_pids) . ')' );	
					
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
				$optionKey["{$attribute->productattribute_id}|{$option->productattributeoption_id}"] = "{$attribute->productattribute_name}|{$option->productattributeoption_name}";				
			}
			
			$optionsA = array_merge($optionsA, $optionKey);	
						
			$attribute->productattribute_options = $options;	
		}
	
		$count_values = array_count_values($optionsA);

		//track so that we will not showing same attribute
		$trackA = array();	
		$newAttributes = array();
		
		foreach($attributes as $attribute)
		{
			if(!in_array($attribute->productattribute_name, $trackA))
			{
				$newAttriObj = new stdClass();
				$newAttriObj->productattribute_name = $attribute->productattribute_name;
				$newAttriObj->productattribute_id = $attribute->productattribute_id;
				foreach($attribute->productattribute_options as $option)
				{
					$index = "{$attribute->productattribute_name}|{$option->productattributeoption_name}";
					$option->total = $count_values[$index];
					$link = $this->_link.'&filter_category='.$this->_catid;
				
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
					
					$link .= '&filter_attribute_set='.implode(',', $attriA);	
					$link .= '&filter_attributeoptionname='.strtolower($option->productattributeoption_name);					
					$option->link = $link;
				}
				
				$newAttriObj->productattribute_options = $attribute->productattribute_options;
				
				$newAttriObj->link = $this->_link.'&filter_category='.$this->_catid;
				
				$newAttributes[] = $newAttriObj;
			}			
			$trackA[] = $attribute->productattribute_name;
		}
		
		return $newAttributes;    	
    }  
    
 	private function getProducts()
    {
    	$app = JFactory::getApplication();
    	$ns = $app->getName().'::'.'com.tienda.model.products';
    	
    	$filter_manufacturer = $app->getUserStateFromRequest($ns.'.manufacturer', 'filter_manufacturer', '', '');
        $filter_attribute_set = $app->getUserStateFromRequest($ns.'.attribute_set', 'filter_attribute_set', '', '');      
        $filter_price_from = $app->getUserStateFromRequest($ns.'.price_from', 'filter_price_from', '0', 'int');
        $filter_price_to = $app->getUserStateFromRequest($ns.'.price_to', 'filter_price_to', '', '');
    	
    	$model = JModel::getInstance( 'Products', 'TiendaModel' );   
        $model->setState('filter_category', $this->_catid);  
        $model->setState('filter_manufacturer', $filter_manufacturer);       
       	$model->setState('filter_attribute_set', $filter_attribute_set);
      
     	$model->setState('filter_price_from', $filter_price_from);           
        $model->setState('filter_price_to', $filter_price_to);
        
	    $model->setState( 'order', 'price' );
        $model->setState( 'direction', 'DESC' ); 
        $model->setState('filter_enabled', '1');
	    $model->setState('filter_quantity_from', '1');	
        $items = $model->getList();  
   
        return $items;
    }
}
?>   
