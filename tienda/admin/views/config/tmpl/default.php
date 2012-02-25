<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; ?>

<?php JFilterOutput::objectHTMLSafe($row); ?>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

        <?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>

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
								<?php echo JText::_( 'Auto Update Exchange Rates' ); ?>
							</th>
			                <td>
				                <?php echo JHTML::_('select.booleanlist', 'currency_exchange_autoupdate', 'class="inputbox"', $this->row->get('currency_exchange_autoupdate', '1') ); ?>
			                </td>
                            <td>
                                <?php echo JText::_( 'Auto Update Exchange Rates Desc' ); ?>
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
					
                    $legend = JText::_( "Order and Checkout Settings" );
                    echo $this->sliders->startPanel( JText::_( $legend ), 'orders' );
                    ?>

                    <table class="adminlist">
                    <tbody>
                    	<tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'One Page Checkout' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'one_page_checkout', 'class="inputbox"', $this->row->get('one_page_checkout', '0') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'One Page Checkout Layout' ); ?>
                            </th>
                            <td>
                                <?php
                                echo TiendaSelect::opclayouts($this->row->get('one_page_checkout_layout', 'onepagecheckout'), 'one_page_checkout_layout');
                                ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                    	<tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'ENABLE TOOLTIPS ONE PAGE CHECKOUT' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'one_page_checkout_tooltips_enabled', 'class="inputbox"', $this->row->get('one_page_checkout_tooltips_enabled', '0') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Force SSL on Checkout' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'force_ssl_checkout', 'class="inputbox"', $this->row->get('force_ssl_checkout', '0') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
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
                                <?php echo JText::_( 'No Zones Countries' ); ?>
                            </th>
                            <td style="width: 280px;">
                            	<input type="text" name="ignored_countries" value="<?php echo $this->row->get('ignored_countries', ''); ?>" class="inputbox" />                              
                            </td>
                            <td>
                                <?php echo JText::_( 'Countries that will be ignored when validating the zones during checkout. Please input the CSV of the country id. The default is 83, 188, 190 which are the ids of Gibraltar, Singapore, and Slovenia respectively.' ); ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Show Taxes' ); ?>
                            </th>
                            <td>
                                <?php echo TiendaSelect::taxdisplaycheckout($this->row->get('show_tax_checkout', '3'), 'show_tax_checkout'); ?>
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
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Article to Display After Successful Checkout' ); ?>
                            </th>
                            <td style="width: 280px;">
                                <?php echo $this->elementArticleModel->_fetchElement( 'article_checkout', $this->row->get('article_checkout') ); ?>
                                <?php echo $this->elementArticleModel->_clearElement( 'article_checkout', '0' ); ?>              
                            </td>
                            <td>
                                <?php echo JText::_( 'Article to Display After Successful Checkout Desc' ); ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Article to Display After Unsuccessful Checkout' ); ?>
                            </th>
                            <td style="width: 280px;">
                                <?php echo $this->elementArticleModel->_fetchElement( 'article_default_payment_failure', $this->row->get('article_default_payment_failure') ); ?>
                                <?php echo $this->elementArticleModel->_clearElement( 'article_default_payment_failure', '0' ); ?>              
                            </td>
                            <td>
                                <?php echo JText::_( 'Article to Display After Unsuccessful Checkout Desc' ); ?>
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
                                <?php echo JText::_( 'Display Front End Submenu' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'show_submenu_fe', 'class="inputbox"', $this->row->get('show_submenu_fe', '1') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
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
                                <?php echo JText::_( 'Display Product Sort By' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'display_sort_by', 'class="inputbox"', $this->row->get('display_sort_by', '1') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                         <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Product Sortings' ); ?>
                            </th>
                            <td>
                                <input type="text" name="display_sortings" value="<?php echo $this->row->get('display_sortings', 'Name|product_name,Price|price,Rating|product_rating'); ?>" class="inputbox" size="45" />
                            </td>
                            <td>
                                <?php echo JText::_('This will be the added to the "sort by" select. The format is "title|columnname" and to add another sorting, prepend "," to it. You can sort the price, product_quantity, and all the #__tienda_products columns.')?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Display Product Quantity' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'display_product_quantity', 'class="inputbox"', $this->row->get('display_product_quantity', '1') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Display Related Items' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'display_relateditems', 'class="inputbox"', $this->row->get('display_relateditems', '1') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Display Facebook Like Button' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'display_facebook_like', 'class="inputbox"', $this->row->get('display_facebook_like', '1') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                         <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Display Twitter Button' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'display_tweet', 'class="inputbox"', $this->row->get('display_tweet', '1') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                         <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Default Twitter Message' ); ?>
                            </th>
                            <td>
                                <input type="text" name="display_tweet_message" value="<?php echo $this->row->get('display_tweet_message', 'Check this out!'); ?>" class="inputbox" size="35" />
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                         <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Display Google +1 Buttom' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'display_google_plus1', 'class="inputbox"', $this->row->get('display_google_plus1', '1') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                         <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Display Google +1 Buttom Size' ); ?>
                            </th>
                            <td>
                                <?php 
                                	$google_sizes = array();
													        $google_sizes[] = JHTML::_('select.option',  'small', JText::_( "GOOGLE Small" ) );
													        $google_sizes[] = JHTML::_('select.option',  'medium', JText::_( "GOOGLE Medium" ) );
													        $google_sizes[] = JHTML::_('select.option',  '', JText::_( "GOOGLE Standard" ) );
													        $google_sizes[] = JHTML::_('select.option',  'tall', JText::_( "GOOGLE Tall" ) );
                                	echo JHTML::_( 'select.genericlist', $google_sizes, 'display_google_plus1_size', array('class' => 'inputbox', 'size' => '1'), 'value', 'text', $this->row->get('display_google_plus1_size', 'medium') );
                                ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                         <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Use URI For Social Bookmark Integration' ); ?>
                            </th>
                            <td>
                                <?php 
                                	$social_uri_types = array();
													        $social_uri_types[] = JHTML::_('select.option',  0, JText::_( "Long URI" ) );
													        $social_uri_types[] = JHTML::_('select.option',  1, JText::_( "Bit.ly" ) );
                                	echo JHTML::_( 'select.genericlist', $social_uri_types, 'display_bookmark_uri', array('class' => 'inputbox', 'size' => '1'), 'value', 'text', $this->row->get('display_bookmark_uri', 0) );
                                ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                         <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'BitLy Login' ); ?>
                            </th>
                            <td>
                                <input type="text" name="bitly_login" value="<?php echo $this->row->get('bitly_login', ''); ?>" class="inputbox" size="35" />
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                         <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'BitLy Key' ); ?>
                            </th>
                            <td>
                                <input type="text" name="bitly_key" value="<?php echo $this->row->get('bitly_key', ''); ?>" class="inputbox" size="35" />
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Display "Ask a question about this product"' ); ?>
                            </th>
                           	<td>
                                <?php echo JHTML::_('select.booleanlist', 'ask_question_enable', 'class="inputbox"', $this->row->get('ask_question_enable', '1') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Enable Captcha on "Ask a question about this product"' ); ?>
                            </th>
                           	<td>
                                <?php echo JHTML::_('select.booleanlist', 'ask_question_showcaptcha', 'class="inputbox"', $this->row->get('ask_question_showcaptcha', '1') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( '"Ask a question about this product" in modal' ); ?>
                            </th>
                           	<td>
                                <?php echo JHTML::_('select.booleanlist', 'ask_question_modal', 'class="inputbox"', $this->row->get('ask_question_modal', '1') ); ?>
                            </td>
                            <td>
                               <?php echo JText::_('Show the "Ask a question about this product" form in modal.');?> 
                            </td>
                        </tr>                       
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Display Product Prices with Tax' ); ?>
                            </th>
                            <td>
                                <?php echo TiendaSelect::displaywithtax( $this->row->get('display_prices_with_tax', '0'), 'display_prices_with_tax' ); ?>
                            </td>
                            <td>
                            </td>
                        </tr>
						 <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Display Working Image Product' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'dispay_working_image_product', 'class="inputbox"', $this->row->get('dispay_working_image_product', '1') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Number of Subcategories per Line' ); ?>
                            </th>
                            <td>
                                <input type="text" name="subcategories_per_line" id="subcategories_per_line" value="<?php echo $this->row->get('subcategories_per_line', 5); ?>" />
                            </td>
                            <td>
                                <?php echo JText::_( 'Number of Subcategories per Line Desc' ); ?>            
                            </td>
                        </tr>      
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Default Tax Geozone' ); ?>
                            </th>
                            <td>
                                <?php echo TiendaSelect::geozone( $this->row->get('default_tax_geozone'), 'default_tax_geozone', 1 ); ?>
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
                                <?php echo JText::_( 'Display Add to Cart Button in Category Listings' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'display_category_cartbuttons', 'class="inputbox"', $this->row->get('display_category_cartbuttons', '1') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <!--  Add Display Add to Cart Button in Product -->
						<tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Display Add to Cart Button in Product' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'display_product_cartbuttons', 'class="inputbox"', $this->row->get('display_product_cartbuttons', '1') ); ?>
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
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Process Content Plugins on Product Short Description' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'content_plugins_product_desc', 'class="inputbox"', $this->row->get('content_plugins_product_desc', '0') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        
						
					</tbody>
					</table>
					<?php
					echo $this->sliders->endPanel();
                    
                    $legend = JText::_( "Subscription Settings" );
                    echo $this->sliders->startPanel( JText::_( $legend ), 'subscriptions' );
                    ?>
                    
                    <table class="adminlist">
                    <tbody>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Expiration Notice' ); ?>
                            </th>
                            <td>
                                <input name="subscriptions_expiring_notice_days" value="<?php echo $this->row->get('subscriptions_expiring_notice_days', '14'); ?>" type="text" />
                            </td>
                            <td>
                                <?php echo JText::_( "Expiration Notice DESC" ); ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Display Subscription Number' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'display_subnum', 'class="inputbox"', $this->row->get('display_subnum', '0') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Display Subscription Number Digits' ); ?>
                            </th>
                            <td>
                                <input type="text" name="sub_num_digits" value="<?php echo $this->row->get('sub_num_digits', ''); ?>" class="inputbox" size="10" />
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Default Subscription Number' ); ?>
                            </th>
                            <td>
                                <input type="text" name="default_sub_num" value="<?php echo $this->row->get('default_sub_num', '1'); ?>" class="inputbox" size="10" />
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
					
                    
                    $legend = JText::_( "Coupon Settings" );
                    echo $this->sliders->startPanel( JText::_( $legend ), 'coupons' );
                    ?>
                    
                    <table class="adminlist">
                    <tbody>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Enable Coupons' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'coupons_enabled', 'class="inputbox"', $this->row->get('coupons_enabled', '1') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Enable Multiple User Submitted Coupons Per Order' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'multiple_usercoupons_enabled', 'class="inputbox"', $this->row->get('multiple_usercoupons_enabled', '0') ); ?>
                            </td>
                            <td>
                                
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
					
					$legend = JText::_( "Product Reviews" );
					echo $this->sliders->startPanel( JText::_( $legend ), 'defaults' );
					?>
					
					<table class="adminlist">
					<tbody>
					<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Enable Product Reviews' ); ?>
							</th>
			                <td>
			                	 <?php echo JHTML::_('select.booleanlist', 'product_review_enable', 'class="inputbox"', $this->row->get('product_review_enable', '1') ); ?>
			                </td>
                            <td>
                                
                            </td>
					</tr>
                    <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Automatically Approve Reviews' ); ?>
                            </th>
                            <td>
                                 <?php echo JHTML::_('select.booleanlist', 'product_reviews_autoapprove', 'class="inputbox"', $this->row->get('product_reviews_autoapprove', '0') ); ?>
                            </td>
                            <td>
                                
                            </td>
                    </tr>
					<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Require Login to leave review' ); ?>
							</th>
			                <td>
			                	 <?php echo JHTML::_('select.booleanlist', 'login_review_enable', 'class="inputbox"', $this->row->get('login_review_enable', '1') ); ?>
			                </td>
                            <td>
                                
                            </td>
                    </tr>
					<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Require Purchase to leave review' ); ?>
							</th>
			                <td>
			                	 <?php echo JHTML::_('select.booleanlist', 'purchase_leave_review_enable', 'class="inputbox"', $this->row->get('purchase_leave_review_enable', '1') ); ?>
			                </td>
                            <td>
                                
                            </td>
					</tr>
					<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Use captcha' ); ?>
							</th>
			                <td>
			                	 <?php echo JHTML::_('select.booleanlist', 'use_captcha', 'class="inputbox"', $this->row->get('use_captcha', '1') ); ?>
			                </td>
                            <td>
                                
                            </td>
					</tr>
					<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Enable Review Helpfulness Voting' ); ?>
							</th>
			                <td>
			                	 <?php echo JHTML::_('select.booleanlist', 'review_helpfulness_enable', 'class="inputbox"', $this->row->get('review_helpfulness_enable', '1') ); ?>
			                </td>
                            <td>
                                
                            </td>
					</tr>
					<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Enable Share this link' ); ?>
							</th>
			                <td>
			                	 <?php echo JHTML::_('select.booleanlist', 'share_review_enable', 'class="inputbox"', $this->row->get('share_review_enable', '1') ); ?>
			                </td>
                            <td>
                                
                            </td>
					</tr>
					</tbody>
					</table>
					<?php
						echo $this->sliders->endPanel();	

						
						$legend = JText::_( "Advanced Settings" );
	                    echo $this->sliders->startPanel( JText::_( $legend ), 'advanced' );
	                    ?>
                    
					<table class="adminlist">
					<tbody>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Enable Automatic Table Reordering' ); ?>
							</th>
							<td style="width: 150px;">
		                        <?php echo JHTML::_('select.booleanlist', 'enable_reorder_table', 'class="inputbox"', $this->row->get('enable_reorder_table', '1') ); ?>
							</td>
                            <td>
                                <?php echo JText::_( 'ENABLE AUTOMATIC TABLE REORDERING DESC' ); ?>
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Default User Group' ); ?>
							</th>
							<td style="width: 150px;">
		                        <?php echo TiendaSelect::groups($this->row->get('default_user_group', '1'), 'default_user_group'); ?>
							</td>
                            <td>
                                &nbsp;
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Load Custom Language File?' ); ?>
							</th>
							<td style="width: 150px;">
		                        <?php echo JHTML::_('select.booleanlist', 'custom_language_file', 'class="inputbox"', $this->row->get('custom_language_file', '0') ); ?>
							</td>
                            <td>
                                <?php echo JText::_( 'TIENDA CUSTOM LANGUAGE FILE DESC'); ?>
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Use SHA1 to store the images?' ); ?>
							</th>
							<td style="width: 150px;">
		                       <?php echo JHTML::_('select.booleanlist', 'sha1_images', 'class="inputbox"', $this->row->get('sha1_images', '0') ); ?>
							</td>
                            <td>
                                <?php echo JText::_( 'TIENDA SHA1 IMAGE DESC'); ?>
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Max FileSize for Images / Image Archives' ); ?>
							</th>
							<td style="width: 150px;">
		                        <input type="text" name="files_maxsize" value="<?php echo $this->row->get('files_maxsize', '3000'); ?>" /> Kb
							</td>
                            <td>
                              &nbsp;
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'CHOOSE MULTI UPLOAD SCRIPT' ); ?>
							</th>
							<td style="width: 150px;">
		                        <?php echo TiendaSelect::multipleuploadscript($this->row->get('multiupload_script', '0'), 'multiupload_script'); ?>
							</td>
                            <td>
                                <?php echo JText::_( 'CHOOSE MULTI UPLOAD SCRIPT DESC' ); ?>
                            </td>
						</tr>
						<tr>
            	<th style="width: 25%;">
								<?php echo JText::_( 'COM_TIENDA_CONFIG_PASSWORD_LENGTH' ); ?>
							</th>
							<td style="width: 150px;">
		            <input type="text" name="password_min_length" value="<?php echo $this->row->get('password_min_length', '5'); ?>" />
							</td>
              <td>
              </td>
						</tr>
						<tr>
            	<th style="width: 25%;">
								<?php echo JText::_( 'COM_TIENDA_CONFIG_PASSWORD_REQUIRE_NUMBER' ); ?>
							</th>
							<td style="width: 150px;">
                <?php echo JHTML::_('select.booleanlist', 'password_req_num', 'class="inputbox"', $this->row->get('password_req_num', '1') ); ?>
							</td>
              <td>
              </td>
						</tr>
						<tr>
            	<th style="width: 25%;">
								<?php echo JText::_( 'COM_TIENDA_CONFIG_PASSWORD_REQUIRE_ALPHA' ); ?>
							</th>
							<td style="width: 150px;">
                <?php echo JHTML::_('select.booleanlist', 'password_req_alpha', 'class="inputbox"', $this->row->get('password_req_alpha', '1') ); ?>
							</td>
              <td>
              </td>
						</tr>
						<tr>
            	<th style="width: 25%;">
								<?php echo JText::_( 'COM_TIENDA_CONFIG_PASSWORD_REQUIRE_SPECIAL' ); ?>
							</th>
							<td style="width: 150px;">
                <?php echo JHTML::_('select.booleanlist', 'password_req_spec', 'class="inputbox"', $this->row->get('password_req_spec', '1') ); ?>
							</td>
              <td>
								<?php echo JText::_( 'COM_TIENDA_CONFIG_PASSWORD_REQUIRE_SPECIAL_DESC' ); ?>
              </td>
						</tr>
						<tr>
            	<th style="width: 25%;">
								<?php echo JText::_( 'COM_TIENDA_CONFIG_PASSWORD_VALDATE_PHP' ); ?>
							</th>
							<td style="width: 150px;">
                <?php echo JHTML::_('select.booleanlist', 'password_php_validate', 'class="inputbox"', $this->row->get('password_php_validate', '1') ); ?>
							</td>
              <td>
              </td>
						</tr>
					</tbody>
					</table>
					<?php
						echo $this->sliders->endPanel();				

						$legend = JText::_( "Email Settings" );
	                    echo $this->sliders->startPanel( JText::_( $legend ), 'email' );
	                    ?>
                    
					<table class="adminlist">
					<tbody>
                         <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Shop Email Address' ); ?><br />
                            </th>
                            <td>
                                <input type="text" name="shop_email" value="<?php echo $this->row->get('shop_email', ''); ?>" class="inputbox" size="35" />
                            </td>
                            <td>
                                <?php echo JText::_( 'Shop Email Address Desc' ); ?>                                
                            </td>
                        </tr>
                         <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Shop Email From Name' ); ?><br />
                            </th>
                            <td>
                                <input type="text" name="shop_email_from_name" value="<?php echo $this->row->get('shop_email_from_name', ''); ?>" class="inputbox" size="35" />
                            </td>
                            <td>
								<?php echo JText::_( 'Shop Email From Name Desc' ); ?>                                
                            </td>
                        </tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Disable Guest Signup Email' ); ?>
							</th>
							<td style="width: 150px;">
		                        <?php echo JHTML::_('select.booleanlist', 'disable_guest_signup_email', 'class="inputbox"', $this->row->get('disable_guest_signup_email', '0') ); ?>
							</td>
                            <td>
                                <?php echo JText::_( 'DISABLE GUEST SIGNUP EMAIL DESC' ); ?>
                            </td>
						</tr>
                        <tr>
							<th style="width: 25%;">
								<?php echo JText::_( 'Obfuscate Guest Email' ); ?>
							</th>
							<td style="width: 150px;">
		                        <?php echo JHTML::_('select.booleanlist', 'obfuscate_guest_email', 'class="inputbox"', $this->row->get('obfuscate_guest_email', '0') ); ?>
							</td>
                            <td>
                                <?php echo JText::_( 'OBFUSCATE_GUEST_EMAIL_DESC' ); ?>
                            </td>
						</tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Enable Order Status Update Email to User When Order Payment is Received' ); ?>
                            </th>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'autonotify_onSetOrderPaymentReceived', 'class="inputbox"', $this->row->get('autonotify_onSetOrderPaymentReceived', '0') ); ?>
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 25%;">
                                <?php echo JText::_( 'Additional Email Addresses to Receive Order Notifications' ); ?><br />
                            </th>
                            <td>
                                <textarea name="order_emails" style="width: 250px;" rows="10"><?php echo $this->row->get('order_emails', ''); ?></textarea>
                            </td>
                            <td>
                                <?php echo JText::_( "Separate emails with a comma or put them on each on a new line" ); ?>
                            </td>
                        </tr>
					</tbody>
					</table>
					<?php
						echo $this->sliders->endPanel();				

						$legend = JText::_( "Address Fields Management" );
	                    echo $this->sliders->startPanel( JText::_( $legend ), 'adrress_fields' );
	                 ?>
	                <table class="adminlist">
					<tbody>					
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Show Address Name Field' ); ?><br />
                <small><?php echo JText::_( 'COM_TIENDA_CONFIG_SHOW_ADDRESS_TITLE_NOTE' );?></small>
							</th>
							<td style="width: 150px;">
		                        <?php echo TiendaSelect::addressShowList( $this->row->get('show_field_address_name', '3'), 'show_field_address_name');?>
							</td>
                            <th>
                               	<?php echo JText::_( 'Validate Address Name Field' ); ?>
                            </th>
                            <td>
                               <?php echo TiendaSelect::addressShowList( $this->row->get('validate_field_address_name', '3'), 'validate_field_address_name');?>
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Show Title Field' ); ?>
							</th>
							<td style="width: 150px;">
		                        <?php echo TiendaSelect::addressShowList( $this->row->get('show_field_title', '3'), 'show_field_title');?>
							</td>
                            <th>
                               	<?php echo JText::_( 'Validate Title Field' ); ?>
                            </th>
                            <td>
                               <?php echo TiendaSelect::addressShowList( $this->row->get('validate_field_title', '3'), 'validate_field_title');?>
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Show First Name Field' ); ?>
							</th>
							<td style="width: 150px;">
		                        <?php echo TiendaSelect::addressShowList( $this->row->get('show_field_name', '3'), 'show_field_name');?>
							</td>
                            <th>
                               	<?php echo JText::_( 'Validate First Name Field' ); ?>
                            </th>
                            <td>
                               <?php echo TiendaSelect::addressShowList( $this->row->get('validate_field_name', '3'), 'validate_field_name');?>
                            </td>
						</tr>
                        <tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Show Middle Name Field' ); ?>
							</th>
							<td style="width: 150px;">
		                        <?php echo TiendaSelect::addressShowList( $this->row->get('show_field_middle', '3'), 'show_field_middle');?>
							</td>
                            <th>
                               	<?php echo JText::_( 'Validate Middle Name Field' ); ?>
                            </th>
                            <td>
                               <?php echo TiendaSelect::addressShowList( $this->row->get('validate_field_middle', '0'), 'validate_field_middle');?>
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Show Last Name Field' ); ?>
							</th>
							<td style="width: 150px;">
		                        <?php echo TiendaSelect::addressShowList( $this->row->get('show_field_last', '3'), 'show_field_last');?>
							</td>
                            <th>
                               	<?php echo JText::_( 'Validate Last Name Field' ); ?>
                            </th>
                            <td>
                               <?php echo TiendaSelect::addressShowList( $this->row->get('validate_field_last', '3'), 'validate_field_last');?>
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Show Company Field' ); ?>
							</th>
							<td style="width: 150px;">
		                        <?php echo TiendaSelect::addressShowList( $this->row->get('show_field_company', '3'), 'show_field_company');?>
							</td>
                            <th>
                               	<?php echo JText::_( 'Validate Company Field' ); ?>
                            </th>
                            <td>
                               <?php echo TiendaSelect::addressShowList( $this->row->get('validate_field_company', '0'), 'validate_field_company');?>
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Show Company Tax Number Field' ); ?>
							</th>
							<td style="width: 150px;">
		                        <?php echo TiendaSelect::addressShowList( $this->row->get('show_field_tax_number', '3'), 'show_field_tax_number');?>
							</td>
                           <th>
                               	<?php echo JText::_( 'Validate Company Tax Number Field' ); ?>
                            </th>
                            <td>
                               <?php echo TiendaSelect::addressShowList( $this->row->get('validate_field_tax_number', '3'), 'validate_field_tax_number');?>
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Show Address 1 Field' ); ?>
							</th>
							<td style="width: 150px;">
		                        <?php echo TiendaSelect::addressShowList( $this->row->get('show_field_address1', '3'), 'show_field_address1');?>
							</td>
                            <th>
                               	<?php echo JText::_( 'Validate Address 1 Field' ); ?>
                            </th>
                            <td>
                               <?php echo TiendaSelect::addressShowList( $this->row->get('validate_field_address1', '3'), 'validate_field_address1');?>
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Show Address 2 Field' ); ?>
							</th>
							<td style="width: 150px;">
		                        <?php echo TiendaSelect::addressShowList( $this->row->get('show_field_address2', '3'), 'show_field_address2');?>
							</td>
                            <th>
                               	<?php echo JText::_( 'Validate Address 2 Field' ); ?>
                            </th>
                            <td>
                               <?php echo TiendaSelect::addressShowList( $this->row->get('validate_field_address2', '0'), 'validate_field_address2');?>
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Show City Field' ); ?>
							</th>
							<td style="width: 150px;">
		                        <?php echo TiendaSelect::addressShowList( $this->row->get('show_field_city', '3'), 'show_field_city');?>
							</td>
                            <th>
                               	<?php echo JText::_( 'Validate City Field' ); ?>
                            </th>
                            <td>
                               <?php echo TiendaSelect::addressShowList( $this->row->get('validate_field_city', '3'), 'validate_field_city');?>
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Show Country Field' ); ?>
							</th>
							<td style="width: 150px;">
		                        <?php echo TiendaSelect::addressShowList( $this->row->get('show_field_country', '3'), 'show_field_country');?>
							</td>
                            <th>
                               	<?php echo JText::_( 'Validate Country Field' ); ?>
                            </th>
                            <td>
                               <?php echo TiendaSelect::addressShowList( $this->row->get('validate_field_country', '3'), 'validate_field_country');?>
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Show Zone Field' ); ?>
							</th>
							<td style="width: 150px;">
		                        <?php echo TiendaSelect::addressShowList( $this->row->get('show_field_zone', '3'), 'show_field_zone');?>
							</td>
                            <th>
                               	<?php echo JText::_( 'Validate Zone Field' ); ?>
                            </th>
                            <td>
                               <?php echo TiendaSelect::addressShowList( $this->row->get('validate_field_zone', '3'), 'validate_field_zone');?>
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Show Postal Code Field' ); ?>
							</th>
							<td style="width: 150px;">
		                        <?php echo TiendaSelect::addressShowList( $this->row->get('show_field_zip', '3'), 'show_field_zip');?>
							</td>
                           <th>
                               	<?php echo JText::_( 'Validate Postal Code Field' ); ?>
                            </th>
                            <td>
                               <?php echo TiendaSelect::addressShowList( $this->row->get('validate_field_zip', '3'), 'validate_field_zip');?>
                            </td>
						</tr>
						<tr>
			            	<th style="width: 25%;">
								<?php echo JText::_( 'Show Phone Field' ); ?>
							</th>
							<td style="width: 150px;">
		                        <?php echo TiendaSelect::addressShowList( $this->row->get('show_field_phone', '3'), 'show_field_phone');?>
							</td>
	                            <th>
                               	<?php echo JText::_( 'Validate Phone Field' ); ?>
                            </th>
                            <td>
                               <?php echo TiendaSelect::addressShowList( $this->row->get('validate_field_phone', '0'), 'validate_field_phone');?>
                            </td>
						</tr>
					</tbody>
					</table>
					<?php
						echo $this->sliders->endPanel();	
	     				echo $this->sliders->startPanel( JText::_( "Product Compare Settings" ), 'product_compare' );
	    			?>
					<table class="adminlist">
					<tbody>		
						<tr>
			   				<th style="width: 25%;">
								<?php echo JText::_( 'Enable Product Compare' ); ?>
							</th>
							<td style="width: 150px;">
		       					<?php echo JHTML::_('select.booleanlist', 'enable_product_compare', 'class="inputbox"', $this->row->get('enable_product_compare', '1') ); ?>
							</td>
							<td>								
							</td>
						</tr>			
						<tr>
			   				<th style="width: 25%;">
								<?php echo JText::_( 'Product Compared Limit' ); ?>
							</th>
							<td style="width: 150px;">
												<input type="text" name="compared_products" value="<?php echo $this->row->get('compared_products', ''); ?>" />
							</td>
							<td>
								<?php echo JText::_('Number of products that can be compared at once.');?>
							</td>
						</tr>
						<tr>
				   			<th style="width: 25%;">
								<?php echo JText::_( 'Show Add To Cart' ); ?>
							</th>
							<td style="width: 150px;">
			      				<?php echo JHTML::_('select.booleanlist', 'show_addtocart_productcompare', 'class="inputbox"', $this->row->get('show_addtocart_productcompare', '1') ); ?>
							</td>
							<td>								
							</td>
						</tr>
						<tr>
					   		<th style="width: 25%;">
								<?php echo JText::_( 'Show Average Customer Rating' ); ?>
							</th>
							<td style="width: 150px;">
			      			<?php echo JHTML::_('select.booleanlist', 'show_rating_productcompare', 'class="inputbox"', $this->row->get('show_rating_productcompare', '1') ); ?>
							</td>
							<td>								
							</td>
						</tr>
						<tr>
			   				<th style="width: 25%;">
								<?php echo JText::_( 'Show Manufacturer' ); ?>
							</th>
							<td style="width: 150px;">
		       					<?php echo JHTML::_('select.booleanlist', 'show_manufacturer_productcompare', 'class="inputbox"', $this->row->get('show_manufacturer_productcompare', '1') ); ?>
							</td>
							<td>								
							</td>
						</tr>	
						<tr>
			   				<th style="width: 25%;">
								<?php echo JText::_( 'Show Product Model' ); ?>
							</th>
							<td style="width: 150px;">
		       					<?php echo JHTML::_('select.booleanlist', 'show_model_productcompare', 'class="inputbox"', $this->row->get('show_model_productcompare', '1') ); ?>
							</td>
							<td>								
							</td>
						</tr>	
						<tr>
			   				<th style="width: 25%;">
								<?php echo JText::_( 'Show Product SKU' ); ?>
							</th>
							<td style="width: 150px;">
		       					<?php echo JHTML::_('select.booleanlist', 'show_sku_productcompare', 'class="inputbox"', $this->row->get('show_sku_productcompare', '1') ); ?>
							</td>
							<td>								
							</td>
						</tr>			
					</tbody>
					</table>
					<?php
            echo $this->sliders->endPanel();

            $legend = JText::_( "LOW STOCK NOTIFY SETTINGS" );
            echo $this->sliders->startPanel( JText::_( $legend ), 'low_stock_notify' );
            ?>
            
            <table class="adminlist">
            <tbody>
                <tr>
                    <th style="width: 25%;">
                        <?php echo JText::_( 'LOW STOCK NOTIFY' ); ?>
                    </th>
                    <td>
                        <?php echo JHTML::_('select.booleanlist', 'low_stock_notify', 'class="inputbox"', $this->row->get('low_stock_notify', '0') ); ?>
                    </td>
                    <td>
                        
                    </td>
                </tr>
                <tr>
                    <th style="width: 25%;">
                        <?php echo JText::_( 'LOW STOCK NOTIFY VALUE' ); ?>
                    </th>
                    <td>
                        <input ="text" name="low_stock_notify_value" value="<?php echo $this->row->get('low_stock_notify_value', '0'); ?>" />
                    </td>
                    <td>
                        <?php echo JText::_( "LOW STOCK NOTIFY VALUE DESC" ); ?>
                    </td>
                </tr>
            </tbody>
            </table>					
					<?php
							echo $this->sliders->endPanel();	
	     				echo $this->sliders->startPanel( JText::_( "EAV Editor Settings" ), 'eav_editor_settings' );
	    			?>
					<table class="adminlist">
					<tbody>		
						<tr>
			   				<th style="width: 25%;">
								<?php echo JText::_( 'Textarea Rows' ); ?>
							</th>
							<td style="width: 150px;">
		       					<input type="text" name="eav_textarea_rows" value="<?php echo $this->row->get('eav_textarea_rows', '20'); ?>" />
							</td>
							<td>								
							</td>
						</tr>
						<tr>
			   				<th style="width: 25%;">
								<?php echo JText::_( 'Textarea Columns' ); ?>
							</th>
							<td style="width: 150px;">
		       					<input type="text" name="eav_textarea_columns" value="<?php echo $this->row->get('eav_textarea_columns', '50'); ?>" />
							</td>
							<td>								
							</td>
						</tr>		
						<tr>
			   				<th style="width: 25%;">
								<?php echo JText::_( 'Textarea Width' ); ?>
							</th>
							<td style="width: 150px;">
		       					<input type="text" name="eav_textarea_width" value="<?php echo $this->row->get('eav_textarea_width', '300'); ?>" />
							</td>
							<td>								
							</td>
						</tr>		
						<tr>
			   				<th style="width: 25%;">
								<?php echo JText::_( 'Textarea Height' ); ?>
							</th>
							<td style="width: 150px;">
		       					<input type="text" name="eav_textarea_height" value="<?php echo $this->row->get('eav_textarea_height', '200'); ?>" />
							</td>
							<td>								
							</td>
						</tr>					
					</tbody>
					</table>										
					<?php
						echo $this->sliders->endPanel();				
     				echo $this->sliders->startPanel( JText::_( "Features Settings" ), 'features_settings' );
					?>
					<table class="adminlist">
					<tbody>		
						<tr>
			   				<th style="width: 25%;">
								<?php echo JText::_( 'Enable Subscriptions' ); ?>
							</th>
							<td style="width: 150px;">
                <?php echo JHTML::_('select.booleanlist', 'display_subscriptions', 'class="inputbox"', $this->row->get('display_subscriptions', '1') ); ?>
							</td>
							<td>								
								<?php echo JText::_( 'Enable Subscriptions Note' );?>
							</td>
						</tr>
						<tr>
			   				<th style="width: 25%;">
								<?php echo JText::_( 'Enable My Downloads' ); ?>
							</th>
							<td style="width: 150px;">
                <?php echo JHTML::_('select.booleanlist', 'display_mydownloads', 'class="inputbox"', $this->row->get('display_mydownloads', '1') ); ?>
							</td>
							<td>								
								<?php echo JText::_( 'Enable My Downloads Note' );?>
							</td>
						</tr>
						<tr>
			   				<th style="width: 25%;">
								<?php echo JText::_( 'Enable Wishlist' ); ?>
							</th>
							<td style="width: 150px;">
                <?php echo JHTML::_('select.booleanlist', 'display_wishlist', 'class="inputbox"', $this->row->get('display_wishlist', '0') ); ?>
							</td>
							<td>								
							</td>
						</tr>		
						<tr>
		   				<th style="width: 25%;">
								<?php echo JText::_( 'Enable Credits' ); ?>
							</th>
							<td style="width: 150px;">
                <?php echo JHTML::_('select.booleanlist', 'display_credits', 'class="inputbox"', $this->row->get('display_credits', '0') ); ?>
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
						
						?>
					</td>
					<td style="vertical-align: top; max-width: 30%;">
					
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
