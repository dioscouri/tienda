<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; ?>

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
					
					$legend = JText::_( "Currency Settings" );
					echo $this->sliders->startPanel( JText::_( $legend ), 'currency' );
					?>
					
					<table class="adminlist">
					<tbody>
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
					
					$legend = JText::_( "Other Settings" );
					echo $this->sliders->startPanel( JText::_( $legend ), 'others' );
					?>
					
					<table class="adminlist">
					<tbody>
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
								<?php echo JText::_( 'Show Shipping Tax' ); ?>
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
