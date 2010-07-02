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

$resize = false;
$options = array();
if ($params->get('display_image_width', '') != '')
{
	$options['width'] = $params->get('display_image_width');
}
if ($params->get('display_image_height', '') != '')
{
	$options['height'] = $params->get('display_image_height');
} 
if ($num > 0 && @$orders)
{ ?>
	<table style="clear: both;" class="adminlist">
        <thead>
              <tr>
                <th style="width: 50px;">
                   ID </th>
                <th style="width: 200px;">
                  Date                </th>
                <th style="width: 100px;">
                   Total</th>
                <th style="width: 100px;">
                    State                </th>
            </tr>
        </thead> <tbody>
  <?php  // Loop through the products to display
  
	 $count=0;
	  foreach (@$orders as $order) : ?>
		 <tr class="row<?php echo $count?> ">
		 <td>
		 <a href="<?php echo $order->link ?>"><?php echo $order ->order_id?></a> 
		 </td>
		  <td>
		<a href="<?php echo $order->link; ?>">
                        <?php echo JHTML::_('date', $order->created_date, TiendaConfig::getInstance()->get('date_format')); ?>
                    </a>
		 </td>
		 <td>
		<?php echo $order ->order_total?>
		 </td>
		  <td>
		 <?php echo $order ->order_state_name?> 
		 </td>
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