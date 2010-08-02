<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; ?>

<?php JFilterOutput::objectHTMLSafe($row); ?>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

		<div id='onBeforeDisplay_wrapper'>
			<?php 
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger( 'onBeforeDisplayConfigForm', array() );
			?>
		</div>                

		<table style="width: 100%;">
			<tbody>
                <tr>
					<td style="vertical-align: top; min-width: 70%;">

					<?php
					// display defaults
					$pane = '1';
					echo $this->sliders->startPane( "pane_$pane" );
					
					$legend = JText::_( "Shop Information" );
					echo $this->sliders->startPanel( JText::_( $legend ), 'shop' );
					
					?>
					
					<table class="adminlist">
					<tbody>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Enable Shopping' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'shop_enabled', 'class="inputbox"', $this->row->get('shop_enabled', '1') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Shop Name' ); ?>
                            </th>
                            <td>
                               <input type="text" name="shop_name" value="<?php echo $this->row->get('shop_name', ''); ?>" size="25" />
                            </td>
                            <td>
                                <?php echo JText::_( "The Name of the Shop" ); ?>
                            </td>
                        </tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Company Name' ); ?>
							</th>
			                <td>
			                	<input type="text" name="shop_company_name" value="<?php echo $this->row->get('shop_company_name', ''); ?>" size="25" />
			                </td>
                            <td>
                                
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Address Line 1' ); ?>
							</th>
			                <td>
			                	<input type="text" name="shop_address_1" value="<?php echo $this->row->get('shop_address_1', ''); ?>" size="35" />
			                </td>
                            <td>
                                
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Address Line 2' ); ?>
							</th>
			                <td>
			                	<input type="text" name="shop_address_2" value="<?php echo $this->row->get('shop_address_2', ''); ?>" size="35" />
			                </td>
                            <td>
                                
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'City' ); ?>
							</th>
			                <td>
			                	<input type="text" name="shop_city" value="<?php echo $this->row->get('shop_city', ''); ?>" size="25" />
			                </td>
                            <td>
                                
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Country' ); ?>
							</th>
			                <td>
			                	<?php
								// TODO Change this to use a task within the checkout controller rather than creating a new zones controller 
								$url = "index.php?option=com_tienda&format=raw&controller=addresses&task=getzones&name=shop_zone&country_id=";
								$attribs = array('onchange' => 'tiendaDoTask( \''.$url.'\'+document.getElementById(\'shop_country\').value, \'zones_wrapper\', \'\');' );
								echo TiendaSelect::country( $this->row->get('shop_country', ''), 'shop_country', $attribs,'shop_country', true );
								?>
			                </td>
                            <td>
                                
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'State / Region' ); ?>
							</th>
			                <td>
			                	<div id="zones_wrapper">
						            <?php 
						            $shop_zone = $this->row->get('shop_zone', '');
						            if (empty($shop_zone)) 
						            {
						            	echo JText::_( "Select Country First" ); 
						            }
						            else
						            {
						            	echo TiendaSelect::zone( $shop_zone, 'shop_zone', $this->row->get('shop_country', '') );
						            }
						            ?>
					            </div>
			                </td>
                            <td>
                                
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Postal Code' ); ?>
							</th>
			                <td>
			                	<input type="text" name="shop_zip" value="<?php echo $this->row->get('shop_zip', ''); ?>" />
			                </td>
                            <td>
                                
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Tax Number 1' ); ?>
							</th>
			                <td>
			                	<input type="text" name="shop_tax_number_1" value="<?php echo $this->row->get('shop_tax_number_1', ''); ?>" size="25" />
			                </td>
                            <td>
                                
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Tax Number 2' ); ?>
							</th>
			                <td>
			                	<input type="text" name="shop_tax_number_2" value="<?php echo $this->row->get('shop_tax_number_2', ''); ?>" size="25" />
			                </td>
                            <td>
                                
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Phone' ); ?>
							</th>
			                <td>
			                	<input type="text" name="shop_phone" value="<?php echo $this->row->get('shop_phone', ''); ?>" />
			                </td>
                            <td>
                                
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Shop Owner Name' ); ?>
							</th>
			                <td>
			                	<input type="text" name="shop_owner_name" value="<?php echo $this->row->get('shop_owner_name', ''); ?>" size="35" />
			                </td>
                            <td>
                                
                            </td>
						</tr>
					</tbody>
					</table>
					
					
					<?php
					echo $this->sliders->endPanel();
					
					$legend = JText::_( "Images Settings" );
					echo $this->sliders->startPanel( JText::_( $legend ), 'images' );
					?>
					
					<table class="adminlist">
					<tbody>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Display Default Category Image' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'use_default_category_image', 'class="inputbox"', $this->row->get('use_default_category_image', '1') ); ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Default Product Image Height' ); ?>
                            </th>
                            <td>
                                <input type="text" name="product_img_height" value="<?php echo $this->row->get('product_img_height', ''); ?>" />
                            </td>
                        </tr>
						<tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Default Product Image Width' ); ?>
                            </th>
                            <td>
                                <input type="text" name="product_img_width" value="<?php echo $this->row->get('product_img_width', ''); ?>" />
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Recreate Product Thumbnails' ); ?>
                            </th>
                            <td>
                                <a href="index.php?option=com_tienda&view=products&task=recreateThumbs" onClick="return confirm('<?php echo JText::_('Are you sure? Remember to save your new Configuration Values before doing this!'); ?>');"><?php echo JText::_('Click here to recreate the Product Thumbnails'); ?></a>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Default Category Image Height' ); ?>
                            </th>
                            <td>
                                <input type="text" name="category_img_height" value="<?php echo $this->row->get('category_img_height', ''); ?>" />
                            </td>
                        </tr>
						<tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Default Category Image Width' ); ?>
                            </th>
                            <td>
                                <input type="text" name="category_img_width" value="<?php echo $this->row->get('category_img_width', ''); ?>" />
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Recreate Category Thumbnails' ); ?>
                            </th>
                            <td>
                                <a href="index.php?option=com_tienda&view=categories&task=recreateThumbs" onClick="return confirm('<?php echo JText::_('Are you sure? Remember to save your new Configuration Values before doing this!'); ?>');"><?php echo JText::_('Click here to recreate the Category Thumbnails'); ?></a>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Default Manufacturer Image Height' ); ?>
                            </th>
                            <td>
                                <input type="text" name="manufacturer_img_height" value="<?php echo $this->row->get('manufacturer_img_height', ''); ?>" />
                            </td>
                        </tr>
						<tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Default Manufacturer Image Width' ); ?>
                            </th>
                            <td>
                                <input type="text" name="manufacturer_img_width" value="<?php echo $this->row->get('manufacturer_img_width', ''); ?>" />
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Recreate Manufacturer Thumbnails' ); ?>
                            </th>
                            <td>
                                <a href="index.php?option=com_tienda&view=manufacturers&task=recreateThumbs" onClick="return confirm('<?php echo JText::_('Are you sure? Remember to save your new Configuration Values before doing this!'); ?>');"><?php echo JText::_('Click here to recreate the Manufacturer Thumbnails'); ?></a>
                            </td>
                        </tr>
					</tbody>
					</table>
					
					<?php
					echo $this->sliders->endPanel();
					
					$legend = JText::_( "Currency Units and Date Settings" );
					echo $this->sliders->startPanel( JText::_( $legend ), 'currency' );
					?>
					
					<table class="adminlist">
					<tbody>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'SET DATE FORMAT' ); ?>
                            </th>
                            <td>
                                <input name="date_format" value="<?php echo $this->row->get('date_format', '%a, %d %b %Y, %I:%M%p'); ?>" type="text" size="40"/>
                            </td>
                            <td>
                                <?php echo JText::_( "CONFIG SET DATE FORMAT" ); ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'SELECT DEFAULT CURRENCY FOR DB VALUES' ); ?>
                            </th>
                            <td>
                                <?php echo TiendaSelect::currency( $this->row->get('default_currencyid', '1'), 'default_currencyid' ); ?>
                            </td>
                            <td>
                                <?php echo JText::_( "CONFIG DEFAULT CURRENCY" ); ?>
                            </td>
                        </tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Number of Decimal Places' ); ?>
							</th>
			                <td>
			                	<input type="text" name="currency_num_decimals" value="<?php echo $this->row->get('currency_num_decimals', '2'); ?>" />
			                </td>
                            <td>
                                
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Thousands Separator' ); ?>
							</th>
			                <td>
			                	<input type="text" name="currency_thousands" value="<?php echo $this->row->get('currency_thousands', ','); ?>" />
			                </td>
                            <td>
                                
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Decimal Separator' ); ?>
							</th>
			                <td>
			                	<input type="text" name="currency_decimal" value="<?php echo $this->row->get('currency_decimal', '.'); ?>" />
			                </td>
                            <td>
                                
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Prefix' ); ?>
							</th>
			                <td>
			                	<input type="text" name="currency_symbol_pre" value="<?php echo $this->row->get('currency_symbol_pre', '$'); ?>" />
			                </td>
                            <td>
                                
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Suffix' ); ?>
							</th>
			                <td>
			                	<input type="text" name="currency_symbol_post" value="<?php echo $this->row->get('currency_symbol_post', ''); ?>" />
			                </td>
                            <td>
                                
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Dimensions Measure Unit' ); ?>
							</th>
			                <td>
			                	<input type="text" name="dimensions_unit" value="<?php echo $this->row->get('dimensions_unit', ''); ?>" />
			                </td>
                            <td>
                                
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Weight Measure Unit' ); ?>
							</th>
			                <td>
			                	<input type="text" name="weight_unit" value="<?php echo $this->row->get('weight_unit', ''); ?>" />
			                </td>
                            <td>
                                
                            </td>
						</tr>
					</tbody>
					</table>
					<?php
					echo $this->sliders->endPanel();
					
					$legend = JText::_( "Administrator Dashboard Settings" );
					echo $this->sliders->startPanel( JText::_( $legend ), 'dashboard' );
					?>
					
					<table class="adminlist">
					<tbody>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Display Statistics' ); ?>
							</th>
			                <td>
								<?php echo JHTML::_('select.booleanlist', 'display_dashboard_statistics', 'class="inputbox"', $this->row->get('display_dashboard_statistics', '1') ); ?>
			                </td>
                            <td>
                                
                            </td>
						</tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'SELECT ORDER STATES TO REPORT ON' ); ?>
                            </th>
                            <td>
                                <input type="text" name="orderstates_csv" value="<?php echo $this->row->get('orderstates_csv', '2, 3, 5, 17'); ?>" />
                            </td>
                            <td>
                                <?php echo JText::_( "CONFIG ORDER STATES TO REPORT ON" ); ?>
                            </td>
                        </tr>
					</tbody>
					</table>
                    <?php
                    echo $this->sliders->endPanel();
                    
                    $legend = JText::_( "Order and Checkout Settings" );
                    echo $this->sliders->startPanel( JText::_( $legend ), 'orders' );
                    ?>

                    <table class="adminlist">
                    <tbody>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Require Acceptance of Terms on Checkout' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'require_terms', 'class="inputbox"', $this->row->get('require_terms', '0') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Terms and Conditions Article' ); ?>
                            </th>
                            <td style="width: 280px;">
                                <?php echo $this->elementArticle_terms; ?>
                                <?php echo $this->resetArticle_terms; ?>              
                            </td>
                            <td>
                                <?php echo JText::_( 'Article for Terms and Conditions Desc' ); ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Show Separate Line Items for Each Tax Class' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'display_taxclass_lineitems', 'class="inputbox"', $this->row->get('display_taxclass_lineitems', '0') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Show Shipping Tax on Order Invoices and Checkout' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'display_shipping_tax', 'class="inputbox"', $this->row->get('display_shipping_tax', '1') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Initial Order State' ); ?>
                            </th>
                            <td>
                                <?php echo TiendaSelect::orderstate($this->row->get('initial_order_state', '15'), 'initial_order_state'); ?>
                            </td>
                            <td>
                                <?php echo JText::_( 'Initial Order State DESC' ); ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Pending Order State' ); ?>
                            </th>
                            <td>
                                <?php echo TiendaSelect::orderstate($this->row->get('pending_order_state', '1'), 'pending_order_state'); ?>
                            </td>
                            <td>
                                <?php echo JText::_( 'Pending Order State DESC' ); ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Default Shipping Method' ); ?>
                            </th>
                            <td>
                                <?php echo TiendaSelect::shippingtype($this->row->get('defaultShippingMethod', '2'), 'defaultShippingMethod'); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Enable Guest Checkout' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'guest_checkout_enabled', 'class="inputbox"', $this->row->get('guest_checkout_enabled', '1') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Order Number Prefix' ); ?>
                            </th>
                            <td>
                                <input type="text" name="order_number_prefix" value="<?php echo $this->row->get('order_number_prefix', ''); ?>" class="inputbox" size="10" />
                            </td>
                            <td>
                                <?php echo JText::_( 'Order Number Prefix Desc' ); ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Global Handling Cost' ); ?>
                            </th>
                            <td>
                                <input type="text" name="global_handling" value="<?php echo $this->row->get('global_handling', ''); ?>" class="inputbox" size="10" />
                            </td>
                            <td>
                                <?php echo JText::_( 'Global Handling Cost Desc' ); ?>
                            </td>
                        </tr>
                    </tbody>
                    </table>

					<?php
					echo $this->sliders->endPanel();
					
					$legend = JText::_( "Display Settings" );
					echo $this->sliders->startPanel( JText::_( $legend ), 'display' );
					?>
					
					<table class="adminlist">
					<tbody>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Display Out of Stock Products' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'display_out_of_stock', 'class="inputbox"', $this->row->get('display_out_of_stock', '1') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Display Root Category in Joomla Breadcrumb' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'include_root_pathway', 'class="inputbox"', $this->row->get('include_root_pathway', '0') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Display Tienda Breadcrumb' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'display_tienda_pathway', 'class="inputbox"', $this->row->get('display_tienda_pathway', '1') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Display Product Prices with Tax' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'display_prices_with_tax', 'class="inputbox"', $this->row->get('display_prices_with_tax', '0') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Default Tax Geozone' ); ?>
                            </th>
                            <td>
                                <?php echo TiendaSelect::geozone( $this->row->get('default_tax_geozone'), 'default_tax_geozone' ); ?>
                            </td>
                            <td>
                                <?php echo JText::_( 'Default Tax Geozone Desc' ); ?>            
                            </td>
                        </tr>                        
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Display Product Prices with Link to Shipping Costs Article' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'display_prices_with_shipping', 'class="inputbox"', $this->row->get('display_prices_with_shipping', '0') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Shipping Costs Article' ); ?>
                            </th>
                            <td style="width: 280px;">
                                <?php echo $this->elementArticle_shipping; ?>
                                <?php echo $this->resetArticle_shipping; ?>              
                            </td>
                            <td>
                                <?php echo JText::_( 'Article for Shipping Costs Desc' ); ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Add to Cart Action' ); ?>
                            </th>
                            <td>
                                <?php echo TiendaSelect::addtocartaction( $this->row->get('addtocartaction', 'lightbox'), 'addtocartaction' ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Select Cart Button Type' ); ?>
                            </th>
                            <td>
                                <?php echo TiendaSelect::cartbutton( $this->row->get('cartbutton', 'image'), 'cartbutton' ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Width of UI Lightboxes' ); ?>
                            </th>
                            <td>
                                <input type="text" name="lightbox_width" value="<?php echo $this->row->get('lightbox_width', ''); ?>" class="inputbox" size="10" />
                            </td>
                            <td>
                                
                            </td>
                        </tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Show Dioscouri Link in Footer' ); ?>
							</th>
			                <td>
								<?php echo JHTML::_('select.booleanlist', 'show_linkback', 'class="inputbox"', $this->row->get('show_linkback', '1') ); ?>
			                </td>
                            <td>
                                
                            </td>
						</tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Your Dioscouri Affiliate ID' ); ?>
                            </th>
                            <td>
                                <input type="text" name="amigosid" value="<?php echo $this->row->get('amigosid', ''); ?>" class="inputbox" />
                            </td>
                            <td>
                                <a href='http://www.dioscouri.com/index.php?option=com_amigos' target='_blank'>
                                <?php echo JText::_( "No AmigosID" ); ?>
                                </a>                                
                            </td>
                        </tr>
					</tbody>
					</table>
					<?php
					echo $this->sliders->endPanel();
					
					$legend = JText::_( "Administrator ToolTips" );
					echo $this->sliders->startPanel( JText::_( $legend ), 'defaults' );
					?>
					
					<table class="adminlist">
					<tbody>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Hide Dashboard Note' ); ?>
							</th>
							<td>
		                        <?php echo JHTML::_('select.booleanlist', 'page_tooltip_dashboard_disabled', 'class="inputbox"', $this->row->get('page_tooltip_dashboard_disabled', '0') ); ?>
							</td>
                            <td>
                                
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Hide Configuration Note' ); ?>
							</th>
							<td>
		                        <?php echo JHTML::_('select.booleanlist', 'page_tooltip_config_disabled', 'class="inputbox"', $this->row->get('page_tooltip_config_disabled', '0') ); ?>
							</td>
                            <td>
                                
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Hide Tools Note' ); ?>
							</th>
							<td>
		                        <?php echo JHTML::_('select.booleanlist', 'page_tooltip_tools_disabled', 'class="inputbox"', $this->row->get('page_tooltip_tools_disabled', '0') ); ?>
							</td>
                            <td>
                                
                            </td>
						</tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Hide User Dashboard Note' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'page_tooltip_users_view_disabled', 'class="inputbox"', $this->row->get('page_tooltip_users_view_disabled', '0') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
					</tbody>
					</table>
					<?php
						echo $this->sliders->endPanel();				
						// if there are plugins, display them accordingly
		                if ($this->items_sliders) 
		                {               	
	                		$tab=1;
							$pane=2;
							for ($i=0, $count=count($this->items_sliders); $i < $count; $i++) {
								if ($pane == 1) {
									// echo $this->sliders->startPane( "pane_$pane" );
								}
								$item = $this->items_sliders[$i];
								echo $this->sliders->startPanel( JText::_( $item->element ), $item->element );
								
								// load the plugin
									$import = JPluginHelper::importPlugin( strtolower( 'Tienda' ), $item->element );
								// fire plugin
									$dispatcher = JDispatcher::getInstance();
									$dispatcher->trigger( 'onDisplayConfigFormSliders', array( $item, $this->row ) );
									
								echo $this->sliders->endPanel();
								if ($i == $count-1) {
									// echo $this->sliders->endPane();
								}
							}
						}						
						echo $this->sliders->endPane();					
					?>
					</td>
					<td style="vertical-align: top; max-width: 30%;">
						
						<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>
						
						<div id='onDisplayRightColumn_wrapper'>
							<?php
								$dispatcher = JDispatcher::getInstance();
								$dispatcher->trigger( 'onDisplayConfigFormRightColumn', array() );
							?>
						</div>

					</td>
                </tr>
            </tbody>
		</table>

		<div id='onAfterDisplay_wrapper'>
			<?php 
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger( 'onAfterDisplayConfigForm', array() );
			?>
		</div>
        
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
	
	<?php echo $this->form['validate']; ?>
</form>
