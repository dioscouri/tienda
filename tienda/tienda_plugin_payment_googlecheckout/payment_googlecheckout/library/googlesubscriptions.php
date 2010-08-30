<?php
/**
 * Classes that handle the Google Checkout subscriptions
 * {@link http://code.google.com/intl/ru/apis/checkout/developer/Google_Checkout_Beta_Subscriptions.html}
 * 
 * @version	1.5
 * @package	Ambrasubs
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// ensure this file is being included by a parent file
defined('_JEXEC') or die('Restricted access');

/**
 * Represents the Google Checkout Subscription
 */
class GoogleSubscription
{
	/**
	 * @var object
	 * @access public
	 */
	var $paymentsData;
		
	/**
	 * @var object
	 * @access public
	 */
	var $recurrentItemData;
	
	/**
	 * Subscription attributes
	 * 
	 * @var string
	 * @access public
	 */
	var $type;
	var $period;
	var $start_date;
	var $no_charge_after;
	
	
	/**
	 * Class constructor
	 * 
	 * @param string $type google or merchant
	 * @param string $period DAILY, WEEKLY, SEMI_MONTHLY, MONTHLY, EVERY_TWO_MONTHS, QUARTERLY, YEARLY
	 * @param string $start_date ISO 8601 formatted date (optional)
	 * @param string $now_charge_after ISO 8601 formatted date (optional)
	 * @return void
	 * @access public
	 */
	function GoogleSubscription($type, $period, $start_date = null, $no_charge_after = null)
	{
		$this->type = $type;
		$this->period = $period;
		$this->start_date = $start_date;
		$this->no_charge_after = $no_charge_after;
	}
	
	
	/**
	 * Sets the <payments> tag data
	 * 
	 * @param object $data
	 * @return void
	 * @access public
	 */
	function SetPaymentsData($data)
	{
		$this->paymentsData = $data;	
	}
	
	
	/**
	 * Sets the <recurrent-item> data
	 * 
	 * @param object $data
	 * @return void
 	 * @access public
	 */
	function SetRecurrentItemData($data)
	{
		$this->recurrentItemData = $data;
	}
	
	
	/**
	 * Creates the subscription XML and adds it to the result document
	 * 
	 * @param object $xml_data
	 * @return void
	 * @access public
	 */
	function AddToXML(& $xml_data)
	{
		$attributes = array(
			'type' => $this->type,
			'period' => strtoupper($this->period)
		);
		if ($this->start_date) {
			$attributes['start-date'] = $this->start_date;
		}
		if ($this->no_charge_after) {
			$attributes['no-charge-after'] = $this->no_charge_after;
		}		
		
		$xml_data->Push('subscription', $attributes);
		
			if ($this->paymentsData) {
				$this->paymentsData->addToXML($xml_data);
			}
		
			if ($this->recurrentItemData) {
				$this->recurrentItemData->addToXML($xml_data);;
			}		
		
		$xml_data->Pop('subscription');		
    }
    
}


/**
 * Represents the <payments> tag data
 */
class GoogleSubscriptionPaymets
{
	/**
	 * @var mixed
	 * @access public
	 */
	var $price;
	var $currency;
	var $times;
	
	
	/**
	 * Class constructor
	 * 
	 * @param float $price
	 * @param string $currency
	 * @param int $times (optional)
	 * @return void
	 * @access public
	 */
	function GoogleSubscriptionPaymets($price, $currency, $times = null)
	{
		$this->price = $price;
		$this->currency = $currency;
		$this->times = $times;	
	}
	
	
	/**
	 * Creates the payments XML and adds it to the result document
	 * 
	 * @param object $xml_data
	 * @return void
	 * @access public
	 */
	function AddToXML(& $xml_data)
	{
		$xml_data->Push('payments');
		
			$sp_attributes = array();
			if ($this->times) {
				$sp_attributes['times'] = (int)$this->times;
			}
		
			$xml_data->Push('subscription-payment', $sp_attributes);				
				$xml_data->Element('maximum-charge', $this->price, array('currency' => $this->currency));		
			$xml_data->Pop('subscription-payment');
		
		$xml_data->Pop('payments');
	}
	
}


/**
 * Represents the <recurrent-item> tag data
 */
class GoogleSubscriptionsRecurrentItem extends GoogleItem
{
	/**
	 * Creates the item XML and adds it to the result document
	 * 
	 * @param object $xml_data
	 * @return void
	 * @access public
	 */
	function AddToXML(& $xml_data)
	{
		$xml_data->Push('recurrent-item');		
		
		/*
		 * borrowed from GoogleItem::GetXML()
		 */
		$xml_data->Element('item-name', $this->item_name);
		$xml_data->Element('item-description', $this->item_description);
		$xml_data->Element('unit-price', $this->unit_price, array('currency' => $this->currency));
		$xml_data->Element('quantity', $this->quantity);
		if($this->merchant_private_item_data != '') {
			if(is_a($this->merchant_private_item_data, 'merchantprivate')) {
				$this->merchant_private_item_data->AddMerchantPrivateToXML($xml_data);
			}
			else {
				$xml_data->Element('merchant-private-item-data', $this->merchant_private_item_data);
			}
		}
		if($this->merchant_item_id != '')
			$xml_data->Element('merchant-item-id', $this->merchant_item_id);
		if($this->tax_table_selector != '')
			$xml_data->Element('tax-table-selector', $this->tax_table_selector);
		// Carrier calculation
		if($this->item_weight != '' && $this->numeric_weight !== '') {
			$xml_data->EmptyElement('item-weight', array( 'unit' => $this->item_weight, 'value' => $this->numeric_weight));
        }
		// New Digital Delivery Tags
		if($this->digital_content) {
			$xml_data->push('digital-content');
			if(!empty($this->digital_url)) {
				$xml_data->element('description', substr($this->digital_description, 0, MAX_DIGITAL_DESC));
				$xml_data->element('url', $this->digital_url);
				// To avoid NULL key message in GC confirmation Page
				if(!empty($this->digital_key)) {
					$xml_data->element('key', $this->digital_key);
				}
			}
			else {
				$xml_data->element('email-delivery', $this->_GetBooleanValue($this->email_delivery, "true"));
			}
			$xml_data->pop('digital-content');          
		}
		
		$xml_data->Pop('recurrent-item');
	}
}