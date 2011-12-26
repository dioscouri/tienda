<?php
defined('_JEXEC') or die('Restricted access');
$display_tax_checkout = TiendaConfig::getInstance()->get('show_tax_checkout', '1');
Tienda::load( 'TiendaHelperBase', 'helpers._base' );
$row = &$this->row;

switch( $display_tax_checkout )
{
	case 1 : // Tax Rates in Separate Lines
		foreach ( $row->ordertaxrates as $taxrate)
		{
			$tax_desc = $taxrate->ordertaxrate_description ? $taxrate->ordertaxrate_description : 'Tax';
			$amount = $taxrate->ordertaxrate_amount;
			if ( $amount )
			{
	    	?>
		<tr>
			<th colspan="2" style="text-align: right;"><?php echo JText::_( $tax_desc ).":"; ?></th>
    	<th style="text-align: right;"><?php echo TiendaHelperBase::currency( $amount, $row->currency); ?></th>
		</tr>
  			<?php
	  	}
		}
	break;
 	case 2 : // Tax Classes in Separate Lines
	foreach ( $row->ordertaxclasses as $taxclass)
	{
		$tax_desc = $taxclass->ordertaxclass_description ? $taxclass->ordertaxclass_description : 'Tax';
		$amount = $taxclass->ordertaxclass_amount;
		if ( $amount )
	 	{
		?>
		<tr>
   		<th colspan="2" style="text-align: right;"><?php echo JText::_( $tax_desc ).":"; ?></th>
			<th style="text-align: right;"><?php echo TiendaHelperBase::currency( $amount , $row->currency); ?></th>
		</tr>
  	<?php
	  }
	}
	break;
 	case 3 : // Tax Classes and Tax Rates in Separate Lines
		foreach ( $row->ordertaxclasses as $taxclass)
		{
	 		$tax_desc = $taxclass->ordertaxclass_description ? $taxclass->ordertaxclass_description : 'Tax';
			$amount = $taxclass->ordertaxclass_amount;
	 		if ( $amount )
	  	{
	    	?>
		<tr>
			<th colspan="2" style="text-align: right;"><?php echo JText::_( $tax_desc ).":"; ?></th>
			<th style="text-align: right;"><?php echo TiendaHelperBase::currency( $amount , $row->currency); ?></th>
		</tr>
  		<?php
   		}
    	foreach( $row->ordertaxrates as $taxrate )
   		{
				$tax_desc = $taxrate->ordertaxrate_description ? $taxrate->ordertaxrate_description : 'Tax';
		 		$amount = $taxrate->ordertaxrate_amount;
		  	if ( $amount && $taxrate->ordertaxclass_id == $taxclass->tax_class_id )
		  	{
		  		?>
		<tr>
   		<th colspan="2" style="text-align: right;"><?php echo JText::_( $tax_desc )." &nbsp;&nbsp; :"; ?></th>
   		<th style="text-align: right;"><?php echo TiendaHelperBase::currency( $amount, $row->currency); ?></th>
		</tr>
  	<?php
	  		}
	 		}
		}
	break;
	case 4 : // All in One Line
  	if( $row->order_tax )
    {
    ?>
    <tr>
	    <th colspan="2" style="text-align: right;">
     	<?php
     	if (!empty($this->show_tax)) { echo JText::_("COM_TIENDA_PRODUCT_TAX_INCLUDED").":"; }
       	else { echo JText::_("COM_TIENDA_PRODUCT_TAX").":"; }    
	   	?>
			</th>
   		<th style="text-align: right;"><?php echo TiendaHelperBase::currency($row->order_tax) ?></th>
   	</tr>
		<?php
  	}
	break;
}
