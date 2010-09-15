<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>
    <table>
      <tr class="filterline">
                <td>
                	<?php $attribs = array('class' => 'inputbox', 'size' => '1', 'onchange' => 'document.adminForm.submit();'); ?>
                   
                    <div class="filtername" >
                    <span class="label"><?php echo JText::_("Name"); ?>:</span> 
                    <input id="filter_name" name="filter_name" value="<?php echo @$state->filter_name; ?>" size="25"/>
                    </div>            
                	
                	
                	<div class="filtermulticategory" >
                    <span class="label"><?php echo JText::_("Category"); ?>: </span> 
                    <?php $catattribut=array('class' => 'inputbox', 'size' => '1','multiple' => 'yes' , 'size'=>3 );?>
                	<?php echo TiendaSelect::category( @$state->filter_multicategory, 'filter_multicategory[]',$catattribut , 'filter_multicategory', true ); ?>
                    </div>
                	
                	
                	
                	<div class="filtershipping" >
                        <span class="label"><?php echo JText::_("Requires Shipping"); ?>: </span> 
                        <?php echo TiendaSelect::booleans( @$state->filter_ships, 'filter_ships', '', 'ships', true, "Doesn't Matter", 'Yes', 'No' ); ?>
                	 </div>
                	
                	 <div class="filtersku" >   
                	  <span class="label"><?php echo JText::_("SKU"); ?>: </span>         	                
                	<input id="filter_sku" name="filter_sku" value="<?php echo @$state->filter_sku; ?>" size="15"/>
                    </div>
                    
                	<div class="Price">
	                   	<div class="rangeline">
	                	<span class="label"><?php echo JText::_("Price"); ?>: </span> 
	                		<span class="label"><?php echo JText::_("From"); ?>:</span> <input id="filter_price_from" name="filter_price_from" value="<?php echo @$state->filter_price_from; ?>" size="5" class="input" />
	                		<span class="label"><?php echo JText::_("To"); ?>:</span> <input id="filter_price_to" name="filter_price_to" value="<?php echo @$state->filter_price_to; ?>" size="5" class="input" />
	                	</div>
                	</div>
                
                	<div class="quantity">
	                	<span class="label"><?php echo JText::_("Show only Items that are in Stock"); ?>: </span>
	                	<?php echo TiendaSelect::booleanlist( 'filter_stock', '', @$state->filter_stock ); ?> 
                	</div>
                	
                	<div class="filterdescription" >   
                	  <span class="label"><?php echo JText::_("Description"); ?>: </span>         	                
                	<input id="filter_description" name="filter_description" value="<?php echo @$state->filter_description; ?>" size="15"/>
                    </div>
                    
                    <div class="filtermanufacturer" >   
                	  <span class="label"><?php echo JText::_("Manufacturer"); ?>: </span>         	                
                	<input id="filter_manufacturer" name="filter_manufacturer" value="<?php echo @$state->filter_manufacturer; ?>" size="15"/>
                    </div>
                    
                    <div class="submint" >   
                	 <input id="filter_submit" name="filter_submit" type="submit" value="<?php echo JText::_("Search") ?>"/>
                    </div>
               
                </td>
            </tr>
    </table>
	<hr width="100%"> 
	<table class="adminlist" style="clear: both;">
		<thead>
            <tr>
                <th style="width: 5px;">
                	<?php echo JText::_("Num"); ?>
                </th>
                 <th style="text-align: left;" colspan="2">
                	<?php echo TiendaGrid::sort( 'Name', "tbl.product_name", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 70px;">
                	<?php echo TiendaGrid::sort( 'SKU', "tbl.product_sku", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 50px;">
                	<?php echo TiendaGrid::sort( 'Price', "price", @$state->direction, @$state->order ); ?>
                </th>
             </tr>
          	
		</thead>
		<tfoot>
			<tr>
				<td colspan="20">
					<div style="float: right; padding: 5px;"><?php echo @$this->pagination->getResultsCounter(); ?></div>
					<?php echo @$this->pagination->getPagesLinks(); ?>
				</td>
			</tr>
		</tfoot>
        <tbody>
		<?php $i=0; $k=0; ?>
        <?php foreach (@$items as $item) : ?>
            <tr class='row<?php echo $k; ?>'>
				<td align="center">
					<?php echo $i + 1; ?>
				</td>
				<td style="text-align: center; width: 50px;">
                    <?php echo TiendaHelperProduct::getImage($item->product_id, 'id', $item->product_name, 'full', false, false, array( 'width'=>48 ) ); ?>
				</td> 
				<td style="text-align: left;">
					<a href="<?php echo $item->link; ?>">
						<?php echo JText::_($item->product_name); ?>
					</a>
				</td>
				<td style="text-align: center;">
					<?php echo $item->product_sku; ?>
				</td>
				<td style="text-align: right;">
					<?php echo TiendaHelperBase::currency($item->price); ?>
				</td>
				
               </tr>
			<?php ++$i; $k = (1 - $k); ?>
			<?php endforeach; ?>

			<?php if (!count(@$items)) : ?>
			<tr>
				<td colspan="10" align="center">
					<?php echo JText::_('No items found'); ?>
				</td>
			</tr>
			<?php endif; ?>
		</tbody>
		
	</table>
	<hr width="100%"> 
	<table>
	<tr>
				<th colspan="20" style="font-weight: normal;">
					<div style="float: right; padding: 5px;"><?php echo @$this->pagination->getResultsCounter(); ?></div>
					<div style="float: left;"><?php echo @$this->pagination->getListFooter(); ?></div>
				</th>
			</tr></table>

	<input type="hidden" name="order_change" value="0" />
	<input type="hidden" name="id" value="" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />

	<?php echo $this->form['validate']; ?>
</form>