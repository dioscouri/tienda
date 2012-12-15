<?php
/**
* @package		Tienda
* @copyright	Copyright (C) 2009 DT Design Inc. All rights reserved.
* @license		GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
* @link 		http://www.dioscouri.com
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

class TiendaSelect extends DSCSelect
{
    /**
     * A boolean radiolist that uses bootstrap
     *  
     * @param unknown_type $name
     * @param unknown_type $attribs
     * @param unknown_type $selected
     * @param unknown_type $yes
     * @param unknown_type $no
     * @param unknown_type $id
     * @return string
     */
	public static function btbooleanlist($name, $attribs = null, $selected = null, $yes = 'JYES', $no = 'JNO', $id = false)
	{
	    JHTML::_('script', 'bootstrapped-advanced-ui.js', 'media/com_tienda/js/');
	    JHTML::_('stylesheet', 'bootstrapped-advanced-ui.css', 'media/com_tienda/css/');
	    $arr = array(JHtml::_('select.option', '0', JText::_($no)), JHtml::_('select.option', '1', JText::_($yes)));
	    $html = '<div class="control-group"><div class="controls"><fieldset id="'.$name.'" class="radio btn-group">';
	    $html .=  TiendaSelect::btradiolist( $arr, $name, $attribs, 'value', 'text', (int) $selected, $id);
	    $html .= '</fieldset></div></div>';

	    return $html;
	}
	
	/**
	 * A standard radiolist that uses bootstrap
	 * 
	 * @param unknown_type $data
	 * @param unknown_type $name
	 * @param unknown_type $attribs
	 * @param unknown_type $optKey
	 * @param unknown_type $optText
	 * @param unknown_type $selected
	 * @param unknown_type $idtag
	 * @param unknown_type $translate
	 * @return string
	 */
	public static function btradiolist($data, $name, $attribs = null, $optKey = 'value', $optText = 'text', $selected = null, $idtag = false, $translate = false)
	{
	    reset($data);
	    $html = '';

	    if (is_array($attribs))
	    {
	        $attribs = JArrayHelper::toString($attribs);
	    }

	    $id_text = $idtag ? $idtag : $name;

	    foreach ($data as $obj)
	    {
	        $k = $obj->$optKey;
	        $t = $translate ? JText::_($obj->$optText) : $obj->$optText;
	        $id = (isset($obj->id) ? $obj->id : null);

	        $extra = '';
	        $extra .= $id ? ' id="' . $obj->id . '"' : '';
	        if (is_array($selected))
	        {
	            foreach ($selected as $val)
	            {
	                $k2 = is_object($val) ? $val->$optKey : $val;
	                if ($k == $k2)
	                {
	                    $extra .= ' selected="selected"';
	                    break;
	                }
	            }
	        }
	        else
	        {
	            $extra .= ((string) $k == (string) $selected ? ' checked="checked"' : '');
	        }
	        
	        $active ='';
	        if(!empty($k)) {
	            $active = 'active';
	        }
	        
	        $html .= "\n\t" . '<input type="radio" name="' . $name . '"' . ' id="' . $id_text . $k . '" value="' . $k . '"' . ' ' . $extra . ' '
	        . $attribs . '/>' . "\n\t" . '<label for="' . $id_text . $k . '"' . ' id="' . $id_text . $k . '-lbl" class="btn">' . $t
	        . '</label>';
	    }
	    
	    $html .= "\n";
	    
	    return $html;
	}
	
	/**
	* Generates range list
	*
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param mixed The key that is selected
	* @returns string HTML for the radio list
	*/
	public static function range( $selected, $name = 'filter_range', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title = 'COM_TIENDA_SELECT_RANGE' )
	{
	    $list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_( $title )." -" );
		}

		$list[] = JHTML::_('select.option',  'today', JText::_('COM_TIENDA_TODAY') );
		$list[] = JHTML::_('select.option',  'yesterday', JText::_('COM_TIENDA_YESTERDAY') );
		$list[] = JHTML::_('select.option',  'last_seven', JText::_('COM_TIENDA_LAST_SEVEN_DAYS') );
		$list[] = JHTML::_('select.option',  'last_thirty', JText::_('COM_TIENDA_LAST_THIRTY_DAYS') );
		$list[] = JHTML::_('select.option',  'ytd', JText::_('COM_TIENDA_YEAR_TO_DATE') );
		$list[] = JHTML::_('select.option',  'annually', JText::_('COM_TIENDA_ANNUALLY') );

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
    public static function reportrange( $selected, $name = 'filter_range', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title = 'COM_TIENDA_SELECT_RANGE' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  'custom', JText::_('COM_TIENDA_CUSTOM') );
        $list[] = JHTML::_('select.option',  'yesterday', JText::_('COM_TIENDA_YESTERDAY') );
        $list[] = JHTML::_('select.option',  'last_week', JText::_('COM_TIENDA_LAST_WEEK') );
        $list[] = JHTML::_('select.option',  'last_month', JText::_('COM_TIENDA_LAST_MONTH') );
        $list[] = JHTML::_('select.option',  'ytd', JText::_('COM_TIENDA_YEAR_TO_DATE') );
        $list[] = JHTML::_('select.option',  'all', JText::_('COM_TIENDA_ALL_TIME') );
        
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
    public static function datetype( $selected, $name = 'filter_datetype', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title='COM_TIENDA_SELECT_TYPE' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  'created', JText::_('COM_TIENDA_CREATED') );
        $list[] = JHTML::_('select.option',  'modified', JText::_('COM_TIENDA_MODIFIED') );
        $list[] = JHTML::_('select.option',  'shipped', JText::_('COM_TIENDA_SHIPPED') );
        
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
    public static function paymentType( $selected, $name = 'filter_type', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title='COM_TIENDA_SELECT_TYPE' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }
		 $db = JFactory::getDbo();
        $q = new DSCQuery();
        
        $q->select( 'SELECT DISTINCT orderpayment_type as type' );
        $q->from( '#__tienda_orderpayments' );
        
	      $db->setQuery( $q );
        $items = $db->loadObjectList();
		
		foreach($items as $item) {
			$list[] = JHTML::_('select.option',  $item->type, JText::_($item->type) );
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
	 * @return unknown_type
	 */
	public static function category($selected, $name = 'filter_parentid', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $allowNone = false, $title = 'Select Category', $title_none = 'COM_TIENDA_NO_PARENT', $enabled = null, $disabled = array() )
 	{
		// Build list
        $list = array();
		if ($allowAny) {
			$list[] =  self::option('', "- ".JText::_( $title )." -", 'category_id', 'category_name' );
			$list[] =  self::option('none', "- ".JText::_('COM_TIENDA_ORPHAN_PRODUCTS')." -", 'category_id', 'category_name' );
		}
 	 if ($allowNone) {
			JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
			$root = JTable::getInstance('Categories', 'TiendaTable')->getRoot();
			$list[] =  self::option( $root->category_id, "- ".JText::_( $title_none )." -", 'category_id', 'category_name' );
		}

		JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
		JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
		
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
        	$level = $item->level;
        	if($level == 0)
        	{
        		$level = 1;
        	}
			$disable = false;
			if(in_array($item->category_id,$disabled)) {
			$disable = true;	
			}
			
        	$list[] =  self::option( $item->category_id, str_repeat( '.&nbsp;', $level-1 ).JText::_($item->name), 'category_id', 'category_name', $disable );
       		
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
	public static function manufacturer($selected, $name = 'filter_manufacturerid', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $allowNone = false, $title = 'Select Manufacturer', $title_none = 'No Manufacturer', $enabled = null )
 	{
 		// Build list
        $list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_( $title )." -", 'manufacturer_id', 'manufacturer_name' );

		}
 	 	if($allowNone) {
 	 		$list[] =  self::option('0', "- ".JText::_( $title_none )." -", 'manufacturer_id', 'manufacturer_name' );
		}

		JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
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
	public static function taxclass($selected, $name = 'filter_tax_class_id', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $allowNone = false, $title = 'COM_TIENDA_SELECT_TAX_CLASS', $title_none = 'COM_TIENDA_NO_TAX_CLASS' )
 	{
 		// Build list
        $list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_( $title )." -", 'tax_class_id', 'tax_class_name' );

		}
 	 	if($allowNone) {
 	 		$list[] =  self::option('0', "- ".JText::_( $title_none )." -", 'tax_class_id', 'tax_class_name' );
		}

		JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
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
	public static function country($selected, $name = 'filter_countryid', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $enabled = null)
 	{
        $list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_('COM_TIENDA_SELECT_COUNTRY')." -", 'country_id', 'country_name' );
		}

		JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
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
        	$list[] =  self::option( $item->country_id, $item->country_name, 'country_id', 'country_name' );
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
	public static function geozonetypes($selected, $name = 'filter_geozonetype', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false)
 	{
        $list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_('COM_TIENDA_SELECT_GEO_ZONE_TYPE')." -", 'geozonetype_id', 'geozonetype_name' );
		}

		JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
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
	public static function zone($selected, $name = 'filter_zoneid', $countryid,  $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false)
 	{
        $list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_('COM_TIENDA_SELECT_ZONE')." -", 'zone_id', 'zone_name' );
		}

		JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
		$model = JModel::getInstance( 'Zones', 'TiendaModel' );
		$model->setState( 'order', 'zone_name' );
		$model->setState( 'direction', 'ASC' );
		if ($countryid !== null)
		{
			$model->setState( 'filter_countryid', $countryid );
		}
		$items = $model->getList();
		
		if(!$items)//empty
		{
			$txt = '<span style="color: #FF0000;">';
			$txt .= JText::_('COM_TIENDA_NO_AVAILABLE_ZONES_FOR_THE_SELECTED_COUNTRY');
			$txt .= '</span>';			
			return $txt;
		}
		
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
	 * @param $type
	 * @param $attribs
	 * @param $idtag
	 * @param $allowAny
	 * @return unknown_type
	 */
	public static function geozone($selected, $name = 'filter_geozoneid', $type = '', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false)
 	{
 		// TODO Make these static?
 		
        $list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_('COM_TIENDA_SELECT_GEO_ZONE')." -", 'geozone_id', 'geozone_name' );
		}

		JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
		$model = JModel::getInstance( 'Geozones', 'TiendaModel' );
		$model->setState( 'order', 'geozone_name' );
		$model->setState( 'direction', 'ASC' );
		$model->setState( 'filter_geozonetype', $type );
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
	public static function currency($selected, $name = 'filter_currency_id', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false)
 	{
        $list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_('COM_TIENDA_SELECT_CURRENCY')." -", 'currency_id', 'currency_code' );
		}

		JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
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
	public static function selectsort($selected, $name = 'default_selectsort', $attribs = array('class' => 'inputbox') , $idtag = null, $allowAny = false)
	{
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  'created_date', JText::_('COM_TIENDA_DATE') );
        $list[] = JHTML::_('select.option',  'productcomment_rating', JText::_('COM_TIENDA_RATING') );
        $list[] = JHTML::_('select.option',  'helpful_votes_total', JText::_('COM_TIENDA_HELPFULNESS') );

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
 	 * @param unknown_type $addNew
 	 * @return unknown_type
 	 */
	public static function address($userid, $selected, $name = 'filter_address_id', $type = 1, $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $addNew = false )
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
				$address_type = JText::_('COM_TIENDA_BILLING');
				break;
			case 2:
				$address_type = JText::_('COM_TIENDA_SHIPPING');
				break;
		} 		
	
        $list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_( 'Select '.$address_type.' Address' )." -", 'address_id', 'address_name' );
		}
		
		JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
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
        
		if($addNew) {
			$list[] =  self::option('0',JText::_('COM_TIENDA_NEW_ADDRESS'), 'address_id', 'address_name' );
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
    public static function order($user_id, $selected = '', $name = 'filter_order', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = true, $title = 'Select Order' )
    {
        if (empty($user_id))
        {
            return JText::_('COM_TIENDA_INVALID_USER');
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
            $title .= " - " . JHTML::_('date', $item->created_date, Tienda::getInstance()->get('date_format'));
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
    public static function addressaction($selected, $name = 'filter_action', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $allowNone = false, $title = 'COM_TIENDA_SELECT_ACTION' )
    {
        $list = array();
        if($allowAny) 
        {
            $list[] =  self::option('', "- ".JText::_( $title )." -", 'value', 'text' );
        }

        $list[] = JHTML::_('select.option',  'flag_shipping', JText::_('COM_TIENDA_USE_AS_DEFAULT_FOR_SHIPPING') );
        $list[] = JHTML::_('select.option',  'flag_billing', JText::_('COM_TIENDA_USE_AS_DEFAULT_FOR_BILLING') );
        $list[] = JHTML::_('select.option',  'flag_deleted', JText::_('COM_TIENDA_DELETE') );
        
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
	public static function orderstate($selected, $name = 'filter_orderstateid', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false)
 	{
        $list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_('COM_TIENDA_SELECT_STATE')." -", 'order_state_id', 'order_state_name' );
		}

		JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
		JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
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
    public static function shippingtype( $selected, $name = 'filter_shipping_method_type', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title = 'Select Shipping Type' )
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
	public static function shippingmethod( $selected, $name = 'filter_shipping_method', $attribs = array('class' => 'inputbox'), $idtag = null )
	{
	    $list = array();

		JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
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
    public static function productattributeoptions( $productattribute_id, $selected, $name = 'filter_pao', $attribs = array('class' => 'inputbox'), $idtag = null, $opt_selected = array())
    {
        $list = array();
        JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
        $model = JModel::getInstance( 'ProductAttributeOptions', 'TiendaModel' );
        $model->setState( 'filter_attribute', $productattribute_id );
        $model->setState('order', 'tbl.ordering');
                
        // Parent options
        if(count($opt_selected))
        {
        	$model->setState('filter_parent', $opt_selected);
        }
        
        $items = $model->getList();
        $geozones = array();
        $shipping = false;
        if( count( $items ) )
        {
        	$shipping = $items[0]->product_ships;
        	if( $shipping )
        	{
		        Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
        		Tienda::load( 'TiendaHelperUser', 'helpers.user' );
						$geozones = TiendaHelperUser::getGeoZones( JFactory::getUser( )->id );
						if ( empty( $geozones ) )
						{
							// use the default
							$table = JTable::getInstance( 'Geozones', 'TiendaTable' );
							$table->load( array(
										'geozone_id' => Tienda::getInstance( )->get( 'default_tax_geozone' )
									) );
							$geozones = array(
								$table
							);
						}
        	}
        }
        foreach (@$items as $item)
        {
        	if( $shipping )
        	{
	        	$tax = TiendaHelperProduct::getTaxTotal( $item->product_id, $geozones, $item->productattributeoption_price );
	        	$item->productattributeoption_price += $tax->tax_total;
        	}
        	// Do not display the prefix if it is "=" (it's not good to see =�13, better �13)
        	if($item->productattributeoption_prefix != '=')
        	{
        		$display_suffix = ($item->productattributeoption_price > '0') ? ": ".$item->productattributeoption_prefix.TiendaHelperBase::currency($item->productattributeoption_price) : '';
        	}
        	else
        	{
        		$display_suffix = ($item->productattributeoption_price > '0') ? ": ".TiendaHelperBase::currency($item->productattributeoption_price) : '';
        	}
        	$display_name = JText::_($item->productattributeoption_name).$display_suffix;
        	if( $item->is_blank )
            $list[] =  self::option( 0, $display_name );
        	else
            $list[] =  self::option( $item->productattributeoption_id, $display_name );
        }
        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag  );
    }	
    
    /**
	 * Generates a selectlist of Product Attributes 
	 */
    public static function productattributes( $selected, $product_id, $id, $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title = 'COM_TIENDA_NO_PARENT')
    {
        $list = array();
        
    	if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }
        
        $name = "attribute_parent[".$id."]";
        
        $opt_name = "parent";
        
        JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
        $model = JModel::getInstance( 'ProductAttributes', 'TiendaModel' );
        $model->setState('order', 'tbl.ordering');
        if($product_id)
        {
        	$model->setState('filter_product', $product_id);
        }
        $items = $model->getList();
        foreach (@$items as $item)
        {
            $list[] =  self::option( $item->productattribute_id, $item->productattribute_name );
        }
        
        $attribs["onchange"] = "tiendaPopulateAttributeOptions( this, 'parent_option_select_".$id."', '".$opt_name."', '".$id."');";
        
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
    public static function productattributeoptionprefix( $selected, $name = 'filter_prefix', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title = 'Select Prefix' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  '+', "+" );
        $list[] = JHTML::_('select.option',  '-', "-" );
        $list[] = JHTML::_('select.option',  '=', "=" );

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
    public static function periodUnit( $selected, $name = 'filter_periodunit', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title='Select Period Unit' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  'D', JText::_('COM_TIENDA_DAY') );
        $list[] = JHTML::_('select.option',  'W', JText::_('COM_TIENDA_WEEK') );
        $list[] = JHTML::_('select.option',  'M', JText::_('COM_TIENDA_MONTH') );
        $list[] = JHTML::_('select.option',  'Y', JText::_('COM_TIENDA_YEAR') );
        $list[] = JHTML::_('select.option',  'I', JText::_('COM_TIENDA_UNIT_ISSUE') );
        
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
    public static function relationship( $selected, $name = 'filter_relationtype', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title='Select Relationship' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  'relates', JText::_('COM_TIENDA_RELATIONSHIP_RELATES') );
        $list[] = JHTML::_('select.option',  'requires', JText::_('COM_TIENDA_RELATIONSHIP_REQUIRES') );
        $list[] = JHTML::_('select.option',  'required_by', JText::_('COM_TIENDA_RELATIONSHIP_REQUIRED_BY') );
        $list[] = JHTML::_('select.option',  'requires_past', JText::_('COM_TIENDA_RELATIONSHIP_REQUIRES_PAST') );
        $list[] = JHTML::_('select.option',  'requires_current', JText::_('COM_TIENDA_RELATIONSHIP_REQUIRES_CURRENT') );
        $list[] = JHTML::_('select.option',  'child', JText::_('COM_TIENDA_RELATIONSHIP_CHILD') );
        $list[] = JHTML::_('select.option',  'parent', JText::_('COM_TIENDA_RELATIONSHIP_PARENT') );
                
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
    public static function productlayout( $selected, $name = 'filter_productlayout', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = true, $title='COM_TIENDA_SELECT_LAYOUT' )
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
    public static function categorylayout( $selected, $name = 'filter_categorylayout', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = true, $title='COM_TIENDA_SELECT_LAYOUT' )
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
    public static function addtocartaction( $selected, $name = 'filter_addtocartaction', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title = 'COM_TIENDA_SELECT_ACTION' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  '0', JText::_('COM_TIENDA_REDIRECT_TO_PRODUCT_PAGE') );
        // $list[] = JHTML::_('select.option',  'lightbox', JText::_('COM_TIENDA_DISPLAY_MINICART_IN_LIGHTBOX') );
        $list[] = JHTML::_('select.option',  'redirect', JText::_('COM_TIENDA_REDIRECT_TO_CART') );
        $list[] = JHTML::_('select.option',  'samepage', JText::_('COM_TIENDA_RETURN_ON_THE_SAME_PAGE') );

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
    public static function displaywithtax( $selected, $name = 'filter_displaywithtax', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title = 'Display Prices With Tax' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  '0', JText::_('COM_TIENDA_DO_NOT_DISPLAY_TAX') );
        $list[] = JHTML::_('select.option',  '1', JText::_('COM_TIENDA_DISPLAY_TAX_NEXT_TO_PRICE') );
        $list[] = JHTML::_('select.option',  '2', JText::_('COM_TIENDA_SUM_THE_TAX_AND_PRODUCT_PRICE') );
        $list[] = JHTML::_('select.option',  '3', JText::_('COM_TIENDA_SUM_THE_TAX_AND_PRODUCT_PRICE_INCLUDING_TEXT') );
        $list[] = JHTML::_('select.option',  '4', JText::_('COM_TIENDA_COM_TIENDA_DISPLAY_BOTH_PRICE_WITHOUT_TAX_AND_PRICE_INCLUDING_TAX') );
                
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
    public static function cartbutton( $selected, $name = 'filter_cartbutton', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title = 'Select Cart Button' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  'image', JText::_('COM_TIENDA_IMAGE') );
        $list[] = JHTML::_('select.option',  'button', JText::_('COM_TIENDA_BUTTON') );

        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }
    
    /**
     * Getting list of products
     *
     */
     
	public static function product($selected, $name = 'product_id', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $allowNone = false, $title = 'Select product', $title_none = 'COM_TIENDA_NO_PARENT', $enabled = null )
 	{
		// Build list
        $list = array();
		if ($allowAny) {
			$list[] =  self::option('', "- ".JText::_( $title )." -", 'product_id', 'product_name' );
		}
		JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
		JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
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
 	public static function users($selected, $name = 'userid', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $allowNone = false, $title = 'Select User', $title_none = 'COM_TIENDA_NO_PARENT', $enabled = null )
 	{
		// Build list
        $list = array();
		if ($allowAny) {
			$list[] =  self::option('', "- ".JText::_( $title )." -", 'id', 'name' );
		}
		JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
		JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
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
 	public static function groups($selected, $name = 'group_id', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $allowNone = false, $title = 'COM_TIENDA_SELECT_GROUP', $title_none = 'No Group', $enabled = null )
 	{
		// Build list
        $list = array();
		if ($allowAny) {
			$list[] =  self::option('', "- ".JText::_( $title )." -", 'group_id', 'group_name' );
		}
		JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
		JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
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
    public static function coupongroup( $selected, $name = 'filter_coupongroup', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title = 'Select Coupon Group' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  'price', JText::_('COM_TIENDA_PRICE') );
        $list[] = JHTML::_('select.option',  'shipping', JText::_('COM_TIENDA_SHIPPING') );

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
    public static function coupontype( $selected, $name = 'filter_coupontype', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title = 'Coupon Type' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  '0', JText::_('COM_TIENDA_PER_ORDER') );
        $list[] = JHTML::_('select.option',  '1', JText::_('COM_TIENDA_PER_PRODUCT') );

        return self::radiolist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }

    /**
    * Generates a created/modified select list
    *
    * @param string The value of the HTML name attribute
    * @param string Additional HTML attributes for the <select> tag
    * @param mixed The key that is selected
    * @returns string HTML for the radio list
    */
    public static function subdatetype( $selected, $name = 'filter_datetype', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title='COM_TIENDA_SELECT_TYPE' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  'created', JText::_('COM_TIENDA_CREATED') );
        $list[] = JHTML::_('select.option',  'expires', JText::_('COM_TIENDA_EXPIRES') );
        
        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }
    
	/**
    * Generates an entity type list
    *
    * @param string The value of the HTML name attribute
    * @param string Additional HTML attributes for the <select> tag
    * @param mixed The key that is selected
    * @returns string HTML for the radio list
    */
    public static function entitytype( $selected, $name = 'filter_entitytype', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title='COM_TIENDA_SELECT_TYPE' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  'products', JText::_('COM_TIENDA_PRODUCTS') );
        //$list[] = JHTML::_('select.option',  'addresses', JText::_('COM_TIENDA_ADDRESSES') );
        
        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }
    
	/**
    * Generates a data type list
    *
    * @param string The value of the HTML name attribute
    * @param string Additional HTML attributes for the <select> tag
    * @param mixed The key that is selected
    * @returns string HTML for the radio list
    */
    public static function attributetype( $selected, $name = 'filter_attributetype', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title='COM_TIENDA_SELECT_TYPE' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  'int', JText::_('COM_TIENDA_INT') );
        $list[] = JHTML::_('select.option',  'varchar', JText::_('COM_TIENDA_STRING') );
        $list[] = JHTML::_('select.option',  'hidden', JText::_('COM_TIENDA_HIDDEN') );
        $list[] = JHTML::_('select.option',  'text', JText::_('COM_TIENDA_TEXTAREA') );
        $list[] = JHTML::_('select.option',  'decimal', JText::_('COM_TIENDA_DECIMAL') );
        $list[] = JHTML::_('select.option',  'datetime', JText::_('COM_TIENDA_DATE_TIME') );
        //$list[] = JHTML::_('select.option',  'time', JText::_('COM_TIENDA_TIME') ); // NO NEW FEATURES YET
        $list[] = JHTML::_('select.option',  'bool', JText::_('COM_TIENDA_BOOLEAN') );
        
        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }
    
	/**
    * Generates a data editableby list
    *
    * @param string The value of the HTML name attribute
    * @param string Additional HTML attributes for the <select> tag
    * @param mixed The key that is selected
    * @returns string HTML for the radio list
    */
    public static function editableby( $selected, $name = 'filter_editable', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title='Select Editable' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  0, JText::_('COM_TIENDA_NONE') );
        $list[] = JHTML::_('select.option',  1, JText::_('COM_TIENDA_ADMIN') );
        $list[] = JHTML::_('select.option',  2, JText::_('COM_TIENDA_USER') );
        
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
    public static function view( $selected, $name = 'filter_view', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title = 'Select View' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  'orders', JText::_('COM_TIENDA_ORDERS') );
        $list[] = JHTML::_('select.option',  'orderitems', JText::_('COM_TIENDA_ORDERED_ITEMS') );
        $list[] = JHTML::_('select.option',  'products', JText::_('COM_TIENDA_PRODUCTS') );
        $list[] = JHTML::_('select.option',  'orderpayments', JText::_('COM_TIENDA_PAYMENTS') );
        $list[] = JHTML::_('select.option',  'subscriptions', JText::_('COM_TIENDA_SUBSCRIPTIONS') );
        $list[] = JHTML::_('select.option',  'coupons', JText::_('COM_TIENDA_COUPONS') );
        $list[] = JHTML::_('select.option',  'users', JText::_('COM_TIENDA_USERS') );
        $list[] = JHTML::_('select.option',  'categories', JText::_('COM_TIENDA_CATEGORIES') );

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
    public static function downloadableproduct($user_id, $selected, $name = 'filter_product_id', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = true, $title = 'Select Product')
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
        $model = JModel::getInstance( 'ProductDownloads', 'TiendaModel' );
        $model->setState('filter_user', $user_id);
        $query = $model->getQuery();
        $query->group( 'tbl.product_id' );
        $model->setQuery( $query );
        $items = $model->getList();
        foreach (@$items as $item)
        {
            $list[] =  self::option( $item->product_id, JText::_( $item->product_name ) );
        }

        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }
    
