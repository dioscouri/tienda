<?php
	defined('_JEXEC') or die('Restricted access');
	JHTML::_('stylesheet', 'menu.css', 'media/com_tienda/css/');
	JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
	JHTML::_('script', 'joomla.javascript.js', 'includes/js/');
	Tienda::load( 'TiendaGrid', 'library.grid' );
	$items = @$this->items;
	$state = @$this->state;
	Tienda::load( "TiendaHelperRoute", 'helpers.route' );
	$router = new TiendaHelperRoute();
	Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
	$menu = TiendaMenu::getInstance( @$this->submenu );
	$products_model = $this->getModel('products');
?>

<script type="text/javascript">
tiendaJQ(document).ready(function(){

    tiendaJQ('.privatize-wishlist').on('change', function(){
        el = tiendaJQ(this);
        privacy = el.val();
        if (privacy > 0) {
            Tienda.privatizeWishlist(<?php echo $this->row->wishlist_id; ?>, privacy, function(response){
                container = tiendaJQ('#message-container');
                container.find('.confirmation').remove();
                container.append('<p class="confirmation">'+response.html+'</p>').find('.confirmation').fadeIn().delay(1500).fadeOut().delay(3000);
            });            
        }
    });
    
    tiendaJQ('.delete-wishlistitem').on('click', function(){
        el = tiendaJQ(this);
        wishlistitem_id = el.attr('data-wishlistitem_id');
        if (wishlistitem_id) {
            Tienda.deleteWishlistItem(wishlistitem_id, '<?php echo JText::_("COM_TIENDA_CONFIRM_DELETE_WISHLISTITEM"); ?>', function(){
                tiendaJQ('.wishlistitem-'+wishlistitem_id).remove();
            });            
        }
    });
    
    tiendaJQ('.delete-wishlist').on('click', function(){
        el = tiendaJQ(this);
        Tienda.deleteWishlist(<?php echo $this->row->wishlist_id; ?>, '<?php echo JText::_("COM_TIENDA_CONFIRM_DELETE_WISHLIST"); ?>', function(){
            window.location = '<?php echo JRoute::_('index.php?option=com_tienda&view=wishlists&Itemid='.$router->findItemid( array('view'=>'wishlists') ) ); ?>';
        });         
    });

    tiendaJQ('.rename-wishlist').on('click', function() {
        el = tiendaJQ(this);
        Tienda.renameWishlist(<?php echo $this->row->wishlist_id; ?>, '<?php echo JText::_("COM_TIENDA_PROVIDE_WISHLIST_NAME"); ?>', function(response){
            tiendaJQ('.wishlist-name.wishlist-<?php echo $this->row->wishlist_id; ?>').html( response.wishlist_name );
        });
    });
});
</script>

<div id="message-container" class="dsc-wrap">
    <h2 class="dsc-wrap">
        <span class="wishlist-name wishlist-<?php echo $this->row->wishlist_id; ?>">
            <?php echo $this->row->wishlist_name; ?>
        </span>
        <a class="rename-wishlist" href="javascript:void(0);">
            <small>
            <?php echo JText::_( "COM_TIENDA_RENAME_WISHLIST" ); ?>
            </small>
        </a>
        
        <a class="delete-wishlist pull-right btn btn-danger indent-10" href="javascript:void(0);">
            <?php echo JText::_( "COM_TIENDA_DELETE_WISHLIST" ); ?>
        </a>
        
        <select name="wishlist-privacy-<?php echo $this->row->wishlist_id; ?>" id="wishlist-privacy-<?php echo $this->row->wishlist_id; ?>" class="privatize-wishlist pull-right input input-small">
            <option value="1" <?php if ($this->row->privacy == '1') { echo "selected='selected'"; } ?>><?php echo JText::_( "COM_TIENDA_PUBLIC" ); ?></option>
            <option value="2" <?php if ($this->row->privacy == '2') { echo "selected='selected'"; } ?>><?php echo JText::_( "COM_TIENDA_LINK_ONLY" ); ?></option>
            <option value="3" <?php if ($this->row->privacy == '3') { echo "selected='selected'"; } ?>><?php echo JText::_( "COM_TIENDA_PRIVATE" ); ?></option>
        </select>
    </h2>
</div>

<?php if( $menu ) { $menu->display(); } ?>

