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
	private $_link 					= 'index.php?option=com_tienda&view=products';
	private $_itemid				= null;
	private $_trackcatcount 		= 0;
	private $_products				= array();
	private $_pids					= array();
	private $_view					= '';
	private $_filter_category		= '';
	private $_filter_manufacturer_set	= '';
	private $_filter_manufacturer	= '';
	private $_filter_price_from		= '';
	private $_filter_price_to		= '';	
	private $_filter_attribute_set	= '';	
	private $_filter_attributeoptionname = array();
	private $_options				= array();	
	public $brands					= null;
	public $category_current		= null;
	
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

    	//TODO: REMOVE THIS
    	//$session	=& JFactory::getSession();
		//$registry	=& $session->get('registry');	
		//debug(111111, $registry);
    }      
    
    /**
     * Method to get condition to know if we have available either
     * categories, manufacturers, price ranges, attributes
     * @return boolean
     */
    function getCondition()
    {
    	return $this->_catfound || count($this->_products) ? true : false;  	
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
    	if(!empty($this->_filter_category) && $this->_params->get('filter_category'))
    	{   	
    		//get categories with parent_id = filter_category and category_id = filter_category
    		Tienda::load( 'TiendaQuery', 'library.query' );
			$query = new TiendaQuery();
			$query->select( 'tbl.*' );					
			$query->where('tbl.parent_id = '.(int) $this->_filter_category.' OR tbl.category_id = '.(int) $this->_filter_category);
			$query->where('tbl.category_enabled = \'1\'');			
			$query->from('#__tienda_categories AS tbl'); 
			$this->_db->setQuery((string) $query);
			$items = $this->_db->loadObjectList();

			//set the current category
			$this->category_current = $items[0];
			
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
	                $pmodel->setState('filter_attribute_set', $this->_filter_attribute_set);           
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
     * Method to get the ratings filter
     * return array    
     */
    function getRatings()
    {
    	$ratings = array();  
    	
    	if( $this->_view != 'products' || empty($this->_products) || !$this->_params->get('filter_rating') || !TiendaConfig::getInstance( )->get( 'product_review_enable' )) return $ratings;
    		    
	    $ratingFirst 	= 0;
	    $ratingSecond	= 0;
	    $ratingThird 	= 0;
	    $ratingFourth	= 0;
	    //loop the products to get the total of products for each rating
	    foreach($this->_products as $product)
	    {
	    	if($product->product_rating >= 1)
	    	{
	    		$ratingFirst++;
	    		
	    		if($product->product_rating >= 2)
	    		{
	    			$ratingSecond++;
	    			if($product->product_rating >= 3)
	    			{
	    				 $ratingThird++;
		    			if($product->product_rating >= 4)
		    			{
		    				 $ratingFourth++;
		    			}
	    			}
	    		}	    		
	    	}
	    }	    
    	$link = $this->_link.'&filter_category='.$this->_filter_category;	    	
    	
	    Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
		$ratingFourthObj = new stdClass();
		$ratingFourthObj->rating_name = TiendaHelperProduct::getRatingImage( 4 );
		$ratingFourthObj->link = JRoute::_( $link.'&filter_rating=4' );	
		$ratingFourthObj->total = $ratingSecond;
		$ratings[] = $ratingFourthObj;	
		
		$ratingThirdObj = new stdClass();
		$ratingThirdObj->rating_name = TiendaHelperProduct::getRatingImage( 3 );
		$ratingThirdObj->link = JRoute::_( $link.'&filter_rating=3' );	
		$ratingThirdObj->total = $ratingSecond;
		$ratings[] = $ratingThirdObj;

		$ratingSecondObj = new stdClass();
		$ratingSecondObj->rating_name = TiendaHelperProduct::getRatingImage( 2 );
		$ratingSecondObj->link = JRoute::_( $link.'&filter_rating=2' );	
		$ratingSecondObj->total = $ratingSecond;
		$ratings[] = $ratingSecondObj;
		
		$ratingFirstObj = new stdClass();
		$ratingFirstObj->rating_name = TiendaHelperProduct::getRatingImage( 1 );
		$ratingFirstObj->link = JRoute::_( $link.'&filter_rating=1' );	
		$ratingFirstObj->total = $ratingFirst;
		$ratings[] = $ratingFirstObj;
	  
    	return $ratings;
    }    
    
    /**      
     * Method to get the manufacturers based on the current view
     * @return array
     */
    function getManufacturers()
    {    	
    	$brandA = array();
    	   	
    	if( $this->_view != 'products' || empty($this->_products) || !$this->_params->get('filter_manufacturer') ) return $brandA;
	    
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
		$this->brands = $brandA;	
	    	
    	$brands = array();   
    	
    	//we need to return an empty array if in single mode we dont want to show the current brand filter to brand listing
    	if(!empty($this->_filter_manufacturer) && !$this->_multi_mode)
    	{
    		return $brands;
    	}   
    	
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
    	 
    	if( $this->_view != 'products' || empty($this->_products) || !$this->_params->get('filter_price') || $this->_filter_price_from ||  $this->_filter_price_to) return $ranges;
    	
    	$link = $this->_link.'&filter_category='.$this->_filter_category;
    	$items = $this->_products;
    	
    	//automatically create price ranges
    	if( $this->_params->get('auto_price_range') )
    	{    		    		    		
    		//get the highest price
    		$priceHigh = abs( floor($items['0']->price) ); 
    		
    		$glueZero = '';    		       	
  			for( $i = 1; $i < strlen($priceHigh); $i++ )
        	{
        		$glueZero .= '0'; 
        	}
        	
        	$priceHigh = $this->roundToNearest($priceHigh, '1'.$glueZero);
        	
        	//get if we are in 1, 10, 100, 1000, 10000,... 
    		$places =strlen($priceHigh);
        	    		
    		//only 1 product
    		if(count($items) < 2)
    		{    			
    			$rangeObj = new stdClass();	
				$rangeObj->price_from = 0;
				$rangeObj->price_to = $priceHigh;
    			$rangeObj->total = 1;		
				$rangeObj->link = $link.'&filter_price_from='.$rangeObj->price_from.'&filter_price_to='.$rangeObj->price_to.'&Itemid='.$this->_itemid;			
				$ranges[] = $rangeObj;    
				return 	$ranges;		
    		}
    		
    		//get the range
    		$range = "1{$glueZero}";
  		       		
    	
			for($i = 0; $i <= (substr($priceHigh, 0, 1) - 1); $i++)
			{
				$rangeObj = new stdClass();
				$rangeObj->price_from = $i == 0 ? 0 : $range * $i;
				$rangeObj->price_to = $i == 0 ? (int) $range : ((int) $range * $i) + (int) $range;
				
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
    	else
    	{
    		$price_range_set = $this->_params->get('price_range_set');
    		debug(999999, $price_range_set);
    		
    	}
     	         
    	return $ranges;
    }   
    
    /**
     * Method to round a number to nearest 10, 100, 1000 ...
     * @param int - $number
     * @param int - nearest
     * @param booleam
     * @return int
     */
    private function roundToNearest($number,$nearest=100, $roundUp = true)
    {
    	$number = round($number);
  
      	if($nearest>$number || $nearest <= 0)
      	{
      		return $number;
      	}
             
      	$mod = ($number%$nearest);  
          
      	return ($mod<($nearest/2)) || $roundUp ? $number+($nearest-$mod) : $number-$mod;
    }

    /**
     * Method to get the attributes with options of the current products     
     * @return array
     */
    function getAttributes()
    {				
		$finalAttributes = array();	
    	if($this->_view != 'products' || empty($this->_products) || !$this->_params->get('filter_attributes')) return $finalAttributes;
    	
    	Tienda::load( 'TiendaHelperProduct', 'helpers.product' ); 	
		
    	//check if we have pids
    	//else get the pids from $this->_products
    	if(empty($this->_pids))
    	{
	    	$pids = array();
	    	foreach($this->_products as $item)
	    	{
	    		$pids[] = $item->product_id;
	    	}
	    	$this->_pids = $pids;
    	}    
    	
    	//retun if we dont have pids
    	if(empty($this->_pids)) return $finalAttributes;
    			
    	//check if we TiendaQuery class exist
    	if(!class_exists('TiendaQuery'))
    	{
    		Tienda::load( 'TiendaQuery', 'library.query' );
    	}
    			
    	//get the attributes of the current products
    	$query = new TiendaQuery();
		$query->select( 'tbl.product_id' );	
		$query->select( 'tbl.productattribute_name' );	
		$query->select( 'tbl.productattribute_id' );	
		$query->from('#__tienda_productattributes AS tbl');  		
		
		//explode first because mysql needs the attribute ids inside a quote
		$excluded_attributes = explode( ',', $this->_params->get('excluded_attributes'));			
		$query->where( "tbl.productattribute_id NOT IN ('" . implode("', '", $excluded_attributes) . "')" );	
		$query->where( "tbl.product_id IN ('" . implode("', '", $this->_pids) . "')" );
		$this->_db->setQuery( (string) $query );
		$attributes = $this->_db->loadObjectList(); 

		//return if no available attributes
		if(empty($attributes)) return $finalAttributes;
		
		$options = array();
		//loop to get the available options of the attribute
    	foreach($attributes as $attribute)
		{		
			$attribute->productattribute_options = TiendaHelperProduct::getAttributeOptionsObjects($attribute->productattribute_id);;	
			
			$optionKey = array();		
			foreach($attribute->productattribute_options as $option)
			{
				$optionKey["{$attribute->productattribute_id}|{$option->productattributeoption_id}"] = "{$attribute->productattribute_name}:::{$option->productattributeoption_name}";				
			}					
			$options = array_merge($options, $optionKey);							
			
		}
	
		//count the total of products available for the particular option
		$count_values = array_count_values($options);
		
		$this->_options = array_keys($count_values);

		$app = JFactory::getApplication();
		$ns = $app->getName().'::'.'com.tienda.model';    	
		$this->_filter_attributeoptionname = array_filter($app->getUserStateFromRequest($ns.'.productsattributeoptionname', 'filter_productsattributeoptionname', array(), 'array'), 'strlen');
 		
    	//loop to get the attibutes with options to be shown in module
    	//need to track the option if its already occur to avoid in the previous attribute to avoid having same option filter
    	$trackOpts = array();
    	foreach($attributes as $attribute)
    	{
    		if(!in_array($attribute->productattribute_name, $trackOpts))
			{
				//create new attribute object
				$newAttriObj = new stdClass();
				$newAttriObj->productattribute_name = $attribute->productattribute_name;
				$newAttriObj->productattribute_id = $attribute->productattribute_id;				
				
				//loop to get all options available
				foreach($attribute->productattribute_options as $option)
				{					
					//get the product total for the option					
					$option->total = $count_values["{$attribute->productattribute_name}:::{$option->productattributeoption_name}"];
					
					//build the link for each option
					$link = $this->_link.'&filter_category='.$this->_filter_category;
					
					//prepare the &filter_attribute_set					
					$attriA = array();
					foreach($options as $key=>$value)
					{					
						if($value == "{$attribute->productattribute_name}:::{$option->productattributeoption_name}")
						{
							$explode = explode('|', $key);
							$attriA[]= $explode[0];	
						}
					}					
					
					//check if the attribute_set filter in the session is not empty
					// merge with the attributes id we get from the query
					$setA = array();
					if(!empty($this->_filter_attribute_set))
					{						
						$attriA = array_unique(array_merge(explode(',', $this->_filter_attribute_set), $attriA));
					}	
					
					$filter_attribute_set = implode(',', $attriA);
					$link .= '&filter_attribute_set='.$filter_attribute_set;
				
					//create filter for tracking the optionname being click
					//it will be used in the currently shopping by
					//$this->_filter_attributeoptionname format will be filter_attributeoptionname['optionname'] = category|attribute_set
					if(array_key_exists($option->productattributeoption_name, $this->_filter_attributeoptionname))
					{
						//recreate the filter_attribute_set							
						$link .= '&filter_attributeoptionname['.$option->productattributeoption_name.']='.implode(',', array_unique(array_merge(explode(',', $this->_filter_attributeoptionname[$option->productattributeoption_name]), $attriA)));
					}
					else
					{
						$link .= '&filter_attributeoptionname['.$option->productattributeoption_name.']='.$filter_attribute_set;
					}
					
					//loop $this->_filter_attributeoptionname to append
					foreach($this->_filter_attributeoptionname as $keyopt=>$optname)
					{
						//check if not $option->productattributeoption_name
						if($keyopt!=$option->productattributeoption_name)
						{
							$link .= '&filter_attributeoptionname['.$keyopt.']='.$optname;					
						}
					}				
					
					$option->link = $link;					
				}	
				
				$newAttriObj->productattribute_options = $attribute->productattribute_options;				
				$newAttriObj->link = $this->_link.'&filter_category='.$this->_filter_category;				
				$finalAttributes[] = $newAttriObj;				
				
				$trackOpts[] = $attribute->productattribute_name;			
			}
    	}
    
		return $finalAttributes;
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
    	$this->_filter_category = $app->getUserStateFromRequest($ns.'.category', 'filter_category', '0', 'int');    	    	
        $this->_filter_attribute_set = $app->getUserStateFromRequest($ns.'.attribute_set', 'filter_attribute_set', '', '');      
        $this->_filter_price_from = $app->getUserStateFromRequest($ns.'.price_from', 'filter_price_from', '0', 'int');
        $this->_filter_price_to = $app->getUserStateFromRequest($ns.'.price_to', 'filter_price_to', '', '');    
        $this->_filter_rating = $app->getUserStateFromRequest($ns.'.rating', 'filter_rating', '0', 'int');     
         	
    	$model = JModel::getInstance( 'Products', 'TiendaModel' );   
        $model->setState('filter_category', $this->_filter_category);  
        
    	if($this->_multi_mode)
    	{    		
    		$this->_filter_manufacturer_set = $app->getUserStateFromRequest($ns.'.manufacturer_set', 'filter_manufacturer_set', '', '');
    		$model->setState('filter_manufacturer_set',  $this->_filter_manufacturer_set);       
    	}
    	else 
    	{    		       
    		$this->_filter_manufacturer = $app->getUserStateFromRequest($ns.'.manufacturer', 'filter_manufacturer', '', 'int');
    		$model->setState('filter_manufacturer',  $this->_filter_manufacturer);       
    	}        
       
       	$model->setState('filter_attribute_set', $this->_filter_attribute_set);      
     	$model->setState('filter_price_from', $this->_filter_price_from);           
        $model->setState('filter_price_to', $this->_filter_price_to);     
        $model->setState('filter_rating', $this->_filter_rating);	
        $model->setState('filter_enabled', '1');
	    $model->setState('filter_quantity_from', '1');	
	    $model->setState( 'order', 'price' );
        $model->setState( 'direction', 'DESC' ); 
        $items = $model->getList();  
  
        return $items;
    }
    
    function getFilters()
    {
		$filters = array();
		
    	if(!empty($this->_filter_category) && !empty($this->category_current))
		{
			$catObj = new stdClass();
			$catObj->label = JText::_('Category');
			$catObj->value = $this->category_current->category_name;
			$catObj->link = $this->_link.'&filter_category=';		
			$filters[] = $catObj;
		}		
		
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

			$options = array();
			foreach($this->_options as $opt)
			{
				$explodetxt = explode(':::', $opt);
				$options[$explodetxt[1]] = $explodetxt[0];
			}

			foreach($this->_filter_attributeoptionname as $key=>$value)
			{
				$attriObj = new stdClass();
				$attriObj->label = $options[$key];
				$attriObj->value = $key;
				
				$link = '';
				$aset = array();
				//loop to create the option link
				foreach($this->_filter_attributeoptionname as $k=>$v)
				{
					if($key != $k)
					{
						$link .= '&filter_attributeoptionname['.$k.']='.$v;
						$aset = array_unique(array_merge(explode(',', $v), $aset));						
					}
				}
								
				$link .= '&filter_attributeoptionname['.$key.']=';						
				$attriObj->link = $this->_link.'&filter_category='.$this->_filter_category.'&filter_attribute_set='.implode(',', $aset).$link;
				$filters[] = $attriObj;
			}
		}
		
    	if($this->_filter_rating && $this->_params->get('filter_rating'))
		{			
			$ratingObj = new stdClass();
			$ratingObj->label = JText::_('Rating');
			$ratingObj->value = TiendaHelperProduct::getRatingImage( (float) $this->_filter_rating ).' '.JText::_('& Up');
			$ratingObj->link = $this->_link.'&filter_category='.$this->_filter_category.'&filter_rating=0';		
			$filters[] = $ratingObj;
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
					$brandObj->value = $this->brands[$brand];
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
				$brandObj->value = $this->brands[$this->_filter_manufacturer];
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
