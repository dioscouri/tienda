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

// Add CSS
$document->addStyleSheet( JURI::root(true).'/modules/mod_tienda_my_order/tmpl/tienda_my_order.css');

if ($num > 0 && @$orders)
{ ?>
	<table style="clear: both;" class="adminlist">
        <thead>
              <tr>
              <?php if ($params->get('display_id', '1') == '1')
				{?>
                <th style="width: 50px;">
                   ID </th>
                <?php  }?>
                <?php if ($params->get('display_date', '1') == '1')
				{?>
                <th style="width: 200px;">
                  Date           
                 </th>
                  <?php  }?>
                  <?php if ($params->get('display_price', '1') == '1')
				{?>
                <th style="width: 100px;">
                   Total</th>
                <?php  }?>
                 <?php if ($params->get('display_state', '1') == '1')
				{?>
                <th style="width: 100px;">
                    State                </th>
                 <?php  }?>
            </tr>
        </thead> <tbody>
  <?php  // Loop through the products to display
  
	 $count=0;
	  foreach (@$orders as $order) : ?>
		 <tr class="row<?php echo $count?> ">
		 <?php if ($params->get('display_id', '1') == '1') {?>
		<td>
		 <a href="<?php echo $order->link ?>"><?php echo $order ->order_id?></a> 
		 </td>
        <?php  }?>
		 <?php if ($params->get('display_date', '1') == '1') {?>
		  <td>
		<a href="<?php echo $order->link; ?>">
          <?php echo JHTML::_('date', $order->created_date, TiendaConfig::getInstance()->get('date_format')); ?>
         </a>
		 </td>
		 <?php } ?>
		   <?php if ($params->get('display_price', '1') == '1')
				{?>
		 <td>
		<?php echo $order ->order_total?>
		 </td>
		   <?php } if ($params->get('display_state', '1') == '1')
				{?>
		  <td>
		 <?php echo $order ->order_state_name?> 
		 </td>
		 <?php } ?>
		 </tr>
		
  <?php  endforeach; ?>
	                </tbody>
    </table>
<?php }  
    elseif ($display_null == '1') 
{
    $text = JText::_( $null_text );
    echo $text;
}