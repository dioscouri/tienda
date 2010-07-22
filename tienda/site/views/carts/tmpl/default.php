<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'menu.css', 'media/com_tienda/css/');
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
JHTML::_('script', 'joomla.javascript.js', 'includes/js/');
Tienda::load( 'TiendaGrid', 'library.grid' );
$items = @$this->items;
$state = @$this->state;
?>

<div class='componentheading'>
    <span><?php echo JText::_( "My Shopping Cart" ); ?></span>
</div>

    <?php if ($menu =& TiendaMenu::getInstance()) { $menu->display(); } ?>
    
<div class="cartitems">
    <?php if (!empty($items)) { ?>
    <form action="<?php echo JRoute::_('index.php?option=com_tienda&view=carts&task=update'); ?>" method="post" name="adminForm" enctype="multipart/form-data">

        <div style="float: right;">
        [<a href="<?php echo JRoute::_('index.php?option=com_tienda&view=checkout'); ?>">
            <?php echo JText::_( "Begin Checkout" ); ?>
        </a>]
        </div>
        
        <table class="adminlist" style="clear: both;">
            <thead>
                <tr>
                    <th style="width: 20px;">
                	   <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                    </th>
                    <th style="text-align: left;"><?php echo JText::_( "Product" ); ?></th>
                    <th style="width: 50px;"><?php echo JText::_( "Quantity" ); ?></th>
                    <th style="width: 50px;"><?php echo JText::_( "Total" ); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php $i=0; $k=0; $subtotal = 0; ?> 
            <?php foreach ($items as $item) : ?>
                <tr class="row<?php echo $k; ?>">
                    <td style="width: 20px; text-align: center;">
                        <input type="checkbox" id="cb<?php echo $i; ?>" name="cid[<?php echo $item->product_id.".".$item->product_attributes; ?>]" value="<?php echo $item->product_id; ?>" onclick="isChecked(this.checked);" />
                    </td>
                    <td>
                        <a href="<?php echo JRoute::_("index.php?option=com_tienda&view=products&task=view&id=".$item->product_id); ?>">
                            <?php echo $item->product_name; ?>
                        </a>
                        <br/>
                        
                        <?php if (!empty($item->attributes_names)) : ?>
	                        <?php echo $item->attributes_names; ?>
	                        <br/>
	                    <?php endif; ?>
	                    <input name="product_attributes[<?php echo $item->product_id.".".$item->product_attributes; ?>]" value="<?php echo $item->product_attributes; ?>" type="hidden" />
                        
                        <?php if ($item->product_recurs) : ?>
                            <?php $recurring_subtotal = $item->recurring_price; ?>
                            <?php echo JText::_( "RECURRING PRICE" ); ?>: <?php echo TiendaHelperBase::currency($item->recurring_price); ?>
                            (<?php echo $item->recurring_payments . " " . JText::_( "PAYMENTS" ); ?>, <?php echo $item->recurring_period_interval." ". JText::_( "$item->recurring_period_unit PERIOD UNIT" )." ".JText::_( "PERIODS" ); ?>) 
                            <?php if ($item->recurring_trial) : ?>
                                <br/>
                                <?php echo JText::_( "TRIAL PERIOD PRICE" ); ?>: <?php echo TiendaHelperBase::currency($item->recurring_trial_price); ?>
                                (<?php echo "1 " . JText::_( "PAYMENT" ); ?>, <?php echo $item->recurring_trial_period_interval." ". JText::_( "$item->recurring_trial_period_unit PERIOD UNIT" )." ".JText::_( "PERIOD" ); ?>)
                            <?php endif; ?>    
                        <?php else : ?>
                            <?php echo JText::_( "Price" ); ?>: <?php echo TiendaHelperBase::currency($item->product_price); ?>                         
                        <?php endif; ?> 
                    </td>
                    <td style="width: 50px; text-align: center;">
                        <input name="quantities[<?php echo $item->product_id.".".$item->product_attributes; ?>]" type="text" size="3" maxlength="3" value="<?php echo $item->product_qty; ?>" />
                    </td>
                    <td style="text-align: right;">
                        <?php $itemsubtotal = $item->product_price * $item->product_qty; ?>
                        <?php $subtotal = $subtotal + $itemsubtotal; ?>
                        <?php echo TiendaHelperBase::currency($itemsubtotal); ?>
                    </td>
                </tr>
            <?php ++$i; $k = (1 - $k); ?>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align: left;">
                        <input type="submit" class="button" value="<?php echo JText::_('Remove Selected'); ?>" name="remove" />
                    </td>
                    <td colspan="2">
                        <input style="float: right;" type="submit" class="button" value="<?php echo JText::_('Update Quantities'); ?>" name="update" />
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="font-weight: bold;">
                        <?php echo JText::_( "Subtotal" ); ?>
                    </td>
                    <td style="text-align: right;">
                        <?php echo TiendaHelperBase::currency($subtotal); ?>
                    </td>
                </tr>
                <tr>
                	<td colspan="4" style="white-space: nowrap;">
                        <b><?php echo JText::_( "Tax and Shipping Totals" ); ?></b>
                        <br/>
                        <?php
                            echo JText::_( "Calculated during checkout process" );
                    	?>
              	 	</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <?php if (!empty($this->return)) { ?>
                        [<a href="<?php echo $this->return; ?>">
                            <?php echo JText::_( "Continue Shopping" ); ?>
                        </a>]
                        <?php } ?>
                    </td>
                    <td style="text-align: right;" nowrap>
				        <div style="float: right;">
				        [<a href="<?php echo JRoute::_('index.php?option=com_tienda&view=checkout'); ?>">
				            <?php echo JText::_( "Begin Checkout" ); ?>
				        </a>]
				        </div>
                    </td>
                </tr>
            </tfoot>
        </table>
        
        <input type="hidden" name="boxchecked" value="" />
        <?php echo $this->form['validate']; ?>
        
    </form>
    <?php } else { ?>
    <p><?php echo JText::_( "No items in your cart" ); ?></p>
    <?php } ?>
</div>