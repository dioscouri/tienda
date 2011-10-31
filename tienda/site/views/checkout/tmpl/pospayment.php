<?php defined('_JEXEC') or die('Restricted access');?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');?>

<div style="padding: 20px;">	

	<div id="payment_info" class="address">
		<h3><?php echo JText::_("COM_TIENDA_BILLING_INFORMATION");?></h3>
		<strong><?php echo JText::_("COM_TIENDA_BILLING_ADDRESS_ERROR");?></strong>:
		<br/>
		<?php
		echo $this->values['orderinfo']->billing_first_name . " " . $this->values['orderinfo']->billing_last_name . "<br/>";
		echo $this->values['orderinfo']->billing_address_1 . ", ";
		echo $this->values['orderinfo']->billing_address_2 ? $this->values['orderinfo']->billing_address_2 . ", " : "";
		echo $this->values['orderinfo']->billing_city . ", ";
		echo $this->values['orderinfo']->billing_zone_name . " ";
		echo $this->values['orderinfo']->billing_postal_code . " ";
		echo $this->values['orderinfo']->billing_country_name;
		?>
	</div>
	<div class="reset"></div>
	<?php if($this->values['shippingrequired']):?>
	<div id="shipping_info" class="address">
		<h3><?php echo JText::_("COM_TIENDA_SHIPPING_INFORMATION");?></h3>
		<strong><?php echo JText::_("COM_TIENDA_SHIPPING_METHOD");?></strong>: 
		<?php echo JText::_($this->values['shipping_name']);?>
		<br/>
		<strong><?php echo JText::_("COM_TIENDA_SHIPPING_ADDRESS");?></strong>:
		<br/>
		<?php
		echo $this->values['orderinfo']->shipping_first_name . " " . $this->values['orderinfo']->shipping_last_name . "<br/>";
		echo $this->values['orderinfo']->shipping_address_1 . ", ";
		echo $this->values['orderinfo']->shipping_address_2 ? $this->values['orderinfo']->shipping_address_2 . ", " : "";
		echo $this->values['orderinfo']->shipping_city . ", ";
		echo $this->values['orderinfo']->shipping_zone_name . " ";
		echo $this->values['orderinfo']->shipping_postal_code . " ";
		echo $this->values['orderinfo']->shipping_country_name;
		?>
	</div>
	<?php if(!empty($this->order->customer_note)):?>
	<div id="shipping_comments">
		<h3><?php echo JText::_("COM_TIENDA_SHIPPING_NOTES");?></h3>
		<?php echo $this->order->customer_note;?>
	</div>	
	<?php endif;?>
	
	<?php endif;?>	
	<div class="reset"></div>	
	<h3>
	<?php echo JText::_("COM_TIENDA_PAYMENT_METHOD");?>
	</h3>
	<?php echo $this->plugin_html;?>

</div>