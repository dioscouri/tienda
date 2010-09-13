<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php $row = @$this->row; ?>
                            
<h1 style="margin-left: 2%; margin-top: 2%;"><?php echo JText::_( "Manage Files for" ); ?>: <?php echo $row->product_name; ?></h1>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>
<div class="note" style="width: 96%; margin-left: auto; margin-right: auto;">
    <div style="float: left; font-size: 1.3em; font-weight: bold; height: 30px;"><?php echo JText::_('Upload a New File'); ?></div>
    <div style="float: right;">
        <button onclick="document.getElementById('task').value='createfile'; document.adminForm.submit();"><?php echo JText::_('Upload File'); ?></button>
    </div>
    <div class="reset"></div>

    <table class="adminlist">
    	<thead>
    	<tr>
    		<th><?php echo JText::_( "Name" ); ?></th>
    		<th><?php echo JText::_( "Purchase Required" ); ?></th>
    		<th><?php echo JText::_( "Enabled" ); ?></th>
    		<th></th>
    		<th><?php echo JText::_( "Max number of Downloads" ); ?></th>
    	</tr>
    	</thead>
    	<tbody>
    	<tr>
    		<td style="text-align: center;">
    			<input id="createproductfile_name" name="createproductfile_name" value="" size="40" />
    		</td>
            <td style="text-align: center;">
                <?php echo JHTML::_('select.booleanlist', 'createproductfile_purchaserequired', '', '' ); ?>
            </td>
    		<td style="text-align: center;">
    		    <?php echo JHTML::_('select.booleanlist', 'createproductfile_enabled', '', '' ); ?>
    		</td>
            <td style="text-align: center;">
                <input name="createproductfile_file" type="file" size="40" />
            </td>
           <td>
            <input type="text" name="max_download" id="max_download" value="-1" size="30" maxlength="250" />
           </td>
            
    	</tr>
    	</tbody>
    </table>

</div>

<div class="note" style="width: 96%; margin-left: auto; margin-right: auto;">
    <div style="float: left; font-size: 1.3em; font-weight: bold; height: 30px;"><?php echo JText::_('Choose a New File from Server'); ?></div>
    <div style="float: right;">
        <button onclick="document.getElementById('task').value='createfilefromdisk'; document.adminForm.submit();"><?php echo JText::_('Create File'); ?></button>
    </div>
    <div class="reset"></div>

    <table class="adminlist">
    	<thead>
    	<tr>
    		<th><?php echo JText::_( "Name" ); ?></th>
    		<th><?php echo JText::_( "Purchase Required" ); ?></th>
    		<th><?php echo JText::_( "Enabled" ); ?></th>
    		<th></th>
    		<th><?php echo JText::_( "Max number of Downloads" ); ?></th>
    	</tr>
    	</thead>
    	<tbody>
    	<tr>
    		<td style="text-align: center;">
    			<input id="createproductfile_name" name="createproductfile_name" value="" size="40" />
    		</td>
            <td style="text-align: center;">
                <?php echo JHTML::_('select.booleanlist', 'createproductfile_purchaserequired', '', '' ); ?>
            </td>
    		<td style="text-align: center;">
    		    <?php echo JHTML::_('select.booleanlist', 'createproductfile_enabled', '', '' ); ?>
    		</td>
            <td style="text-align: center;">
                <?php 
                	$helper = Tienda::getClass('TiendaHelperProduct', 'helpers.product');
                	$path = $helper->getFilePath($row->product_id);
					$files = $helper->getServerFiles($path);
					
					$list = array();
					foreach(@$files as $file){
						$list[] =  TiendaSelect::option( $file, $file );
					}
					
					echo JHTMLSelect::genericlist($list, 'createproductfile_file');
                ?>
            </td>
             <td>
            <input type="text" name="max_download" id="max_download" value="-1" size="30" maxlength="250" />
           </td>
            
    	</tr>
    	</tbody>
    </table>

</div>

<div class="note_green" style="width: 96%; margin-left: auto; margin-right: auto;">
    <div style="float: left; font-size: 1.3em; font-weight: bold; height: 30px;"><?php echo JText::_('Current Files'); ?></div>
    <div style="float: right;">
        <button onclick="document.getElementById('task').value='savefiles'; document.adminForm.toggle.checked=true; checkAll(<?php echo count( @$items ); ?>); document.adminForm.submit();"><?php echo JText::_('Save All Changes'); ?></button>
    </div>
    <div class="reset"></div>

	<table class="adminlist">
		<thead>
            <tr>
                <th style="width: 20px;">
                	<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                </th>
                <th style="text-align: left;">
                	<?php echo TiendaGrid::sort( 'Display Name', "tbl.productfile_name", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'Order', "tbl.ordering", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'Purchase Required', "tbl.purchase_required", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'Enabled', "tbl.productfile_enabled", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                  <!-- // TODO make it  sortable  --> 
                     <?php
                      //TODO for sorting 
                     //echo TiendaGrid::sort( 'Max download', "tbl.productfile_enabled", @$state->direction, @$state->order ); ?>
                    <?php echo JText::_( 'Max Download'); ?>
                </th>
				<th style="width: 100px;">
				</th>
            </tr>
		</thead>
        <tbody>
		<?php $i=0; $k=0; ?>
        <?php foreach (@$items as $item) : ?>
            <tr class='row<?php echo $k; ?>'>
				<td style="text-align: center;">
					<?php echo TiendaGrid::checkedout( $item, $i, 'productfile_id' ); ?>
				</td>
				<td style="text-align: left;">
					<input type="text" name="name[<?php echo $item->productfile_id; ?>]" value="<?php echo $item->productfile_name; ?>" size="40" />
				</td>
                <td style="text-align: center;">
                    <input type="text" name="ordering[<?php echo $item->productfile_id; ?>]" value="<?php echo $item->ordering; ?>" size="10" />
                </td>
				<td style="text-align: center;">
				    <?php echo JHTML::_('select.booleanlist', "purchaserequired[".$item->productfile_id."]", '', $item->purchase_required ); ?>
				</td>
                <td style="text-align: center;">
                    <?php echo JHTML::_('select.booleanlist', "enabled[".$item->productfile_id."]", '', $item->productfile_enabled ); ?>
                </td>
                <td style="text-align: center;">
                    <input type="text" name="max_download[<?php echo $item->productfile_id; ?>]" value="<?php echo $item->max_download; ?>" size="10" />
                </td>
				<td style="text-align: center;">
					[<a href="index.php?option=com_tienda&controller=productfiles&task=delete&cid[]=<?php echo $item->productfile_id; ?>&return=<?php echo base64_encode("index.php?option=com_tienda&controller=products&task=setfiles&id={$row->product_id}&tmpl=component"); ?>">
						<?php echo JText::_( "Delete File" ); ?>	
					</a>
					]
				</td>
			</tr>
			<?php $i=$i+1; $k = (1 - $k); ?>
			<?php endforeach; ?>
			
			<?php if (!count(@$items)) : ?>
			<tr>
				<td colspan="10" align="center">
					<?php echo JText::_('No items found'); ?>
				</td>
			</tr>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="20">
					<?php echo @$this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
	</table>

	<input type="hidden" name="order_change" value="0" />
	<input type="hidden" name="id" value="<?php echo $row->product_id; ?>" />
	<input type="hidden" name="task" id="task" value="setfiles" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
	
	<?php echo $this->form['validate']; ?>
</div>
</form>