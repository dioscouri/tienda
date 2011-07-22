<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; ?>

<p>
<?php
	echo $row->title . " ". $row->first_name . " ". $row->last_name . "<br>";
	if( strlen( $row->company ) )
		echo $row->company . "<br>";
	echo $row->address_1 . " " . $row->address_2 . "<br>";
	echo $row->city . ", " . $row->zone_name ." " . $row->postal_code . "<br>";
	echo $row->country_name . "<br>";
	if( strlen( $row->tax_number ) )
		echo $row->tax_number . "<br>";
?>
</p>
