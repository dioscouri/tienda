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

class modTiendaPriceFiltersHelper extends JObject
{
    /**
     * Sets the modules params as a property of the object
     * @param unknown_type $params
     * @return unknown_type
     */
    function __construct( $params )
    {
        $this->params = $params;
    }
    
    /**
     * Get the price range based on the Highest and lowest prices   
     * @return array
     */
    function getPriceRange()
    {
        // Check the registry to see if our Tienda class has been overridden
        if ( !class_exists('Tienda') ) 
            JLoader::register( "Tienda", JPATH_ADMINISTRATOR."/components/com_tienda/defines.php" );
        
        // load the config class
        Tienda::load( 'Tienda', 'defines' );
                
        JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
    	JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
    						
		$ranges = array();    	
    	$link = '';
    	
        // get the model
    	$model = JModel::getInstance( 'Products', 'TiendaModel' );   
        $app = JFactory::getApplication();
        $ns = $app->getName().'::'.'com.tienda.model.'.$model->getTable()->get('_suffix');
        
    	//check if we are in the manufacturer view
    	$view = JRequest::getWord('view');
    	
    	if ($view == 'manufacturers')
    	{
    		//get the current manufacturer
			$filter_manufacturer = $app->getUserStateFromRequest($ns.'.manufacturer', 'filter_manufacturer', '', 'int');
			
			if ( empty($filter_manufacturer) )	return '';
			
			$model->setState( 'filter_manufacturer', $filter_manufacturer ); 
			
			//create link to be concatinated 
			$link = '&view=manufacturers&layout=products&task=products&filter_manufacturer='.$filter_manufacturer;
			
    	}
    	else 
    	{
    		//get the current category
			$filter_category = $app->getUserStateFromRequest($ns.'.category', 'filter_category', '', 'int');
			
			if ( empty($filter_category) )	return '';
			
			$model->setState( 'filter_category', $filter_category ); 
			
			//create link to be concatinated 
			$link = '&filter_category='.$filter_category;
    		
    	}    	
	  
		//set the direction of the price
        $model->setState( 'order', 'price' );
        $model->setState( 'direction', 'DESC' );            
        
        //get items
        $items = $model->getList();      
        
    	//check if we dont have product in the category	
		if ( empty($items) )
		{	
			$ranges[$link] = JText::_('COM_TIENDA_NO_AVAILABLE_PRODUCT');		
			return $ranges;			
		}
		
        //get the highest price
        $priceHigh = abs( $items['0']->price );
     
      	//get the lowest price
        $priceLow = ( count($items) == 1 ) ? 0 : abs( $items[count( $items ) - 1]->price );
        	
        $range = ( abs( $priceHigh ) - abs( $priceLow ) )/4;        

        //rounding
    	$roundRange = $this->_priceRound($range, $this->params->get( 'round_digit' ), true);
		$roundPriceLow = $this->_priceRound( $priceLow, $this->params->get( 'round_digit' ) );
  	
		$upperPrice = $this->params->get( 'filter_upper_limit' );
		
		//load the helper base class
		Tienda::load( 'TiendaHelperBase', 'helpers._base' );		
	
    	$ranges[$link.'&filter_price_from='.$roundPriceLow.'&filter_price_to='.$roundRange] = TiendaHelperBase::currency($roundPriceLow).' - '.TiendaHelperBase::currency($roundRange);
    	$ranges[$link.'&filter_price_from='.$roundRange.'&filter_price_to='.($roundRange*2)] = TiendaHelperBase::currency($roundRange).' - '.TiendaHelperBase::currency( ( $roundRange*2 ) );
    	$ranges[$link.'&filter_price_from='.($roundRange*2).'&filter_price_to='.($roundRange*3)] = TiendaHelperBase::currency( ( $roundRange*2 ) ).' - '.TiendaHelperBase::currency( ( $roundRange*3 ) );
    	$ranges[$link.'&filter_price_from='.($roundRange*3).'&filter_price_to='.($upperPrice)] = TiendaHelperBase::currency( ( $roundRange*3 ) ).' - '.TiendaHelperBase::currency( $upperPrice );
    	$ranges[$link.'&filter_price_from='.$upperPrice] = JText::_('COM_TIENDA_MORE_THAN_').TiendaHelperBase::currency( $upperPrice );
      
    	return $ranges;
    }
    
    /**
     * Rounding of the the nearest 10s /100s/1000s/ etc depending on the number of digits
     * @param int $price - price of product
     * @param int $digit - how many digit to round
     * @param boolean $up - to round upward
     * @return int
     */
    protected function _priceRound( $price , $digit='100', $up = false )
    {
    	
    	$price = ( (int) ( $price/$digit) ) * $digit;
    	
    	if( $up )
    	{
    		$price = $price + $digit;
    	}
    	
    	return (int) $price;
    }
}
?>
