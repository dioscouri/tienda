<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>

<div id="tienda" class="search default">

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>
    <div id="tienda_searchfilters">
        <h2><?php echo JText::_('Advanced Search'); ?></h2>
        <?php $attribs = array('class' => 'inputbox', 'size' => '1', 'onchange' => 'document.adminForm.submit();'); ?>
        
        <div class="row filtername" >
            <span class="label"><?php echo JText::_('Name'); ?>:</span> 
            <input id="filter_name" name="filter_name" value="<?php echo @$state->filter_name; ?>" size="25" class="text_area" />
        </div>            

        <div class="row filtermulticategory" >
            <span class="label"><?php echo JText::_('Category'); ?>: </span> 
            <?php $catattribut = array('class' => 'inputbox', 'size' => '1','multiple' => 'yes' , 'size'=>5 );?>
            <?php echo TiendaSelect::category( @$state->filter_multicategory, 'filter_multicategory[]',$catattribut , 'filter_multicategory', true ); ?>
        </div>
        
        <div class="row filtershipping" >
            <span class="label"><?php echo JText::_('Requires Shipping'); ?>: </span> 
            <?php echo TiendaSelect::booleans( @$state->filter_ships, 'filter_ships', '', 'ships', true, "Doesn't Matter", 'Yes', 'No' ); ?>
        </div>
        
        <div class="row filtersku" >   
            <span class="label"><?php echo JText::_('SKU'); ?>: </span>         	                
            <input id="filter_sku" name="filter_sku" value="<?php echo @$state->filter_sku; ?>" size="15" class="text_area" />
        </div>
        
        <div class="row price">
        	<span class="label"><?php echo JText::_('Price Range'); ?>: </span> 
            <div class="range">
            	<div><span class="label"><?php echo JText::_('From'); ?>:</span> <input id="filter_price_from" name="filter_price_from" value="<?php echo @$state->filter_price_from; ?>" size="5" class="input" class="text_area" /></div>
                <div><span class="label"><?php echo JText::_('To'); ?>:</span> <input id="filter_price_to" name="filter_price_to" value="<?php echo @$state->filter_price_to; ?>" size="5" class="input" class="text_area" /></div>
        	</div>
        </div>
        
        <div class="reset"></div>
        
        <div class="row quantity">
            <span class="label"><?php echo JText::_('Show only Items that are in Stock'); ?>: </span>
            <?php echo TiendaSelect::booleanlist( 'filter_stock', '', @$state->filter_stock ); ?> 
        </div>
        
        <div class="row filterdescription" >   
            <span class="label"><?php echo JText::_('Description'); ?>: </span>         	                
            <input id="filter_description" name="filter_description" value="<?php echo @$state->filter_description; ?>" size="15" class="text_area" />
        </div>
        
        <div class="row filtermanufacturer" >   
            <span class="label"><?php echo JText::_('Manufacturer'); ?>: </span>         	                
            <input id="filter_manufacturer" name="filter_manufacturer" value="<?php echo @$state->filter_manufacturer; ?>" size="15" class="text_area" />
        </div>
        
        <div class="row submit" >   
            <input id="filter_submit" name="filter_submit" type="submit" value="<?php echo JText::_('Search') ?>" class="button" />
        </div>
        
        <div class="reset"></div>
    </div>
	
    <div id="tienda_searchresults">
        <h2><?php echo JText::_('Search Results'); ?></h2>
        <div id="searchresults_sort">
            <div class="sortresults title"><?php echo JText::_('Sort Results By'); ?>:</div>
            <div class="sortresults option"><?php echo TiendaGrid::sort( 'Name', "tbl.product_name", @$state->direction, @$state->order ); ?></div>
            <div class="sortresults option"><?php echo TiendaGrid::sort( 'SKU', "tbl.product_sku", @$state->direction, @$state->order ); ?></div>
            <div class="sortresults option"><?php echo TiendaGrid::sort( 'Price', "price", @$state->direction, @$state->order ); ?></div>
            <div class="sortresults option"><?php echo TiendaGrid::sort( 'Rating', "tbl.product_rating", @$state->direction, @$state->order ); ?></div>
            <div class="sortresults option"><?php echo TiendaGrid::sort( 'Reviews', "tbl.product_comments", @$state->direction, @$state->order ); ?></div>
            <div class="reset"></div>
        </div>
        
        <div id="searchresults_results">
            <?php $i=0; $k=0; ?>
            <?php foreach (@$items as $item) : ?>
            <div class="product_item">
                <div class="product_thumb">
                    <a href="<?php echo JRoute::_( $item->link."&filter_category=".$item->category_id."&Itemid=".@$item->itemid ); ?>">
                        <?php echo TiendaHelperProduct::getImage($item->product_id, 'id', $item->product_name, 'full', false, false, array( 'width'=>48 ) ); ?>
                    </a>
                </div>
                
                <div class="product_buy">
                    <?php if (empty($item->product_notforsale)) : ?>

                        <?php if (!empty($item->product_listprice_enabled)) : ?>
                            <div class="product_listprice">
                            <span class="title"><?php echo JText::_('List Price'); ?>:</span>
                            <del><?php echo TiendaHelperBase::currency($item->product_listprice); ?></del>
                            </div>                                
                        <?php endif; ?>
                        
                        <div class="product_price">
                        <?php
                        // For UE States, we should let the admin choose to show (+19% vat) and (link to the shipping rates)
                        $config = TiendaConfig::getInstance();
                        $show_tax = $config->get('display_prices_with_tax');
                        
                        $article_link = $config->get('article_shipping', '');
                        $shipping_cost_link = JRoute::_('index.php?option=com_content&view=article&id='.$article_link);
                        
                        if (!empty($show_tax))
                        {
                            Tienda::load('TiendaHelperUser', 'helpers.user');
                            $geozones = TiendaHelperUser::getGeoZones( JFactory::getUser()->id );
                            if (empty($geozones))
                            {
                                // use the default
                                $table = JTable::getInstance('Geozones', 'TiendaTable');
                                $table->load(array('geozone_id'=>TiendaConfig::getInstance()->get('default_tax_geozone')));
                                $geozones = array( $table );
                            }
                            $taxtotal = TiendaHelperProduct::getTaxTotal($item->product_id, $geozones);
                            $tax = $taxtotal->tax_total;
                            if (!empty($tax))
                            {
                                if ($show_tax == '2')
                                {
                                    // sum
                                    echo TiendaHelperBase::currency($item->price + $tax);
                                }
                                    else
                                {
                                    echo TiendaHelperBase::currency($item->price);
                                    echo sprintf( JText::_('INCLUDE_TAX'), TiendaHelperBase::currency($tax));
                                }    
                            }
                                else
                            {
                                echo TiendaHelperBase::currency($item->price); 
                            }
                        }
                           else
                        {
                            echo TiendaHelperBase::currency($item->price); 
                        }

                        if (TiendaConfig::getInstance()->get( 'display_prices_with_shipping') && !empty($item->product_ships))
                        {
                            echo '<br /><a href="'.$shipping_cost_link.'" target="_blank">'.sprintf( JText::_('LINK_TO_SHIPPING_COST'), $shipping_cost_link).'</a>' ;
                        }
                        ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php // TODO Make this display the "quickAdd" layout in a lightbox ?>
                    <?php // $url = "index.php?option=com_tienda&format=raw&controller=carts&task=addToCart&productid=".$item->product_id; ?>
                    <?php // $onclick = 'tiendaDoTask(\''.$url.'\', \'tiendaUserShoppingCart\', \'\');' ?>
                    <?php // <img class="addcart" src="media/com_tienda/images/addcart.png" alt="" onclick="<?php echo $onclick; " /> ?>
                </div>
                
                <div class="product_info">
                    <div class="product_name">
                        <span>
                            <a href="<?php echo JRoute::_($item->link."&filter_category=".$item->category_id."&Itemid=".$item->itemid ); ?>">
                            <?php echo $item->product_name; ?>
                            </a>
                        </span>
                    </div>
                    
                    <div class="product_rating">
                       <?php echo TiendaHelperProduct::getRatingImage( $item->product_rating ); ?>
                       <?php if (!empty($item->product_comments)) : ?>
                       <span class="product_comments_count">(<?php echo $item->product_comments; ?>)</span>
                       <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($item->product_model) || !empty($item->product_sku)) { ?>
                        <div class="product_numbers">
                            <span class="model">
                                <?php if (!empty($item->product_model)) : ?>
                                    <span class="title"><?php echo JText::_('Model'); ?>:</span> 
                                    <?php echo $item->product_model; ?>
                                <?php endif; ?>
                            </span>
                            <span class="sku">
                                <?php if (!empty($item->product_sku)) : ?>
                                    <span class="title"><?php echo JText::_('SKU'); ?>:</span> 
                                    <?php echo $item->product_sku; ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php } ?>

                    <div class="product_minidesc">
                    <?php
                        if (!empty($item->product_description_short))
                        {
                            echo $item->product_description_short;
                        }
                            else
                        {                  
                            $str = wordwrap($item->product_description, 200, '`|+');
                            $wrap_pos = strpos($str, '`|+');
                            if ($wrap_pos !== false) {
                                echo substr($str, 0, $wrap_pos).'...';
                            } else {
                                echo $str;
                            }    
                        }
                    ?>
                    </div>
                </div>
            </div>
            
            <div class="reset"></div>
            
            <?php ++$i; $k = (1 - $k); ?>
            <?php endforeach; ?>

            <?php if (!count(@$items)) : ?>
            <div class="product_item">
                <?php echo JText::_('COM_TIENDA_NO_ITEMS_FOUND'); ?>
            </div>
            <?php endif; ?>
        </div>
       
        <div id="searchresults_footer">
            <div id="results_counter" class="pagination"><?php echo @$this->pagination->getResultsCounter(); ?></div>
            <?php echo @$this->pagination->getListFooter(); ?>
        </div>
    </div>
    
    <div class="reset"></div>
    
    <input type="hidden" name="order_change" value="0" />
	<input type="hidden" name="id" value="" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />

	<?php echo $this->form['validate']; ?>
</form>

</div>