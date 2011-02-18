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
	private $_catids 		= array();
	private $_catid 		= '';
	private $_catfound 		= false;
	private $_manufound		= false;
	private $_pricefound	= false;
	private $_attrifound	= false;
	private $_link 			= 'index.php?option=com_tienda&view=products';
	private $_itemid		= null;
	
    /**
     * Sets the modules params as a property of the object
     * @param object $params     
     *  
     */
    function __construct( $params )    
    {    	
        $this->_params = $params;          
        $this->_db = JFactory::getDBO();
        $this->_multi_mode = $params->get('multi_mode', 1);       
        $this->_catid = JRequest::getInt('filter_category');
    	$this->_catids = array($this->_catid);    	
    	$this->_itemid = JRequest::getInt('Itemid');
    }      
    
    function getCondition()
    {
    	return $this->_catfound ||$this->_manufound || $this->_pricefound || $this->_attrifound ? true : false;  	
    }
    
    /**
     * 
     * @return array - categories, manufacturers, attributes, price range
     */
    function getLayeredNavigation()
    {
    	
    	
    }
    
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
		    	foreach ($items as $item)
		    	{
		    		$catids[] = $item->category_id;
		    	  	$pmodel = JModel::getInstance('Products', 'TiendaModel');
	                $pmodel->setState('filter_category', $item->category_id);
		            $item->product_total = $pmodel->getTotal();	   	    	       
		    	   	$item->link = JRoute::_($this->_link.'&filter_category='.$item->category_id.'&Itemid='.$this->_itemid);
		    	}
		    	    $this->_catids = array_merge($this->_catids, $catids);
		    }
    	}

    	return $items;
    }
    
    function getManufacturers()
    {
    	$items = array(); 	    	
    	
    	if(!empty($this->_catids))
    	{
    		$query = new TiendaQuery();
			$query->select( 'tbl.manufacturer_id' );
			$query->select( 'm.manufacturer_name' );
			$field[] = "
			(
			SELECT 
				COUNT(*)
			FROM
				#__tienda_products AS p 
			WHERE 	
				p.manufacturer_id = tbl.manufacturer_id 				
			LIMIT 1
			) 
			AS total ";
			$query->select($field);
			$query->from('#__tienda_products AS tbl');   			
			$query->join( 'LEFT', '#__tienda_productcategoryxref AS p2c ON p2c.product_id = tbl.product_id' );	
			$query->join( 'LEFT', '#__tienda_manufacturers AS m ON m.manufacturer_id = tbl.manufacturer_id' );	
			$query->where( 'p2c.category_id IN(' . implode(',', $this->_catids) . ')' );
			$query->where( 'tbl.manufacturer_id != \'0\'' );
			$this->_db->setQuery( (string) $query );
			$items = $this->_db->loadObjectList();

			if(!empty($items))
			{
				$this->_manufound = true;
				foreach($items as $item)
				{					
					$link = $this->_link;
					$link .= '&filter_category='.$this->_catid;	
					$link .= '&filter_manufacturer='.$item->manufacturer_id;
					$link .= '&Itemid='.$this->_itemid;
					$item->link = JRoute::_( $link );											
				}		
			}					
    	}		
   
	    return $items;    	
    }
    
    function getPriceRanges()
    {
    	$ranges = array();
 	
    	if(!empty($this->_catids))
    	{
    		$model = JModel::getInstance( 'Products', 'TiendaModel' );   
        	$model->setState('filter_multicategory', $this->_catids);      
        	$model->setState( 'order', 'price' );
        	$model->setState( 'direction', 'DESC' );  
        	$items = $model->getList(); 
    	
        	if(!empty($items))
        	{
        		$this->_pricefound = true;
        		$priceHigh = abs( $items['0']->price );   
        		$priceLow = ( count($items) == 1 ) ? 0 : abs( $items[count( $items ) - 1]->price );
        		$range = ( abs( $priceHigh ) - abs( $priceLow ) )/4;  

        		//rounding
    			$roundRange = $this->priceRound($range, '100', true);
				$roundPriceLow = $this->priceRound( $priceLow);		
        		
        		$rangeObj = new stdClass();
				$rangeObj->price_from = $roundPriceLow;
				$rangeObj->price_to = $roundRange;
				
				$pmodel = JModel::getInstance('Products', 'TiendaModel');
		        $pmodel->setState('filter_price_from', $rangeObj->price_from);
		        $pmodel->setState('filter_price_to', $rangeObj->price_to);   
				$rangeObj->total = $pmodel->getTotal();
				$link = $this->_link;
				$link .= '&filter_category='.$this->_catid;	
				
				$rangeObj->link = $link.'&filter_price_from='.$rangeObj->price_from.'&filter_price_to='.$rangeObj->price_to.'&Itemid='.$this->_itemid;
        		$ranges[] = $rangeObj;
				for($i = 1; $i <= 3; $i++)
				{
					$rangeObj = new stdClass();
					$rangeObj->price_from = $roundRange * $i;
					$rangeObj->price_to = $roundRange * ( 1 + $i );
					
					$pmodel = JModel::getInstance('Products', 'TiendaModel');
		        	$pmodel->setState('filter_price_from', $rangeObj->price_from);
		        	$pmodel->setState('filter_price_to', $rangeObj->price_to);   
					$rangeObj->total = $pmodel->getTotal();		
					$rangeObj->link = $link.'&filter_price_from='.$rangeObj->price_from.'&filter_price_to='.$rangeObj->price_to.'&Itemid='.$this->_itemid;			
					$ranges[] = $rangeObj;		
				}        	
        	}
    		
    	}
    	
    	return $ranges;
    }
    
    private function priceRound( $price , $digit='100', $up = false )
    {    	
    	$price = ( (int) ( $price/$digit) ) * $digit;    	
    	if( $up ) $price = $price + $digit;   	   	
    	return (int) $price;
    }   

    function getAttribute()
    {
    	
    }
}
?>   
