<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; ?>
<?php $carts = @$this->carts; ?>
<?php $procoms=@$this->procoms; ?>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">
<h2 style="padding:0px; margin:0px;"><?php echo @$row->first_name; ?> <?php echo @$row->middle_name; ?> <?php echo @$row->last_name; ?></h2>
<?php echo TiendaGrid::pagetooltip( 'users_view' ); ?>
<table width="100%" border="0">
	<tr>
		<td width="50%">
			<fieldset>
				<legend><?php echo JText::_('Basic User Info'); ?></legend>
				<div id="tienda_header">
					<table class="admintable" style="width: 100%;">					
						<tr>
							<td  align="right" class="key" style="width:65px;">
		                        <label for="name">
		                        	<?php echo JText::_( 'Username' ); ?>:
		                        </label>
	                    	</td>
	                    	<td>
	                        	<div class="name"><?php echo @$row->username; ?></div>          
	                    	</td>
	                    	<td align="right" class="key" key" style="width:65px;">
		                        <label for="email">
		                        	<?php echo JText::_( 'Email' ); ?>:
		                        </label>
	                    	</td>
	                    	<td>
	                        	<div class="name"><?php echo @$row->email; ?></div>          
	                    	</td>   
	                    	<td  align="right" class="key" key" style="width:65px;">
		                        <label for="id">
		                        	<?php echo JText::_( 'ID' ); ?>:
		                        </label>
		                    </td>
		                    <td>
		                        <div class="id"><?php echo @$row->id; ?></div>          
		                    </td>
	                    	<td>
		                        <?php
		                        $config = TiendaConfig::getInstance();
		                        $url = $config->get( "user_edit_url", "index.php?option=com_users&view=user&task=edit&cid[]=");
		                        $url .= @$row->id; 
		                        $text = "<button>".JText::_('Edit User')."</button>"; 
		                        ?>		                        
		                        <div style="float: right;"><?php echo TiendaUrl::popup( $url, $text, array('update' => true) ); ?></div>
		                    </td>                 	
						</tr>
						<tr>
							<td  align="right" class="key" style="width:65px;">
		                        <label for="registerDate">
		                        	<?php echo JText::_( 'Registered' ); ?>:
		                        </label>
		                    </td>
		                    <td colspan="2">
		                        <div class="registerDate"><?php echo JHTML::_('date', @$row->registerDate, "%a, %d %b %Y, %H:%M"); ?></div>         
		                    </td>
		                    <td align="right" class="key">
		                        <label for="lastvisitDate">
		                        	<?php echo JText::_( 'Last Visited' ); ?>:
		                        </label>
		                    </td>
		                    <td colspan="3">
		                        <div class="lastvisitDate"><?php echo JHTML::_('date', @$row->lastvisitDate, "%a, %d %b %Y, %H:%M"); ?></div>           
		                    </td>
						</tr>
						<tr>
							<td align="right" class="key" style="width:65px;">
		                        <label for="group_name">
		                        	<?php echo JText::_( 'User Group' ); ?>:
		                        </label>
		                    </td>
		                    <td colspan="3">
		                      	<div class="id"><?php echo @$row->group_name; ?></div>     
		                      	<table class="admintable" style="width: 100%;">
		                      		<tr>
		                      			<td>
		                      			</td>
		                      		</tr>		
		                      	</table>     
		                    </td>
						</tr>
					</table>
				</div>
			</fieldset>
			
				<fieldset>
					<legend><?php echo JText::_('Summary Data'); ?></legend>
					
				</fieldset>
			
			<fieldset>
					<legend><?php echo JText::_('Last 5 Completed Orders'); ?></legend>
					
				</fieldset>
		</td>
		<td width="50%">
			<fieldset>
					<legend><?php echo JText::_('Integration'); ?></legend>
					
				</fieldset>
			<fieldset>
					<legend><?php echo JText::_('List of Active Subscriptions'); ?></legend>
					
				</fieldset>
			<fieldset>
					<legend><?php echo JText::_('Cart'); ?></legend>
					<table class="adminlist" style="width: 100%;">
						<thead>
							<tr>
								<th style="width: 5px;">
									<?php echo JText::_("Num"); ?>
								</th>
								<th style="width: 200px;">
									<?php echo JText::_("Products"); ?>
								</th>
								<th style="width: 200px;">
									<?php echo JText::_("Price"); ?>
								</th>
								<th style="text-align: center;  width: 200px;">
									<?php echo JText::_("Quantity"); ?>
								</th>
								<th style="width: 150px; text-align: right;">
									<?php echo JText::_("Total"); ?>
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
									<td style="text-align:center;">
										<a href="index.php?option=com_tienda&view=products&task=edit&id=<?php echo $cart->product_id; ?>" target="_blank">
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
						</tbody>
					</table>
				</fieldset>
			<fieldset>
					<legend><?php echo JText::_('Last 5 Reviews Posted'); ?></legend>
					<table class="adminlist" style="width: 100%;">
						<thead>
							<tr>
								<th style="width: 5px;">
									<?php echo JText::_("Num"); ?>
								</th>
								<th>
									<?php echo JText::_("Products + Comments"); ?>
								</th>
								<th style="width: 200px;">
									<?php echo JText::_("User Rating"); ?>
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
										<a href="index.php?option=com_tienda&view=products&task=edit&id=<?php echo $procom->product_id; ?>" target="_blank">
											<?php echo $procom->p_name; ?></a><br/><?php echo $procom->trimcom; ?>							
									</td>
									<td style="text-align:center;">
										<?php echo TiendaHelperProduct::getRatingImage( @$procom->productcomment_rating ); ?>										
									</td>
								</tr>
							<?php ++$i; $k = (1 - $k); ?>
							<?php endforeach; ?>
						</tbody>	
					</table>
				</fieldset>
		</td>
	</tr>
</table>
    
    <input type="hidden" name="id" value="<?php echo @$row->id; ?>" />
    <input type="hidden" name="task" value="" />
</form>