/**
    * Generates a data productsortby list
    *
    * @param string The value of the HTML name attribute
    * @param string Additional HTML attributes for the <select> tag
    * @param mixed The key that is selected
    * @returns string HTML for the radio list
    */
    public static function productsortby( $selected, $name = 'filter_sortby', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title='Sort By' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', JText::_('COM_TIENDA_ORDERING') );
        }

      	$products = JTable::getInstance( 'Products', 'TiendaTable' ); 
       	$columns = $products->getProperties();
   		$columns['price']='';
   		$columns['product_quantity'] = '';
   		
       	$sortings = Tienda::getInstance()->get('display_sortings', 'Name|product_name,Price|price,Rating|product_rating');
        $sortingsA = explode(',', $sortings);
       
        foreach($sortingsA as $sorting)
        {
        	$sortA = explode('|', $sorting);  
        	if(array_key_exists($sortA[1], $columns))
        		$list[] = JHTML::_('select.option',  $sortA[1], JText::_( $sortA[0] ) );
        }
                
        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }
    
	/**
    * Generates a None / Shipping / Billing / Both list
    *
    * @param string The value of the HTML name attribute
    * @param string Additional HTML attributes for the <select> tag
    * @param mixed The key that is selected
    * @returns string HTML for the radio list
    */
    public static function addressShowList( $selected, $name = 'show_field', $attribs = array('class' => 'inputbox'), $idtag = null )
    {
        $list = array();
        $list[] = JHTML::_('select.option',  '0', JText::_('COM_TIENDA_NONE') );
        $list[] = JHTML::_('select.option',  '1', JText::_('COM_TIENDA_BILLING') );
        $list[] = JHTML::_('select.option',  '2', JText::_('COM_TIENDA_SHIPPING') );
        $list[] = JHTML::_('select.option',  '3', JText::_('COM_TIENDA_BOTH') );
        
        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }
    
     /**
	 * Generates a  select list for paov operators
	 * 
	 * @param unknown_type $selected
	 * @param unknown_type $name
	 * @param unknown_type $attribs
	 * @param unknown_type $idtag
	 * @param unknown_type $allowAny
	 * @param unknown_type $title
	 * @return unknown_type
	 */
    public static function productattributeoptionvalueoperator( $selected, $name = 'filter_operator', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title = 'Select Operator' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  'replace', JText::_('COM_TIENDA_ATTRIBOPTIONVALOP_REPLACE') );
        $list[] = JHTML::_('select.option',  'append', JText::_('COM_TIENDA_ATTRIBOPTIONVALOP_APPEND') );
        $list[] = JHTML::_('select.option',  'prepend', JText::_('COM_TIENDA_ATTRIBOPTIONVALOP_PREPEND') );
		$list[] = JHTML::_('select.option',  '+', JText::_('COM_TIENDA_ATTRIBOPTIONVALOP_PLUS') );
		$list[] = JHTML::_('select.option',  '-', JText::_('COM_TIENDA_ATTRIBOPTIONVALOP_MINUS') );
		$list[] = JHTML::_('select.option',  '=', JText::_('COM_TIENDA_ATTRIBOPTIONVALOP_EQUALS') );

        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }
	
	 /**
	 * Generates a  select list for paov operators
	 * 
	 * @param unknown_type $selected
	 * @param unknown_type $name
	 * @param unknown_type $attribs
	 * @param unknown_type $idtag
	 * @param unknown_type $allowAny
	 * @param unknown_type $title
	 * @return unknown_type
	 */
    public static function productattributeoptionvaluefield( $selected, $name = 'filter_field', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title = 'Select Field' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  'product_full_image', JText::_('COM_TIENDA_MAIN_IMAGE') );
        $list[] = JHTML::_('select.option',  'product_model', JText::_('COM_TIENDA_MODEL') );
        $list[] = JHTML::_('select.option',  'product_sku', JText::_('COM_TIENDA_SKU') );
		
        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }

 	/**
	 * Generates a  select list for opc layouts
	 * 
	 * @param unknown_type $selected
	 * @param unknown_type $name
	 * @param unknown_type $attribs
	 * @param unknown_type $idtag
	 * @param unknown_type $allowAny
	 * @param unknown_type $title
	 * @return unknown_type
	 */
    public static function opclayouts( $selected, $name = 'opclayouts', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title = 'COM_TIENDA_SELECT_LAYOUT' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  'standard', JText::_('COM_TIENDA_STANDARD') );
        $list[] = JHTML::_('select.option',  'onepage-1col', JText::_('COM_TIENDA_1_COLUMN') );
        $list[] = JHTML::_('select.option',  'onepage-2cols', JText::_('COM_TIENDA_2_COLUMNS') );
        $list[] = JHTML::_('select.option',  'onepage-opc', JText::_('COM_TIENDA_3_COLUMNS') );
		
        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }

 	/**
	 * Generates a select list for scripts handling upload multiple files at once
	 * 
	 * @param unknown_type $selected
	 * @param unknown_type $name
	 * @param unknown_type $attribs
	 * @param unknown_type $idtag
	 * @param unknown_type $allowAny
	 * @param unknown_type $title
	 * @return unknown_type
	 */
    public static function multipleuploadscript( $selected, $name = 'opclayouts', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title = 'COM_TIENDA_SELECT_LAYOUT' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  '0', JText::_('COM_TIENDA_DETECT_AUTOMATICALLY') );
        $list[] = JHTML::_('select.option',  'multiupload', JText::_('COM_TIENDA_MULTIUPLOAD') );
        $list[] = JHTML::_('select.option',  'uploadify', JText::_('COM_TIENDA_UPLOADIFY') );
		
        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }
	
	 /**
    * Generates a list of credit types
    *
    * @param string The value of the HTML name attribute
    * @param string Additional HTML attributes for the <select> tag
    * @param mixed The key that is selected
    * @returns string HTML for the radio list
    */
    public static function credittype( $selected, $name = 'credit_type', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title='COM_TIENDA_SELECT_TYPE' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
        JModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
        $model = JModel::getInstance( 'CreditTypes', 'TiendaModel' );
        $items = $model->getList();
        foreach (@$items as $item)
        {
            $list[] =  self::option( $item->credittype_code, JText::_($item->credittype_name) );
        }
        
        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }
    
    /**
    * Generates a list of credit status
    *
    * @param string The value of the HTML name attribute
    * @param string Additional HTML attributes for the <select> tag
    * @param mixed The key that is selected
    * @returns string HTML for the radio list
    */
    public static function creditstatus( $selected, $name = 'credit_status', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title='Select Status' )
    {
        $list = array();
        if($allowAny) {
            $list[] =  self::option('', "- ".JText::_( $title )." -" );
        }

        $list[] = JHTML::_('select.option',  'active', JText::_('COM_TIENDA_ACTIVE') );
        $list[] = JHTML::_('select.option',  'disabled', JText::_('COM_TIENDA_DISABLED') );
        $list[] = JHTML::_('select.option',  'used', JText::_('COM_TIENDA_USED') );
        $list[] = JHTML::_('select.option',  'withdrawable', JText::_('COM_TIENDA_WITHDRAWABLE') );
        
        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }
	/**
     * 
     * @param unknown_type $selected
     * @param unknown_type $name
     * @return unknown_type
     */
    public static function userelement( $selected, $name = 'customer', $onChange = '' )
    {
        $return = array();
        $model = JModel::getInstance( 'ElementUser', 'TiendaModel' );
        $return['select'] = $model->fetchElement( $name, $selected, '','', $onChange );
        $return['clear'] = $model->clearElement( $name, '0', '', '', $onChange );
        return $return;
    }

    /*
     * Generates ordered list of tax rates for a specified geozone and tax class
     * 
     * @param $selected 				ID of selected option
     * @param $name 						Name of the element
     * @param $tax_class_id			ID of the tax class (null means any class)
     * @param $geozone_id				ID of a geozone
     * @param $tax_type					Type of the class (for future use)
     * @param $attribs					Element attributes
     * @param $idtag						ID of the element (null means that it will be equal to name)
     */
		public static function taxratespredecessors( $selected, $name = 'level', $tax_class_id = null, $geozone_id = null, $tax_type = null, $attribs = array('class' => 'inputbox' ), $idtag = null )
		{
        $list = array();
        $list[] =  self::option( 0, JText::_('COM_TIENDA_ROOT') );

        $db = JFactory::getDbo();
        Tienda::load( 'TiendaQuery', 'library.query' );
        $q = new TiendaQuery();
        
        $q->select( array( 'level', 'tax_rate_description' ) );
        $q->from( '#__tienda_taxrates' );
        if( $tax_class_id )
	        $q->where( 'tax_class_id = '.(int)$tax_class_id );
        if( $geozone_id )
	        $q->where( 'geozone_id = '.(int)$tax_class_id );
	       
	      $q->order( 'level ASC, tax_rate_description' );
	      $db->setQuery( $q );
        $items = $db->loadObjectList();
        $spaces = '';
        $last_level = -1;
        for( $i = 0, $c = count($items);$i < $c; $i++)
        {
        	$item = $items[$i];
        	if( $item->level != $last_level )
        		$spaces .= '-';
           $list[] =  self::option( $item->level+1, $spaces.' '.$item->tax_rate_description );
          $last_level = $item->level;
        }
        
        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
		}

    /*
     * Generates list of ways to display taxes in order summary
     * 
     * @param $selected ID of selected option
     * @param $name 		Name of the element
     * @param $attribs	Element attributes
     * @param $idtag		ID of the element (null means that it will be equal to name)
     */
		public static function taxdisplaycheckout( $selected, $name = 'show_tax_checkout', $attribs = array('class' => 'inputbox' ), $idtag = null )
		{
        $list = array();
        $list[] = JHTML::_('select.option',  '1', JText::_('COM_TIENDA_TAX_RATES_IN_SEPARATE_LINES') );
        $list[] = JHTML::_('select.option',  '2', JText::_('COM_TIENDA_TAX_CLASSES_IN_SEPARATE_LINES') );
        $list[] = JHTML::_('select.option',  '3', JText::_('COM_TIENDA_TAX_CLASSES_AND_TAX_RATES_IN_SEPARATE_LINES') );
        $list[] = JHTML::_('select.option',  '4', JText::_('COM_TIENDA_ALL_IN_ONE_LINE') );
		
        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
		}

	 /**
    * Generates limit list
    *
    * @param string The value of the HTML name attribute
    * @param string Additional HTML attributes for the <select> tag
    * @param mixed The key that is selected
    * @returns string HTML for the radio list
    */
    public static function limit( $selected, $name = 'limit', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title = 'limit' )
    {
      

        $list[] = JHTML::_('select.option',  '5', '5');
        $list[] = JHTML::_('select.option',  '10', '10');
        $list[] = JHTML::_('select.option',  '15', '15');
        $list[] = JHTML::_('select.option',  '20', '20');
        $list[] = JHTML::_('select.option',  '25', '25');
        $list[] = JHTML::_('select.option',  '30', '30');
        $list[] = JHTML::_('select.option',  '35', '35');
        $list[] = JHTML::_('select.option',  '50', '50');
        $list[] = JHTML::_('select.option',  '100', '100');
        $list[] = JHTML::_('select.option',  '0', 'ALL');

        return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
    }	
}
