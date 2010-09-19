<?php
/**
* @package		Tienda
* @copyright	Copyright (C) 2009 DT Design Inc. All rights reserved.
* @license		GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
* @link 		http://www.dioscouri.com
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

require_once( JPATH_SITE.DS.'libraries'.DS.'joomla'.DS.'html'.DS.'html'.DS.'select.php' );

class TiendaSelect extends JHTMLSelect
{
	/**
	* Generates a yes/no radio list
	*
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param mixed The key that is selected
	* @returns string HTML for the radio list
	*/
	public static function booleans( $selected, $name = 'filter_enabled', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false, $title='Select State', $yes = 'Enabled', $no = 'Disabled' )
	{
	    $list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_( $title )." -" );
		}

		$list[] = JHTML::_('select.option',  '0', JText::_( $no ) );
		$list[] = JHTML::_('select.option',  '1', JText::_( $yes ) );

		return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
	}

	/**
	* Generates range list
	*
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param mixed The key that is selected
	* @returns string HTML for the radio list
	*/
	public static function range( $selected, $name = 'filter_range', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false, $title = 'Select Range' )
	{
	    $list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_( $title )." -" );
		}

		$list[] = JHTML::_('select.option',  'today', JText::_( "Today" ) );
		$list[] = JHTML::_('select.option',  'yesterday', JText::_( "Yesterday" ) );
		$list[] = JHTML::_('select.option',  'last_seven', JText::_( "Last Seven Days" ) );
		$list[] = JHTML::_('select.option',  'last_thirty', JText::_( "Last Thirty Days" ) );
		$list[] = JHTML::_('select.option',  'ytd', JText::_( "Year to Date" ) );

		return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
	}
	
    /**
    * Generates range list
    *
    * @param string The value of the HTML name attribute
    * @param string Additional HTML attributes for the <select> tag
    * @param mixed The key that is selected
    * @returns string HTML for the radio list
    */
    public static function reportrange( $selected, $name = 'filter_range', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false, $title = 'Select Range' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  'custom', JText::_( "Custom" ) );
        $list[] = JHTML::_('select.option',  'yesterday', JText::_( "Yesterday" ) );
        $list[] = JHTML::_('select.option',  'last_week', JText::_( "Last Week" ) );
        $list[] = JHTML::_('select.option',  'last_month', JText::_( "Last Month" ) );
        $list[] = JHTML::_('select.option',  'ytd', JText::_( "Year to Date" ) );
        $list[] = JHTML::_('select.option',  'all', JText::_( "All Time" ) );
        
        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }

    /**
    * Generates a created/modified select list
    *
    * @param string The value of the HTML name attribute
    * @param string Additional HTML attributes for the <select> tag
    * @param mixed The key that is selected
    * @returns string HTML for the radio list
    */
    public static function datetype( $selected, $name = 'filter_datetype', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false, $title='Select Type' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  'created', JText::_( "Created" ) );
        $list[] = JHTML::_('select.option',  'modified', JText::_( "Modified" ) );
        $list[] = JHTML::_('select.option',  'shipped', JText::_( "Shipped" ) );
        
        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }
    
	/**
	 *
	 * @param $selected
	 * @param $name
	 * @param $attribs
	 * @param $idtag
	 * @param $allowAny
	 * @return unknown_type
	 */
	public static function category($selected, $name = 'filter_parentid', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false, $allowNone = false, $title = 'Select Category', $title_none = 'No Parent', $enabled = null )
 	{
		// Build list
        $list = array();
		if ($allowAny) {
			$list[] =  self::option('', "- ".JText::_( $title )." -", 'category_id', 'category_name' );
		}
 	 	if ($allowNone) {
			JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
			$root = JTable::getInstance('Categories', 'TiendaTable')->getRoot();
			$list[] =  self::option( $root->category_id, "- ".JText::_( $title_none )." -", 'category_id', 'category_name' );
		}

		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$model = JModel::getInstance( 'Categories', 'TiendaModel' );
		$model->setState('order', 'tbl.lft');
		if (intval($enabled) == '1')
		{
			// get only the enabled items in the tree
			// this would be used for the front-end
			$items = $model->getTable()->getTree();
		}
			else
		{
			$items = $model->getList();
		}

        foreach (@$items as $item)
        {
        	$list[] =  self::option( $item->category_id, str_repeat( '.&nbsp;', $item->level-1 ).JText::_($item->name), 'category_id', 'category_name' );
        }
		return self::genericlist($list, $name, $attribs, 'category_id', 'category_name', $selected, $idtag );
 	}

	/**
	 *
	 * @param $selected
	 * @param $name
	 * @param $attribs
	 * @param $idtag
	 * @param $allowAny
	 * @return unknown_type
	 */
	public static function manufacturer($selected, $name = 'filter_manufacturerid', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false, $allowNone = false, $title = 'Select Manufacturer', $title_none = 'No Manufacturer', $enabled = null )
 	{
 		// Build list
        $list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_( $title )." -", 'manufacturer_id', 'manufacturer_name' );

		}
 	 	if($allowNone) {
 	 		$list[] =  self::option('0', "- ".JText::_( $title_none )." -", 'manufacturer_id', 'manufacturer_name' );
		}

		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$model = JModel::getInstance( 'Manufacturers', 'TiendaModel' );
		$model->setState( 'order', 'manufacturer_name' );
		$model->setState( 'direction', 'ASC' );
		$items = $model->getList();
        foreach (@$items as $item)
        {
        	$list[] =  self::option( $item->manufacturer_id, JText::_($item->manufacturer_name), 'manufacturer_id', 'manufacturer_name' );
        }

		return self::genericlist($list, $name, $attribs, 'manufacturer_id', 'manufacturer_name', $selected, $idtag );
 	}
 	
 	/**
 	 * 
 	 * @param unknown_type $selected
 	 * @param unknown_type $name
 	 * @param unknown_type $attribs
 	 * @param unknown_type $idtag
 	 * @param unknown_type $allowAny
 	 * @param unknown_type $allowNone
 	 * @param unknown_type $title
 	 * @param unknown_type $title_none
 	 * @return unknown_type
 	 */
	public static function taxclass($selected, $name = 'filter_tax_class_id', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false, $allowNone = false, $title = 'Select Tax Class', $title_none = 'No Tax Class' )
 	{
 		// Build list
        $list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_( $title )." -", 'tax_class_id', 'tax_class_name' );

		}
 	 	if($allowNone) {
 	 		$list[] =  self::option('0', "- ".JText::_( $title_none )." -", 'tax_class_id', 'tax_class_name' );
		}

		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$model = JModel::getInstance( 'Taxclasses', 'TiendaModel' );
		$model->setState( 'order', 'ordering' );
		$model->setState( 'direction', 'ASC' );
		$items = $model->getList();
        foreach (@$items as $item)
        {
        	$list[] =  self::option( $item->tax_class_id, JText::_($item->tax_class_name), 'tax_class_id', 'tax_class_name' );
        }

		return self::genericlist($list, $name, $attribs, 'tax_class_id', 'tax_class_name', $selected, $idtag );
 	}

	/**
	 *
	 * @param $selected
	 * @param $name
	 * @param $attribs
	 * @param $idtag
	 * @param $allowAny
	 * @return unknown_type
	 */
	public static function country($selected, $name = 'filter_countryid', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false, $enabled = null)
 	{
        $list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_( 'Select Country' )." -", 'country_id', 'country_name' );
		}

		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$model = JModel::getInstance( 'Countries', 'TiendaModel' );
		if (!empty($enabled))
		{
            $model->setState( 'filter_enabled', '1' );
		}
		$model->setState( 'order', 'ordering' );
		$model->setState( 'direction', 'ASC' );
		$items = $model->getList();
        foreach (@$items as $item)
        {
        	$list[] =  self::option( $item->country_id, JText::_($item->country_name), 'country_id', 'country_name' );
        }

		return self::genericlist($list, $name, $attribs, 'country_id', 'country_name', $selected, $idtag );
 	}

	/**
	 *
	 * @param $selected
	 * @param $name
	 * @param $attribs
	 * @param $idtag
	 * @param $allowAny
	 * @return unknown_type
	 */
	public static function geozonetypes($selected, $name = 'filter_geozonetype', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false)
 	{
        $list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_( 'Select Geo Zone Type' )." -", 'geozonetype_id', 'geozonetype_name' );
		}

		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$model = JModel::getInstance( 'Geozonetypes', 'TiendaModel' );
		$model->setState( 'order', 'geozonetype_name' );
		$model->setState( 'direction', 'ASC' );
		$items = $model->getList();
        foreach (@$items as $item)
        {
        	$list[] =  self::option( $item->geozonetype_id, JText::_($item->geozonetype_name), 'geozonetype_id', 'geozonetype_name' );
        }

		return self::genericlist($list, $name, $attribs, 'geozonetype_id', 'geozonetype_name', $selected, $idtag );
 	}

	/**
	 *
	 * @param $selected
	 * @param $name
	 * @param $countryid  REQUIRED, therefore should be before $name
	 * @param $attribs
	 * @param $idtag
	 * @param $allowAny
	 * @return unknown_type
	 */
	public static function zone($selected, $name = 'filter_zoneid', $countryid,  $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false)
 	{
        $list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_( 'Select Zone' )." -", 'zone_id', 'zone_name' );
		}

		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$model = JModel::getInstance( 'Zones', 'TiendaModel' );
		$model->setState( 'order', 'zone_name' );
		$model->setState( 'direction', 'ASC' );
		if ($countryid !== null)
		{
			$model->setState( 'filter_countryid', $countryid );
		}
		$items = $model->getList();
        foreach (@$items as $item)
        {
        	$list[] =  self::option( $item->zone_id, JText::_($item->zone_name), 'zone_id', 'zone_name' );
        }
        
		return self::genericlist($list, $name, $attribs, 'zone_id', 'zone_name', $selected, $idtag );
 	}

	/**
	 *
	 * @param $selected
	 * @param $name
	 * @param $attribs
	 * @param $idtag
	 * @param $allowAny
	 * @return unknown_type
	 */
	public static function geozone($selected, $name = 'filter_geozoneid', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false)
 	{
 		// TODO Make these static?
 		
        $list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_( 'Select Geo Zone' )." -", 'geozone_id', 'geozone_name' );
		}

		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$model = JModel::getInstance( 'Geozones', 'TiendaModel' );
		$model->setState( 'order', 'geozone_name' );
		$model->setState( 'direction', 'ASC' );
		$items = $model->getList();
        foreach (@$items as $item)
        {
        	$list[] =  self::option( $item->geozone_id, JText::_($item->geozone_name), 'geozone_id', 'geozone_name' );
        }

		return self::genericlist($list, $name, $attribs, 'geozone_id', 'geozone_name', $selected, $idtag );
 	}
 	
 	/**
 	 * 
 	 * @param unknown_type $selected
 	 * @param unknown_type $name
 	 * @param unknown_type $attribs
 	 * @param unknown_type $idtag
 	 * @param unknown_type $allowAny
 	 * @return unknown_type
 	 */
	public static function currency($selected, $name = 'filter_currency_id', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false)
 	{
        $list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_( 'Select Currency' )." -", 'currency_id', 'currency_code' );
		}

		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$model = JModel::getInstance( 'currencies', 'TiendaModel' );
		$items = $model->getList();
        foreach (@$items as $item)
        {
        	$list[] =  self::option( $item->currency_id, JText::_($item->currency_code), 'currency_id', 'currency_code' );
        }

		return self::genericlist($list, $name, $attribs, 'currency_id', 'currency_code', $selected, $idtag );
 	} 
 	/**
 	 * 
 	 * @param unknown_type $selected
 	 * @param unknown_type $name
 	 * @param unknown_type $attribs
 	 * @param unknown_type $idtag
 	 * @param unknown_type $allowAny
 	 * @return unknown_type
 	 */
