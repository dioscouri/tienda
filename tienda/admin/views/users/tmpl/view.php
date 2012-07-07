<?php defined('_JEXEC') or die('Restricted access');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
$form = @$this->form;
$row = @$this->row;
$carts = @$this->carts;
$procoms=@$this->procoms;
$orders=@$this->orders;
$subs=@$this->subs;
$surrounding = @$this->surrounding;
$total_cart=@$this->total_cart;

Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
Tienda::load( 'TiendaHelperUser', 'helpers.user' );
$helper_user = TiendaHelperBase::getInstance( 'user' );
$helper_product = TiendaHelperBase::getInstance( 'product' );


$config = Tienda::getInstance();
?>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">
<?php echo TiendaGrid::pagetooltip( 'users_view' ); ?>
<table width="100%" border="0">
	<tr>
		<td>
			<h2 style="padding:0px; margin:0px;"><?php echo @$row->first_name; ?>&nbsp;<?php echo @$row->last_name?></h2>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<fieldset>
				<legend><?php echo JText::_('COM_TIENDA_BASIC_USER_INFO'); ?></legend>
				<div id="tienda_header">
					<table class="admintable" style="width: 100%;" border="0">					
						<tr>
							<td  align="right" class="key">
		                        <label for="name">
		                        	<?php echo JText::_('COM_TIENDA_USERNAME'); ?>:
		                        </label>
	                    	</td>
	                    	<td style="width:120px;">
	                        	<div class="name"><?php echo @$row->username; ?></div>          
	                    	</td>
	                    	<td  align="right" class="key">	
		                        <label for="registerDate">
		                        	<?php echo JText::_('COM_TIENDA_REGISTERED'); ?>:
		                        </label>
		                    </td>
		                    <td>
		                        <div class="registerDate"><?php echo JHTML::_('date', @$row->registerDate, Tienda::getInstance()->get('date_format')); ?></div>         
		                    </td>
		                    <td rowspan="3" align="center" valign="top">
		                    	<div style="padding:0px; margin-bottom:5px;width:auto;">
									<?php echo $helper_user->getAvatar($row->id);?>
								</div>
		                      <?php
										if(version_compare(JVERSION,'1.6.0','ge')) {
										// Joomla! 1.6+ code here
										$url = $config->get( "user_edit_url", "index.php?option=com_users&task=user.edit&id=");
										} else {
										// Joomla! 1.5 code here
										$url = $config->get( "user_edit_url", "index.php?option=com_users&view=user&task=edit&cid[]=");
										}
										//

										$url .= @$row->id;
										$text = "<button>".JText::_('COM_TIENDA_EDIT_USER')."</button>";
								?>		                        
		                        <div ><?php echo TiendaUrl::popup( $url, $text, array('update' => true) ); ?></div>
		                    </td>  
						</tr>
						<tr>
							<td align="right" class="key" key">
		                        <label for="email">
		                        	<?php echo JText::_('COM_TIENDA_EMAIL'); ?>:
		                        </label>
	                    	</td>
	                    	<td>
	                        	<div class="name"><?php echo @$row->email; ?></div>          
	                    	</td>  
	                    	<td align="right" class="key">
		                        <label for="lastvisitDate">
		                        	<?php echo JText::_('COM_TIENDA_LAST_VISITED'); ?>:
		                        </label>
		                    </td>
		                    <td colspan="3">
		                        <div class="lastvisitDate"><?php echo JHTML::_('date', @$row->lastvisitDate, Tienda::getInstance()->get('date_format')); ?></div>           
		                    </td>
						</tr>
						<tr>
							<td  align="right" class="key" style="width:85px;">
		                        <label for="id">
		                        	<?php echo JText::_('COM_TIENDA_ID'); ?>:
		                        </label>
		                    </td>
		                    <td>
		                        <div class="id"><?php echo @$row->id; ?></div>          
		                    </td>
		                    <td align="right" class="key" style="width:85px;">
		                        <label for="group_name">
		                        	<?php echo JText::_('COM_TIENDA_USER_GROUP'); ?>:
		                        </label>
		                    </td>
		                    <td colspan="3">
		                      	<div class="id"><?php echo @$row->group_name; ?></div>		                      	
		                    </td>
						</tr>
						<?php if( $config->get( 'display_subnum', 0 ) ) :?>
						<tr>
							<td  align="right" class="key" style="width:85px;">
		                        <label for="sub_number">
		                        	<?php echo JText::_('COM_TIENDA_SUB_NUM'); ?>:
		                        </label>
		                    </td>
		                    <td>
		                        <div class="sub_number"><input name="sub_number" id="sub_number" value="<?php echo @$row->sub_number; ?>" /></div>
		                    </td>
	                    	<td >
	                    			<button name="submit_number" id="submit_number" onclick="tiendaSubmitForm('change_subnum')"><?php echo JText::_('COM_TIENDA_CHANGE_SUB_NUM'); ?></button>
		                    </td>
		                    <td colspan="3">
		                    </td>
						</tr>
						<?php endif; ?>
					</table>
				</div>
			</fieldset>
		</td>
	</tr>
	<tr>
		<td width="50%" valign="top">
				<fieldset>
					<legend><?php echo JText::_('COM_TIENDA_SUMMARY_DATA'); ?></legend>
						<table class="admintable"  width="100%">
							<tr>
								<td class="key" align="right" style="width:250px;">
									<?php echo JText::_('COM_TIENDA_NUMBER_OF_COMPLETED_ORDERS'); ?>:
								</td>
								<td align="right">
									<div class="id"><?php echo count($orders); ?></div>
								</td>
							</tr>
							<tr>
								<td class="key" align="right" style="width:250px;">
									<?php echo JText::_('COM_TIENDA_TOTAL_AMOUNT_SPENT'); ?>:
								</td>
								<td align="right">
									<div class="id"><?php echo TiendaHelperBase::currency (@$this->spent); ?></div>
								</td>
							</tr>
							<tr>
								<td class="key" align="right" style="width:250px;">
									<?php echo JText::_('COM_TIENDA_TOTAL_USER_REVIEWS'); ?>:
								</td>
								<td align="right">
									<div class="id"><?php echo count($procoms); ?></div>
								</td>
							</tr>
						</table>
				</fieldset>			
			<fieldset>
					<legend><?php echo JText::_('COM_TIENDA_LAST_5_COMPLETED_ORDERS'); ?></legend>
					<div id="tienda_header">
					<table class="adminlist" style="width: 100%;">
						<thead>
							<tr>
								<th style="width: 5px;">
									<?php echo JText::_('COM_TIENDA_ID'); ?>
								</th>
								<th style="width: 200px;">
									<?php echo JText::_('COM_TIENDA_DATE'); ?>
								</th>
								<th style="width: 150px; text-align: right;">
									<?php echo JText::_('COM_TIENDA_TOTAL'); ?>
								</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="20"></td>
							</tr>
						</tfoot>
						<tbody>
							<?php $i=0; $k=0; ?>
							<?php foreach (@$orders as $order) : ?>
								<tr class='row <?php echo $k; ?>'>
									<td align="center">
										<?php echo $order->order_id; ?>
									</td>
									<td style="text-align:left;">
										<a href="index.php?option=com_tienda&view=orders&task=view&id=<?php echo $order->order_id; ?>" >
											<?php echo $order->created_date; ?>
										</a>
									</td>
									<td style="text-align:right;">
										<?php echo TiendaHelperBase::currency($order->order_total); ?>										
									</td>
								</tr>
								<?php if ($i==4) break;?>
							<?php ++$i; $k = (1 - $k); ?>
							<?php endforeach; ?>
							<?php if (!count(@$order)) : ?>
								<tr>
									<td colspan="10" align="center"><?php echo JText::_('COM_TIENDA_NO_ITEMS_FOUND'); ?>
									</td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
					</div>
				</fieldset>
		</td>
		<td width="50%" valign="top">					
			<fieldset>
					<legend><?php echo JText::_('COM_TIENDA_LIST_OF_ACTIVE_SUBSCRIPTIONS'); ?></legend>
					<table class="adminlist" style="width: 100%;">
						<thead>
							<tr>
								<th style="width: 5px;">
									<?php echo JText::_('COM_TIENDA_NUM'); ?>
								</th>
								<th style="width: 200px;">
									<?php echo JText::_('COM_TIENDA_TYPE'); ?>
								</th>
								<th style="width: 200px;">
									<?php echo JText::_('COM_TIENDA_ORDER'); ?>
								</th>
								<th style="text-align: center;  width: 200px;">
									<?php echo JText::_('COM_TIENDA_EXPIRES'); ?>
								</th>								
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="20"></td>
							</tr>
						</tfoot>
						<tbody>
							<?php $i=0; $k=0; ?>
							<?php foreach (@$subs as $sub) : ?>
								<tr class='row <?php echo $k; ?>'>
									<td align="center">
										<?php echo $i + 1; ?>
									</td>
									<td style="text-align:left;">
										<a href="	index.php?option=com_tienda&view=subscriptions&task=view&id=<?php echo $sub->subscription_id; ?>" >
											<?php echo $sub->product_name; ?>
										</a>
									</td>
									<td style="text-align:center;">
										<a href="	index.php?option=com_tienda&view=subscriptions&task=view&id=<?php echo $sub->subscription_id; ?>" >
											<?php echo $sub->order_id; ?>										
										</a>
									</td>									
									<td style="text-align:center;">
										<a href="	index.php?option=com_tienda&view=subscriptions&task=view&id=<?php echo $sub->subscription_id; ?>" >											
											<?php if($sub->subscription_lifetime == 1)
												{
													 echo JText::_('COM_TIENDA_LIFETIME'); 
												}
											?>											
											<?php echo JHTML::_('date', $sub->expires_datetime, Tienda::getInstance()->get('date_format')); ?>
										</a>
									</td>				
								</tr>
							<?php ++$i; $k = (1 - $k); ?>
							<?php endforeach; ?>
							<?php if (!count(@$sub)) : ?>
								<tr>
									<td colspan="10" align="center"><?php echo JText::_('COM_TIENDA_NO_ITEMS_FOUND'); ?>
									</td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</fieldset>
			<fieldset>
					<legend><?php echo JText::_('COM_TIENDA_CART'); ?></legend>
					<table class="adminlist" style="width: 100%;">
						<thead>
							<tr>
								<th style="width: 5px;">
									<?php echo JText::_('COM_TIENDA_NUM'); ?>
								</th>
								<th style="width: 200px;">
									<?php echo JText::_('COM_TIENDA_PRODUCTS'); ?>
								</th>
								<th style="width: 200px;">
									<?php echo JText::_('COM_TIENDA_PRICE'); ?>
								</th>
								<th style="text-align: center;  width: 200px;">
									<?php echo JText::_('COM_TIENDA_QUANTITY'); ?>
								</th>
								<th style="width: 150px; text-align: right;">
									<?php echo JText::_('COM_TIENDA_TOTAL'); ?>
								</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="20"></td>
							</tr>
						</tfoot>
						<tbody>
							<?php $i=0; $k=0; ?>
							<?php foreach (@$carts as $cart) : ?>
								<tr class='row <?php echo $k; ?>'>
									<td align="center">
										<?php echo $i + 1; ?>
									</td>
									<td style="text-align:left;">
										<a href="index.php?option=com_tienda&view=products&task=edit&id=<?php echo $cart->product_id; ?>" >
											<?php echo $cart->product_name; ?>
										</a>
									</td>
									<td style="text-align:right;">
										<?php echo TiendaHelperBase::currency($cart->product_price); ?>										
									</td>
									<td style="text-align:center;">
										<?php echo $cart->product_qty;?>
									</td>
									<td style="text-align:right;">
										<?php echo TiendaHelperBase::currency($cart->total_price); ?>									
									</td>
								</tr>
							<?php ++$i; $k = (1 - $k); ?>
							<?php endforeach; ?>
							<?php if (!count(@$cart)) : ?>
								<tr>
									<td colspan="10" align="center"><?php echo JText::_('COM_TIENDA_NO_ITEMS_FOUND'); ?>
									</td>
								</tr>
							<?php endif; ?>
						</tbody>
						<thead>
							<tr>
								<th style="width: 5px;">
									&nbsp;
								</th>
								<th style="width: 200px;">
									&nbsp;
								</th>
								<th style="width: 200px;">
									&nbsp;
								</th>

								<th style="text-align: center;  width: 200px;">
									<?php echo JText::_('COM_TIENDA_TOTAL'); ?>
								</th>
								<th style="width: 150px; text-align: right;">
									<?php echo TiendaHelperBase::currency(@$total_cart); ?>	
								</th>
							</tr>
						</thead>
					</table>
				</fieldset>
			<fieldset>
					<legend><?php echo JText::_('COM_TIENDA_LAST_5_REVIEWS_POSTED'); ?></legend>
					<table class="adminlist" style="width: 100%;">
						<thead>
							<tr>
								<th style="width: 5px;">
									<?php echo JText::_('COM_TIENDA_NUM'); ?>
								</th>
								<th>
									<?php echo JText::_('COM_TIENDA_PRODUCTS_PLUS_COMMENTS'); ?>
								</th>
								<th style="width: 200px;">
									<?php echo JText::_('COM_TIENDA_USER_RATING'); ?>
								</th>													
							</tr>
						</thead>		
						<tfoot>
							<tr>
								<td colspan="20"></td>
							</tr>
						</tfoot>
						<tbody>
							<?php $i=0; $k=0; ?>
							<?php foreach (@$procoms as $procom) : ?>
								<tr class='row <?php echo $k; ?>'>
									<td align="center">
										<?php echo $i + 1; ?>
									</td>
									<td style="text-align:left;">
										<a href="index.php?option=com_tienda&view=productcomments&task=edit&id=<?php echo $procom->product_id; ?>" >
											<?php echo $procom->p_name; ?></a><br/><?php echo $procom->trimcom; ?>							
									</td>
									<td style="text-align:center;">
									
										<?php  echo $helper_product->getRatingImage(null, $procom->productcomment_rating ); ?>						
									</td>
								</tr>
								<?php if ($i==4) break;?>
							<?php ++$i; $k = (1 - $k); ?>
							<?php endforeach; ?>
							<?php if (!count(@$procom)) : ?>
								<tr>
									<td colspan="10" align="center"><?php echo JText::_('COM_TIENDA_NO_ITEMS_FOUND'); ?>
									</td>
								</tr>
							<?php endif; ?>
						</tbody>	
					</table>
				</fieldset>
		</td>
	</tr>
