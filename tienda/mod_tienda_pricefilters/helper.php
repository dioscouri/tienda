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
            JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );
        
        // load the config class
        Tienda::load( 'TiendaConfig', 'defines' );
                
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
    	JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );

    	//get the current category
		$currentcat = JRequest::getInt('filter_category');
    	   	
        // get the model
    	$model = JModel::getInstance( 'Products', 'TiendaModel' );    
        $model->setState( 'filter_category', $currentcat );    
        $model->setState( 'order', 'price' );
        $model->setState( 'direction', 'DESC' );            
         
        $items = $model->getList();
        
        //get the highest price
        $priceHigh = abs( $items['0']->price );
		
        //get the lowes price 
        $priceLow = abs( $items[count( $items ) - 1]->price );
   
        $range = ( abs( $priceHigh ) - abs( $priceLow ) )/4;        
        
        //rounding
    	$roundRange = $this->_priceRound($range, $this->params->get( 'round_digit' ), true);
		$roundPriceLow = $this->_priceRound( $priceLow, $this->params->get( 'round_digit' ) );
    	
		$upperPrice = $this->params->get( 'filter_upper_limit' );
		
		$ranges = array();    	
    	$ranges['&filter_category='.$currentcat.'&filter_price_from='.$roundPriceLow.'&filter_price_to='.$roundRange] = TiendaHelperBase::currency($roundPriceLow).' - '.TiendaHelperBase::currency($roundRange);
    	$ranges['&filter_category='.$currentcat.'&filter_price_from='.$roundRange.'&filter_price_to='.($roundRange*2)] = TiendaHelperBase::currency($roundRange).' - '.TiendaHelperBase::currency( ( $roundRange*2 ) );
    	$ranges['&filter_category='.$currentcat.'&filter_price_from='.($roundRange*2).'&filter_price_to='.($roundRange*3)] = TiendaHelperBase::currency( ( $roundRange*2 ) ).' - '.TiendaHelperBase::currency( ( $roundRange*3 ) );
    	$ranges['&filter_category='.$currentcat.'&filter_price_from='.($roundRange*3).'&filter_price_to='.($roundRange*4)] = TiendaHelperBase::currency( ( $roundRange*3 ) ).' - '.TiendaHelperBase::currency( $upperPrice );
    	$ranges['&filter_category='.$currentcat.'&filter_price_from='.$upperPrice] = JText::_("more than ").TiendaHelperBase::currency( $upperPrice );
    	      
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