public static function selectsort($selected, $name = 'default_selectsort', $attribs=null , $idtag = null, $allowAny = false)
{
	    $attribs= array('class' => 'inputbox', 'size' => '1','onchange'=>'test()');
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  'created_date', JText::_( "Date" ) );
        $list[] = JHTML::_('select.option',  'productcomment_rating', JText::_( "Rating" ) );
        $list[] = JHTML::_('select.option',  'helpful_votes_total', JText::_( "Helpfulness" ) );
       
        

        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }	

 	/**
 	 * TODO Is $type even necessary? 
 	 * I thought we were going to assume ALL addresses could be both shipping and billing
 	 *  
 	 * @param unknown_type $userid  REQUIRED, therefore at beginning of param list 
 	 * @param unknown_type $selected
 	 * @param unknown_type $name
 	 * @param unknown_type $type
 	 * @param unknown_type $attribs
 	 * @param unknown_type $idtag
 	 * @param unknown_type $allowAny
 	 * @return unknown_type
 	 */
	public static function address($userid, $selected, $name = 'filter_address_id', $type = 1, $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false)
 	{
 		// TODO return empty array?
 		if (empty($userid))
 		{
 			return;
 		}

 		$address_type = '';
		switch($type)
		{
			case 1:
				$address_type = JText::_('Billing');
				break;
			case 2:
				$address_type = JText::_('Shipping');
				break;
		} 		
	
        $list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_( 'Select '.$address_type.' Address' )." -", 'address_id', 'address_name' );
		}
		
		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$model = JModel::getInstance( 'addresses', 'TiendaModel' );
		$model->setState("filter_userid", $userid);
		$model->setState("filter_deleted", 0);
		$items = $model->getList();
        foreach (@$items as $item)
        {
        	// TODO This shouldn't be set here.  it should be set $selected when the function is called
        	if (($item->is_default_billing && ($type == 1)) || ($item->is_default_shipping && ($type == 2))) {
        		$selected = $item->address_id;
        	}
        	$list[] =  self::option( $item->address_id, JText::_($item->address_name), 'address_id', 'address_name' );
        }
        
        if (count($list) == 1)
        {
        	return;
        }
        else
        {
        	return self::genericlist($list, $name, $attribs, 'address_id', 'address_name', $selected, $idtag );
        }
 	}
 	
 	/**
 	 * Displays a select list of the user's orders
 	 * 
 	 * @param unknown_type $user_id
 	 * @param unknown_type $selected
 	 * @param unknown_type $name
 	 * @param unknown_type $attribs
 	 * @param unknown_type $idtag
 	 * @param unknown_type $allowAny
 	 * @param unknown_type $title
 	 * @return unknown_type
 	 */
    public static function order($user_id, $selected = '', $name = 'filter_order', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = true, $title = 'Select Order' )
    {
        if (empty($user_id))
        {
            return JText::_("Invalid User");
        }

        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -", 'value', 'text' );
        }
        
        $model = Tienda::getClass('TiendaModelOrders', 'models.orders');
        $model->setState("filter_userid", $user_id);
        $items = $model->getList();
        foreach ($items as $item)
        {
            $title = "# " .$item->order_id;
            $title .= " - " . JHTML::_('date', $item->created_date, TiendaConfig::getInstance()->get('date_format'));
            $list[] = JHTML::_('select.option', $item->order_id, $title );  
        }

        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }
 	
    /**
     * 
     * @param $selected
     * @param $name
     * @param $attribs
     * @param $idtag
     * @param $allowAny
     * @param $allowNone
     * @param $title
     * @param $title_none
     * @return unknown_type
     */
    public static function addressaction($selected, $name = 'filter_action', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false, $allowNone = false, $title = 'Select Action' )
    {
        $list = array();
        if($allowAny) 
        {
            $list[] =  self::option('', "- ".JText::_( $title )." -", 'value', 'text' );
        }

        $list[] = JHTML::_('select.option',  'flag_shipping', JText::_( "Use as Default for Shipping" ) );
        $list[] = JHTML::_('select.option',  'flag_billing', JText::_( "Use as Default for Billing" ) );
        $list[] = JHTML::_('select.option',  'flag_deleted', JText::_( "Delete" ) );
        
        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }

 	/**
 	 * 
 	 * @param unknown_type $selected
 	 * @param unknown_type $name
 	 * @param unknown_type $attribs
 	 * @param unknown_type $idtag
 	 * @param unknown_type $allowAny
 	 * @return unknown_type
 	 */
	public static function orderstate($selected, $name = 'filter_orderstateid', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false)
 	{
        $list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_( 'Select State' )." -", 'order_state_id', 'order_state_name' );
		}

		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$model = JModel::getInstance( 'OrderStates', 'TiendaModel' );
		$items = $model->getList();
        foreach (@$items as $item)
        {
        	$list[] =  self::option( $item->order_state_id, JText::_($item->order_state_name), 'order_state_id', 'order_state_name' );
        }

		return self::genericlist($list, $name, $attribs, 'order_state_id', 'order_state_name', $selected, $idtag );
 	}
 	
    /**
    * Generates shipping method type list
    *
    * @param string The value of the HTML name attribute
    * @param string Additional HTML attributes for the <select> tag
    * @param mixed The key that is selected
    * @returns string HTML for the radio list
    */
    public static function shippingtype( $selected, $name = 'filter_shipping_method_type', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false, $title = 'Select Shipping Type' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }
        Tienda::load( 'TiendaHelperShipping', 'helpers.shipping' );
        $items = TiendaHelperShipping::getTypes();
        foreach ($items as $item)
        {
            $list[] = JHTML::_('select.option', $item->id, $item->title );	
        }

        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }

    /**
     * Generates a selectlist for shipping methods
     * 
     * @param unknown_type $selected
     * @param unknown_type $name
     * @param unknown_type $attribs
     * @param unknown_type $idtag
     * @return unknown_type
     */
	public static function shippingmethod( $selected, $name = 'filter_shipping_method', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null )
	{
	    $list = array();

		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$model = JModel::getInstance( 'shippingmethods', 'TiendaModel' );
		$model->setState('filter_enabled', true);
		$items = $model->getList();
        foreach (@$items as $item)
        {
        	$list[] =  self::option( $item->shipping_method_id, JText::_($item->shipping_method_name));
        }
		return JHTML::_('select.radiolist', $list, $name, $attribs, 'value', 'text', $selected, $idtag);
	}

	/**
	 * Generates a selectlist for the specified Product Attribute 
	 *
	 * @param unknown_type $productattribute_id 
	 * @param unknown_type $selected
	 * @param unknown_type $name
	 * @param unknown_type $attribs
	 * @param unknown_type $idtag
	 * @return unknown_type
	 */
    public static function productattributeoptions( $productattribute_id, $selected, $name = 'filter_pao', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null)
    {
        $list = array();
        
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'ProductAttributeOptions', 'TiendaModel' );
        $model->setState( 'filter_attribute', $productattribute_id );
        $model->setState('order', 'tbl.ordering');
        $items = $model->getList();
        foreach (@$items as $item)
        {
        	$display_suffix = ($item->productattributeoption_price > '0') ? ": ".$item->productattributeoption_prefix.TiendaHelperBase::currency($item->productattributeoption_price) : '';
        	$display_name = JText::_($item->productattributeoption_name).$display_suffix;
            $list[] =  self::option( $item->productattributeoption_id, $display_name );
        }
        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag  );
    }	
	
    
     /**
	 * Generates a +/- select list for pao prefixes
	 * 
	 * @param unknown_type $selected
	 * @param unknown_type $name
	 * @param unknown_type $attribs
	 * @param unknown_type $idtag
	 * @param unknown_type $allowAny
	 * @param unknown_type $title
	 * @return unknown_type
	 */
    public static function productattributeoptionprefix( $selected, $name = 'filter_prefix', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false, $title = 'Select Prefix' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  '+', "+" );
        $list[] = JHTML::_('select.option',  '-', "-" );

        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }
    
    /**
    * Generates a Period Unit Select List for recurring payments
    *
    * @param string The value of the HTML name attribute
    * @param string Additional HTML attributes for the <select> tag
    * @param mixed The key that is selected
    * @returns string HTML for the radio list
    */
    public static function periodUnit( $selected, $name = 'filter_periodunit', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false, $title='Select Period Unit' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  'D', JText::_( "Day" ) );
        $list[] = JHTML::_('select.option',  'W', JText::_( "Week" ) );
        $list[] = JHTML::_('select.option',  'M', JText::_( "Month" ) );
        $list[] = JHTML::_('select.option',  'Y', JText::_( "Year" ) );
        
        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }
    
    /**
     * 
     * Enter description here ...
     * @param unknown_type $selected
     * @param unknown_type $name
     * @param unknown_type $attribs
     * @param unknown_type $idtag
     * @param unknown_type $allowAny
     * @param unknown_type $title
     * @return unknown_type
     */
    public static function relationship( $selected, $name = 'filter_relationtype', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false, $title='Select Relationship' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  'relates', JText::_( "Relationship Relates" ) );
        $list[] = JHTML::_('select.option',  'requires', JText::_( "Relationship Requires" ) );
        $list[] = JHTML::_('select.option',  'required_by', JText::_( "Relationship required_by" ) );
        $list[] = JHTML::_('select.option',  'requires_past', JText::_( "Relationship requires_past" ) );
        $list[] = JHTML::_('select.option',  'requires_current', JText::_( "Relationship requires_current" ) );
        $list[] = JHTML::_('select.option',  'child', JText::_( "Relationship Child" ) );
        $list[] = JHTML::_('select.option',  'parent', JText::_( "Relationship Parent" ) );
                
        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }
    
    /**
     * 
     * @param unknown_type $selected
     * @param unknown_type $name
     * @param unknown_type $attribs
     * @param unknown_type $idtag
     * @param unknown_type $allowAny
     * @param unknown_type $title
     * @return unknown_type
     */
    public static function productlayout( $selected, $name = 'filter_productlayout', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = true, $title='Select Layout' )
    {
        $list = array();
        if($allowAny) 
        {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $items = Tienda::getClass( "TiendaHelperProduct", 'helpers.product' )->getLayouts();
        foreach ($items as $item)
        {
            $namebits = explode('.', $item);
            $value = $namebits[0];
            $list[] =  self::option( $value, $item );
        }
        
        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }
    
    /**
     * 
     * @param unknown_type $selected
     * @param unknown_type $name
     * @param unknown_type $attribs
     * @param unknown_type $idtag
     * @param unknown_type $allowAny
     * @param unknown_type $title
     * @return unknown_type
     */
    public static function categorylayout( $selected, $name = 'filter_categorylayout', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = true, $title='Select Layout' )
    {
        $list = array();
        if($allowAny) 
        {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $items = Tienda::getClass( "TiendaHelperCategory", 'helpers.category' )->getLayouts();
        foreach ($items as $item)
        {
            $namebits = explode('.', $item);
            $value = $namebits[0];
            $list[] =  self::option( $value, $item );
        }
        
        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }
    
    /**
    * Generates range list
    *
    * @param string The value of the HTML name attribute
    * @param string Additional HTML attributes for the <select> tag
    * @param mixed The key that is selected
    * @returns string HTML for the radio list
    */
    public static function addtocartaction( $selected, $name = 'filter_addtocartaction', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false, $title = 'Select Action' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  '0', JText::_( "Remain on Product Page" ) );
        // $list[] = JHTML::_('select.option',  'lightbox', JText::_( "Display Minicart in Lightbox" ) );
        $list[] = JHTML::_('select.option',  'redirect', JText::_( "Redirect to Cart" ) );

        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }
    
    /**
    * Generates range list
    *
    * @param string The value of the HTML name attribute
    * @param string Additional HTML attributes for the <select> tag
    * @param mixed The key that is selected
    * @returns string HTML for the radio list
    */
    public static function displaywithtax( $selected, $name = 'filter_displaywithtax', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false, $title = 'Display Prices With Tax' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  '0', JText::_( "Do Not Display Tax" ) );
        $list[] = JHTML::_('select.option',  '1', JText::_( "Display Tax Next to Price" ) );
        $list[] = JHTML::_('select.option',  '2', JText::_( "Sum the Tax and Product Price" ) );

        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }
    
    /**
    * Generates range list
    *
    * @param string The value of the HTML name attribute
    * @param string Additional HTML attributes for the <select> tag
    * @param mixed The key that is selected
    * @returns string HTML for the radio list
    */
    public static function cartbutton( $selected, $name = 'filter_cartbutton', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false, $title = 'Select Cart Button' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  'image', JText::_( "Image" ) );
        $list[] = JHTML::_('select.option',  'button', JText::_( "Button" ) );

        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }
    
    /**
     * Getting list of products
     *
     */
     
	public static function product($selected, $name = 'product_id', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false, $allowNone = false, $title = 'Select product', $title_none = 'No Parent', $enabled = null )
 	{
		// Build list
        $list = array();
		if ($allowAny) {
			$list[] =  self::option('', "- ".JText::_( $title )." -", 'product_id', 'product_name' );
		}
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$model = JModel::getInstance( 'products', 'TiendaModel' );
		$items = $model->getList();
		foreach (@$items as $item)
        {
        	$list[] =  self::option( $item->product_id,JText::_($item->product_name), 'product_id', 'product_name' );
        }
		return self::genericlist($list, $name, $attribs, 'product_id', 'product_name', $selected, $idtag );
 	}

	/*
	 * getting list of users
	 */
 	public static function users($selected, $name = 'userid', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false, $allowNone = false, $title = 'Select User', $title_none = 'No Parent', $enabled = null )
 	{
		// Build list
        $list = array();
		if ($allowAny) {
			$list[] =  self::option('', "- ".JText::_( $title )." -", 'id', 'name' );
		}
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$model = JModel::getInstance( 'users', 'TiendaModel' );
		$items = $model->getList();
		foreach (@$items as $item)
        {
        	$list[] =  self::option( $item->id,JText::_($item->name), 'id', 'name' );
        }
		return self::genericlist($list, $name, $attribs, 'id', 'name', $selected, $idtag );
 	}
    
 	/*
	 * getting list of groups
	 */
 	public static function groups($selected, $name = 'user_group_id', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false, $allowNone = false, $title = 'Select Group', $title_none = 'No Group', $enabled = null )
 	{
		// Build list
        $list = array();
		if ($allowAny) {
			$list[] =  self::option('', "- ".JText::_( $title )." -", 'group_id', 'group_name' );
		}
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$model = JModel::getInstance( 'Groups', 'TiendaModel' );
		$items = $model->getList();
		foreach (@$items as $item)
        {
        	$list[] =  self::option( $item->group_id,JText::_($item->group_name), 'group_id', 'group_name' );
        }
		return self::genericlist($list, $name, $attribs, 'group_id', 'group_name', $selected, $idtag );
 	}
    
    /**
    * 
    *
    * @param string The value of the HTML name attribute
    * @param string Additional HTML attributes for the <select> tag
    * @param mixed The key that is selected
    * @returns string HTML for the radio list
    */
    public static function coupongroup( $selected, $name = 'filter_coupongroup', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $allowAny = false, $title = 'Select Coupon Group' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  'price', JText::_( "Price" ) );

        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }
        
}
