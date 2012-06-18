<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'menu.css', 'media/com_tienda/css/');
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
JHTML::_('script', 'joomla.javascript.js', 'includes/js/');
Tienda::load( 'TiendaGrid', 'library.grid' );
$items = @$this->items;
$state = @$this->state;
Tienda::load( "TiendaHelperRoute", 'helpers.route' );
$router = new TiendaHelperRoute();
Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
?>

<div class='componentheading'>
    <span><?php echo JText::_('COM_TIENDA_MY_WISHLIST'); ?></span>
</div>

    <?php if ($menu =& TiendaMenu::getInstance( @$this->submenu )) { $menu->display(); } ?>
    
<div class="wishlistitems">
    <?php if (!empty($items)) { ?>
    <form action="<?php echo JRoute::_('index.php?option=com_tienda&view=wishlists&task=update&Itemid='.$router->findItemid( array('view'=>'wishlists') ) ); ?>" method="post" name="adminForm" enctype="multipart/form-data">

        <div style="float: right;">
        [<a href="<?php echo JRoute::_('index.php?option=com_tienda&view=checkout&Itemid='.$this->checkout_itemid ); ?>" onclick="return tiendaCheckUpdateCartQuantities(document.adminForm, '<?php echo JText::_('COM_TIENDA_CHECK_CART_UPDATE'); ?>');">
            <?php echo JText::_('COM_TIENDA_BEGIN_CHECKOUT'); ?>
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
                    <th colspan="2" style="text-align: left;"><?php echo JText::_('COM_TIENDA_PRODUCT'); ?></th>
                    <th style="width: 50px;"><?php echo JText::_('COM_TIENDA_DATE_ADDED'); ?></th>
                    <th style="width: 50px;"><?php echo JText::_('COM_TIENDA_BUY_NOW'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php $i=0; $k=0; ?> 
            <?php foreach ($items as $item) : ?>
            	
            	<?php            	
            		$params = new DSCParameter( trim(@$item->wishlistitem_params) );
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
                        <input type="checkbox" id="cb<?php echo $i; ?>" name="cid[<?php echo $item->wishlist_id; ?>]" value="<?php echo $item->wishlist_id; ?>" onclick="isChecked(this.checked);" />
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

                        <?php if ($item->product_recurs) { ?>
                            <?php echo JText::_('COM_TIENDA_RECURRING_PRICE'); ?>: <?php echo TiendaHelperBase::currency($item->recurring_price); ?>
                            (<?php echo $item->recurring_payments . " " . JText::_('COM_TIENDA_PAYMENTS'); ?>, <?php echo $item->recurring_period_interval." ". JText::_('COM_TIENDA_PERIOD_UNIT_'.$item->recurring_period_unit)." ".JText::_('COM_TIENDA_PERIODS'); ?>) 

				            <?php if( $item->subscription_prorated ) { ?>
                                <br/>
                                <?php echo JText::_('COM_TIENDA_INITIAL_PERIOD_PRICE'); ?>: <?php echo TiendaHelperBase::currency($item->recurring_trial_price); ?>
                                (<?php echo "1 " . JText::_('COM_TIENDA_PAYMENT'); ?>, <?php echo $item->recurring_trial_period_interval." ". JText::_('COM_TIENDA_PERIOD_UNIT_'.$item->recurring_period_unit)." ".JText::_('COM_TIENDA_PERIOD'); ?>)
				            <?php } else { ?>
	                            <?php if ($item->recurring_trial) { ?>
   	                                <br/>
                                    <?php echo JText::_('COM_TIENDA_TRIAL_PERIOD_PRICE'); ?>: <?php echo TiendaHelperBase::currency($item->recurring_trial_price); ?>
                                    (<?php echo "1 " . JText::_('COM_TIENDA_PAYMENT'); ?>, <?php echo $item->recurring_trial_period_interval." ". JText::_('COM_TIENDA_PERIOD_UNIT_'.$item->recurring_period_unit)." ".JText::_('COM_TIENDA_PERIOD'); ?>)
				                <?php } ?>
                            <?php } ?>    
                        <?php } else { ?>
                            <?php echo JText::_('COM_TIENDA_PRICE'); ?>: <?php echo TiendaHelperBase::currency($item->product_price); ?>                         
                        <?php } ?> 
                        
                        <br/> <?php echo TiendaHelperProduct::getRatingImage( $item->product_rating ); ?>  <br/>
                        
					    <?php if (!empty($this->onDisplayCartItem) && (!empty($this->onDisplayCartItem[$i]))) : ?>
					        <div class='onDisplayCartItem_wrapper_<?php echo $i?>'>
					        <?php echo $this->onDisplayCartItem[$i]; ?>
					        </div>
					    <?php endif; ?>                        
                    </td>
                    <td style="width: 50px; text-align: center;">
                        <?php echo JHTML::_( 'date', $item->last_updated, '%b %d' ); ?>
                    </td>
                    <td style="width: 50px; text-align: center;">
                        <span class="<?php if (empty($item->available)) { echo "wishlist_item_unavailable"; } else { echo "wishlist_item_available"; } ?>">
                        <?php if (empty($item->available)) { echo JText::_('COM_TIENDA_WISHLIST_UNAVAILABLE'); } else { echo JText::_('COM_TIENDA_WISHLIST_AVAILABLE'); } ?>
                        </span>
                    </td>
                </tr>
            <?php ++$i; $k = (1 - $k); ?>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="20" style="text-align: left;">
                        <input type="submit" class="button" value="<?php echo JText::_('COM_TIENDA_ADD_TO_CART'); ?>" name="addtocart" />
                        <input type="submit" class="button" value="<?php echo JText::_('COM_TIENDA_REMOVE'); ?>" name="remove" />
                        <input type="submit" class="button" value="<?php echo JText::_('COM_TIENDA_SHARE'); ?>" name="share" />
                    </td>
                </tr>
            </tfoot>
        </table>
        </div>  
        
        <div style="float: right;">
            [<a href="<?php echo JRoute::_('index.php?option=com_tienda&view=checkout&Itemid='.$this->checkout_itemid ); ?>" onclick="return tiendaCheckUpdateCartQuantities(document.adminForm, '<?php echo JText::_('COM_TIENDA_CHECK_CART_UPDATE'); ?>');">
                <?php echo JText::_('COM_TIENDA_BEGIN_CHECKOUT'); ?>
            </a>]
        </div>
        
        <?php if (!empty($this->return)) { ?>
            <div style="float: left;">
                [<a href="<?php echo $this->return; ?>">
                    <?php echo JText::_('COM_TIENDA_CONTINUE_SHOPPING'); ?>
                </a>]
            </div>
        <?php } ?>

        
        <div style="clear: both;"></div>
        
        <input type="hidden" name="boxchecked" value="" />
        <?php echo $this->form['validate']; ?>
        
    </form>
    <?php } else { ?>
        <p><?php echo JText::_('COM_TIENDA_NO_ITEMS_IN_WISHLIST'); ?></p>
    <?php } ?>
</div>