</table>
<table style="width: 100%;">
	<tr>
		<td style="width: 70%; max-width: 70%; vertical-align: top; padding: 0px 5px 0px 5px;">		
			<?php
            $modules = JModuleHelper::getModules("tienda_user_main");
            $document   = JFactory::getDocument();	
            $renderer   = $document->loadRenderer('module');
            $attribs    = array();
            $attribs['style'] = 'xhtml';	
            foreach ( @$modules as $mod ) 
            {
                echo $renderer->render($mod, $attribs);
            }
            ?>
		</td>
		<td style="vertical-align: top; width: 30%; min-width: 30%; padding: 0px 5px 0px 5px;">
			<?php
            $modules = JModuleHelper::getModules("tienda_user_right");
            $attribs    = array();
            $attribs['style'] = 'xhtml';
            foreach ( @$modules as $mod ) 
            {
                echo $renderer->render($mod, $attribs);
            }
            ?> 
		</td>
	</tr>
</table>
	<input type="hidden" name="prev" value="<?php echo intval(@$surrounding["prev"]); ?>" />
    <input type="hidden" name="next" value="<?php echo intval(@$surrounding["next"]); ?>" /> 
    <input type="hidden" name="id" value="<?php echo @$row->id; ?>" />
    <input type="hidden" name="task" value="" />
</form>