<div class="wishlist-items dsc-wrap">
    <?php if (!empty($items)) { ?>
    <form action="<?php echo JRoute::_('index.php?option=com_tienda&view=wishlists&task=update&Itemid='.$router->findItemid( array('view'=>'wishlists') ) ); ?>" method="post" name="adminForm" enctype="multipart/form-data" class="dsc-wrap">

        <div class="dsc-wrap bottom-10">
            <a href="<?php echo JRoute::_('index.php?option=com_tienda&view=wishlists&Itemid='.$router->findItemid( array('view'=>'wishlists') ) ); ?>">
                <?php echo JText::_( "COM_TIENDA_RETURN_TO_LIST" ); ?>
            </a>
                    
            <a class="pull-right btn btn-primary" href="<?php echo JRoute::_('index.php?option=com_tienda&view=checkout&Itemid='.$this->checkout_itemid ); ?>" onclick="return tiendaCheckUpdateCartQuantities(document.adminForm, '<?php echo JText::_('COM_TIENDA_CHECK_CART_UPDATE'); ?>');">
                <?php echo JText::_('COM_TIENDA_BEGIN_CHECKOUT'); ?>
            </a>
        </div>
        
        <table class="dsc-clear table item-grid">
            <thead>
                <tr>
                    <th>
                    </th>
                    <th colspan="2"></th>
                    <th><?php echo JText::_('COM_TIENDA_DATE_ADDED'); ?></th>
                    <th><?php echo JText::_('COM_TIENDA_STATUS'); ?></th>
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
            		if ($itemid = $products_model->getItemid( $item->product_id ))
            		{
            		    $default_url .= "&Itemid=" . $itemid;
            		}            		
            		$link = $params->get('product_url', $default_url );
            		$link = JRoute::_($link);
            	?>
            
                <tr class="row<?php echo $k; ?> wishlistitem-<?php echo $item->wishlistitem_id; ?>">
                    <td>
                        <a class="delete-wishlistitem btn btn-danger" href="javascript:void(0);" data-wishlistitem_id="<?php echo $item->wishlistitem_id; ?>">
                            <?php echo JText::_( "COM_TIENDA_DELETE_WISHLISTITEM" ); ?>
                        </a>
                    </td>
                    <td class="product_thumb_container">
                        <?php $product_image = TiendaHelperProduct::getImage($item->product_id, '', '', 'full', true, false, array(), true ); ?>
                        <?php if ($product_image) { ?>
                        <div class="dsc-wrap product_thumb frame">
                            <div class="frame-inner">
                                <a href="<?php echo $link; ?>">
                	            <img src="<?php echo $product_image; ?>" />
                	            </a>
                            </div>
                        </div>
                        <?php } ?>
                    </td>
                    <td class="wishlist-column-product">
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
                    <td class="wishlist-column-date">
                        <?php echo JHTML::_( 'date', $item->last_updated, 'M d' ); ?>
                    </td>
                    <td class="wishlist-column-status">
                        <span class="<?php if (empty($item->available)) { echo "wishlist_item_unavailable"; } else { echo "wishlist_item_available"; } ?>">
                        <?php if (empty($item->available)) { 
                            echo JText::_('COM_TIENDA_WISHLIST_UNAVAILABLE'); 
                        } else { 
                            echo JText::_('COM_TIENDA_WISHLIST_AVAILABLE');
                            ?>
                            <div>
                                <a class="btn btn-success add-to-cart" href="<?php echo JRoute::_("index.php?option=com_tienda&view=wishlists&task=update&addtocart=1&cid[]=" . $item->wishlistitem_id ); ?>"><?php echo JText::_("COM_TIENDA_ADD_TO_CART"); ?></a>
                            </div>
                            <?php
                        } ?>
                        </span>
                    </td>
                </tr>
            <?php ++$i; $k = (1 - $k); ?>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="20" style="text-align: left;">
                        <?php /* ?>
                        <input type="submit" class="btn" value="<?php echo JText::_('COM_TIENDA_SHARE'); ?>" name="share" />
                        */ ?>
                    </td>
                </tr>
            </tfoot>
        </table>
        
        <?php if (!empty($this->pagination) && method_exists($this->pagination, 'getResultsCounter')) { ?>
        <form action="<?php echo JRoute::_( @$form['action']."&limitstart=".@$state->limitstart )?>" method="post" name="adminForm" enctype="multipart/form-data">        
        <div id="pagination-footer" class="pagination">
            <div id="results_counter"><?php echo $this->pagination->getResultsCounter(); ?></div>
                <?php 

                    $html = "<div class=\"list-footer\">\n";
                    $html .= $this->pagination->getPagesLinks();
                    $html .= "\n<div class=\"counter\">" . $this->pagination->getPagesCounter() . "</div>";
                    $html .= "\n<input type=\"hidden\" name=\"limitstart\" value=\"". $this->pagination->limitstart ."\" />";
                    $html .= "\n</div>";

                    echo $html;
                ?>
        </div>
        <?php echo $this->form['validate']; ?>
        </form>
        <?php } ?>
        
        <div style="clear: both;"></div>
        
        <input type="hidden" name="boxchecked" value="" />
        <?php echo $this->form['validate']; ?>
        
    </form>
    <?php } else { ?>
        <p><?php echo JText::_('COM_TIENDA_NO_ITEMS_IN_WISHLIST'); ?></p>
    <?php } ?>
</div>