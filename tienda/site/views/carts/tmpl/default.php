<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'menu.css', 'media/com_tienda/css/');
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
JHTML::_('script', 'joomla.javascript.js', 'includes/js/');
Tienda::load( 'TiendaGrid', 'library.grid' );
$items = @$this->cartobj->items;
$subtotal = @$this->cartobj->subtotal;
$state = @$this->state;
Tienda::load( "TiendaHelperRoute", 'helpers.route' );
$router = new TiendaHelperRoute();
$quantities = array();
Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
?>

<div class='componentheading'>
    <span><?php echo JText::_( "My Shopping Cart" ); ?></span>
</div>

    <?php if ($menu =& TiendaMenu::getInstance( @$this->submenu )) { $menu->display(); } ?>
    
<div class="cartitems">
    <?php if (!empty($items)) { ?>
    <form action="<?php echo JRoute::_('index.php?option=com_tienda&view=carts&task=update&Itemid='.$router->findItemid( array('view'=>'carts') ) ); ?>" method="post" name="adminForm" enctype="multipart/form-data">

        <div style="float: right;">
        [<a href="<?php echo JRoute::_('index.php?option=com_tienda&view=checkout&Itemid='.$this->checkout_itemid ); ?>" onclick="return tiendaCheckUpdateCartQuantities(document.adminForm, '<?php echo JText::_('CHECK_CART_UPDATE'); ?>');">
            <?php echo JText::_( "Begin Checkout" ); ?>
        </a>]
        </div>
        <div class="reset"></div>
        <div id="onCheckoutCart_wrapper">
        <table class="adminlist">
            <thead>
                <tr>
                    <th style="width: 20px;">
                	   <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                    </th>
                    <th colspan="2" style="text-align: left;"><?php echo JText::_( "Product" ); ?></th>
                    <th style="width: 50px;"><?php echo JText::_( "Quantity" ); ?></th>
                    <th style="width: 50px;"><?php echo JText::_( "Total" ); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php $i=0; $k=0; $subtotal = 0;?> 
            <?php foreach ($items as $item) : ?>
            	
            	<?php            	
            		$params = new JParameter( trim(@$item->cartitem_params) );
            		$default_url = "index.php?option=com_tienda&view=products&task=view&id=".$item->product_id;
            		$attributes = TiendaHelperProduct::convertAttributesToArray( $item->product_id, $item->product_attributes );
            		for( $j = 0, $c = count( $attributes ); $j < $c; $j++ )
            		{
            			$default_url .= '&attribute_'.$attributes[$j][0].'='.$attributes[$j][1];
            		}	
            		$link = $params->get('product_url', $default_url );
            		$link = JRoute::_($link);
            	?>
            
                <tr class="row<?php echo $k; ?>">
                    <td style="width: 20px; text-align: center;">
                        <input type="checkbox" id="cb<?php echo $i; ?>" name="cid[<?php echo $item->cart_id; ?>]" value="<?php echo $item->product_id; ?>" onclick="isChecked(this.checked);" />
                    </td>
                    <td style="text-align: center; width: 50px;">
                        <?php echo TiendaHelperProduct::getImage($item->product_id, 'id', $item->product_name, 'full', false, false, array( 'width'=>48 ) ); ?>
                    </td>
                    <td>
                        <a href="<?php echo $link; ?>">
                            <?php echo $item->product_name; ?>
                        </a>
                        <br/>
                        
                        <?php if (!empty($item->attributes_names)) : ?>
	                        <?php echo $item->attributes_names; ?>
	                        <br/>
	                    <?php endif; ?>
	                    <input name="product_attributes[<?php echo $item->cart_id; ?>]" value="<?php echo $item->product_attributes; ?>" type="hidden" />                       
                      
                        <?php if (!empty($item->product_sku)) : ?>
                            <b><?php echo JText::_( "SKU" ); ?>:</b>
                            <?php echo $item->product_sku; ?>
                            <br/>
                        <?php endif; ?>
                      
                        <?php if ($item->product_recurs) : ?>
                            <?php $recurring_subtotal = $item->recurring_price; ?>
                            <?php echo JText::_( "RECURRING PRICE" ); ?>: <?php echo TiendaHelperBase::currency($item->recurring_price); ?>
                            (<?php echo $item->recurring_payments . " " . JText::_( "PAYMENTS" ); ?>, <?php echo $item->recurring_period_interval." ". JText::_( "$item->recurring_period_unit PERIOD UNIT" )." ".JText::_( "PERIODS" ); ?>) 

										            <?php if( $item->subscription_prorated ) : ?>
		                                <br/>
		                                <?php echo JText::_( "Initial Period Price" ); ?>: <?php echo TiendaHelperBase::currency($item->recurring_trial_price); ?>
		                                (<?php echo "1 " . JText::_( "PAYMENT" ); ?>, <?php echo $item->recurring_trial_period_interval." ". JText::_( "$item->recurring_trial_period_unit PERIOD UNIT" )." ".JText::_( "PERIOD" ); ?>)
										            <?php else : ?>
			                            <?php if ($item->recurring_trial) : ?>
			                                <br/>
		                                <?php echo JText::_( "TRIAL PERIOD PRICE" ); ?>: <?php echo TiendaHelperBase::currency($item->recurring_trial_price); ?>
		                                (<?php echo "1 " . JText::_( "PAYMENT" ); ?>, <?php echo $item->recurring_trial_period_interval." ". JText::_( "$item->recurring_trial_period_unit PERIOD UNIT" )." ".JText::_( "PERIOD" ); ?>)
										            <?php endif;?>
                            <?php endif; ?>    
                        <?php else : ?>
                            <?php echo JText::_( "Price" ); ?>: <?php echo TiendaHelperBase::currency($item->product_price); ?>                         
                        <?php endif; ?> 
                        
					    <?php if (!empty($this->onDisplayCartItem) && (!empty($this->onDisplayCartItem[$i]))) : ?>
					        <div class='onDisplayCartItem_wrapper_<?php echo $i?>'>
					        <?php echo $this->onDisplayCartItem[$i]; ?>
					        </div>
					    <?php endif; ?>                        
                    </td>
                    <td style="width: 50px; text-align: center;">
                        <?php $type = 'text'; 
                        if ($item->product_parameters->get('hide_quantity_cart') == '1') { 
                            $type = 'hidden';
                            echo $item->product_qty;
                        } ?>
                        
                        <input name="quantities[<?php echo $item->cart_id; ?>]" type="<?php echo $type; ?>" size="3" maxlength="3" value="<?php echo $item->product_qty; ?>" />
                        
                        <!-- Keep Original quantity to check any update to it when going to checkout -->
                        <input name="original_quantities[<?php echo $item->cart_id; ?>]" type="hidden" value="<?php echo $item->product_qty; ?>" />
                    </td>
                    <td style="text-align: right;">                       
                        <?php $subtotal = $subtotal + $item->subtotal; ?>
                        <?php echo TiendaHelperBase::currency($item->subtotal); ?>
                    </td>
                </tr>
            <?php ++$i; $k = (1 - $k); ?>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: left;">
                        <input type="submit" class="button" value="<?php echo JText::_('Remove Selected'); ?>" name="remove" />
                    </td>
                    <td colspan="2">
                        <input style="float: right;" type="submit" class="button" value="<?php echo JText::_('Update Quantities'); ?>" name="update" />
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="font-weight: bold;">
                        <?php echo JText::_( "Subtotal" ); ?>
                    </td>
                    <td style="text-align: right;">
                        <span id="totalAmountDue"><?php echo TiendaHelperBase::currency($subtotal); ?></span>
                    </td>
                </tr>
                <tr>
                	<td colspan="5" style="white-space: nowrap;">
                        <b><?php echo JText::_( "Tax and Shipping Totals" ); ?></b>
                        <br/>
                        <?php
                            echo JText::_( "Calculated during checkout process" );
                    	?>
              	 	</td>
                </tr>
                <tr>
                    <td colspan="4">
                        <?php if (!empty($this->return)) { ?>
                        [<a href="<?php echo $this->return; ?>">
                            <?php echo JText::_( "Continue Shopping" ); ?>
                        </a>]
                        <?php } ?>
                    </td>
                    <td style="text-align: right;" nowrap>
				        <div style="float: right;">
				        [<a href="<?php echo JRoute::_('index.php?option=com_tienda&view=checkout&Itemid='.$this->checkout_itemid ); ?>" onclick="return tiendaCheckUpdateCartQuantities(document.adminForm, '<?php echo JText::_('CHECK_CART_UPDATE'); ?>');">
				            <?php echo JText::_( "Begin Checkout" ); ?>
				        </a>]
				        </div>
                    </td>
                </tr>
            </tfoot>
        </table>
        </div>        
        <input type="hidden" name="boxchecked" value="" />
        <?php echo $this->form['validate']; ?>
        
    </form>
    <?php } else { ?>
    <p><?php echo JText::_( "No items in your cart" ); ?></p>
    <?php } ?>
</div>