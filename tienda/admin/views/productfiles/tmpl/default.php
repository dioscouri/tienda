<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php $row = @$this->row; ?>
                            
<h1 style="margin-left: 2%; margin-top: 2%;"><?php echo JText::_('COM_TIENDA_MANAGE_FILES_FOR'); ?>: <?php echo $row->product_name; ?></h1>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">

	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>
<div class="note" style="width: 96%; margin-left: auto; margin-right: auto;">
    <div style="float: left; font-size: 1.3em; font-weight: bold; height: 30px;"><?php echo JText::_('COM_TIENDA_UPLOAD_A_NEW_FILE'); ?></div>
    <div style="float: right;">
        <button class="btn btn-primary" onclick="document.getElementById('task').value='createfile'; document.adminForm.submit();"><?php echo JText::_('COM_TIENDA_UPLOAD_FILE'); ?></button>
    </div>
    <div class="reset"></div>

    <table class="table table-striped table-bordered">
    	<thead>
    	<tr>
    		<th><?php echo JText::_('COM_TIENDA_NAME'); ?></th>
    		<th><?php echo JText::_('COM_TIENDA_PURCHASE_REQUIRED'); ?></th>
    		<th><?php echo JText::_('COM_TIENDA_ENABLED'); ?></th>
    		<th></th>
    		<th><?php echo JText::_('COM_TIENDA_MAX_NUMBER_OF_DOWNLOADS'); ?><br><?php echo "(".JText::_('COM_TIENDA_USE_MINUS_ONE_FOR_UNLIMTED_DOWNLOADING').")" ?></th>
    	</tr>
    	</thead>
    	<tbody>
    	<tr>
    		<td style="text-align: center;">
    			<input id="createproductfile_name" name="createproductfile_name" value="" size="40" />
    		</td>
            <td style="text-align: center;">
                <?php echo TiendaSelect::btbooleanlist( 'createproductfile_purchaserequired', '', '' ); ?>
            </td>
    		<td style="text-align: center;">
    		    <?php echo TiendaSelect::btbooleanlist( 'createproductfile_enabled', '', '' ); ?>
    		</td>
            <td style="text-align: center;">
                <input name="createproductfile_file" type="file" size="40" />
            </td>
            <td style="text-align: center;">
                <input type="text" name="createproductfile_max_download" id="createproductfile_max_download" value="-1" size="10" maxlength="250" />
            </td>
    	</tr>
    	</tbody>
    </table>

</div>

<div class="note" style="width: 96%; margin-left: auto; margin-right: auto;">
    <div style="float: left; font-size: 1.3em; font-weight: bold; height: 30px;"><?php echo JText::_('COM_TIENDA_CHOOSE_A_NEW_FILE_FROM_SERVER'); ?></div>
    <div style="float: right;">
        <button class="btn btn-primary" onclick="document.getElementById('task').value='createfilefromdisk'; document.adminForm.submit();"><?php echo JText::_('COM_TIENDA_CREATE_FILE'); ?></button>
    </div>
    <div class="reset"></div>

    <table class="table table-striped table-bordered">
    	<thead>
    	<tr>
    		<th><?php echo JText::_('COM_TIENDA_NAME'); ?></th>
    		<th><?php echo JText::_('COM_TIENDA_PURCHASE_REQUIRED'); ?></th>
    		<th><?php echo JText::_('COM_TIENDA_ENABLED'); ?></th>
    		<th></th>
    		<th><?php echo JText::_('COM_TIENDA_MAX_NUMBER_OF_DOWNLOADS'); ?>
    		<br><?php echo "(".JText::_('COM_TIENDA_USE_MINUS_ONE_FOR_UNLIMTED_DOWNLOADING').")" ?>
    		</th>
    	</tr>
    	</thead>
    	<tbody>
    	<tr>
    		<td style="text-align: center;">
    			<input id="createproductfileserver_name" name="createproductfileserver_name" value="" size="40" />
    		</td>
            <td style="text-align: center;">
                <?php echo TiendaSelect::btbooleanlist( 'createproductfileserver_purchaserequired', '', '' ); ?>
            </td>
    		<td style="text-align: center;">
    		    <?php echo TiendaSelect::btbooleanlist( 'createproductfileserver_enabled', '', '' ); ?>
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
					
					echo JHTMLSelect::genericlist($list, 'createproductfileserver_file');
                ?>
            </td>
            <td style="text-align: center;">
                <input type="text" name="createproductfileserver_max_download" id="createproductfileserver_max_download" value="-1" size="10" maxlength="250" />
            </td>
            
    	</tr>
    	</tbody>
    </table>

</div>

<div class="note_green" style="width: 96%; margin-left: auto; margin-right: auto;">
    <div style="float: left; font-size: 1.3em; font-weight: bold; height: 30px;"><?php echo JText::_('COM_TIENDA_CURRENT_FILES'); ?></div>
    <div style="float: right;">
        <button class="btn btn-success" onclick="document.getElementById('task').value='savefiles'; document.adminForm.toggle.checked=true; checkAll(<?php echo count( @$items ); ?>); document.adminForm.submit();"><?php echo JText::_('COM_TIENDA_SAVE_ALL_CHANGES'); ?></button>
    </div>
    <div class="reset"></div>

	<table class="table table-striped table-bordered">
		<thead>
            <tr>
                <th style="width: 20px;">
                	<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                </th>
                <th style="text-align: left;">
                	<?php echo TiendaGrid::sort( 'COM_TIENDA_DISPLAY_NAME', "tbl.productfile_name", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_ORDER', "tbl.ordering", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_PURCHASE_REQUIRED', "tbl.purchase_required", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_ENABLED', "tbl.productfile_enabled", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                  <!-- // TODO make it  sortable  --> 
                     <?php
                      //TODO for sorting 
                     //echo TiendaGrid::sort( 'COM_TIENDA_MAX_DOWNLOAD', "tbl.productfile_enabled", @$state->direction, @$state->order ); ?>
                    <?php echo JText::_('COM_TIENDA_MAX_DOWNLOAD');  ?>
                    <br><?php echo "(".JText::_('COM_TIENDA_USE_MINUS_ONE_FOR_UNLIMTED_DOWNLOADING').")" ?>
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
				    <?php echo TiendaSelect::btbooleanlist( "purchaserequired[".$item->productfile_id."]", '', $item->purchase_required ); ?>
				</td>
                <td style="text-align: center;">
                    <?php echo TiendaSelect::btbooleanlist( "enabled[".$item->productfile_id."]", '', $item->productfile_enabled ); ?>
                </td>
                <td style="text-align: center;">
                    <input type="text" name="max_download[<?php echo $item->productfile_id; ?>]" value="<?php echo $item->max_download; ?>" size="10" />
                </td>
				<td style="text-align: center;">
					[<a href="index.php?option=com_tienda&controller=productfiles&task=delete&cid[]=<?php echo $item->productfile_id; ?>&return=<?php echo base64_encode("index.php?option=com_tienda&controller=products&task=setfiles&id={$row->product_id}&tmpl=component"); ?>">
						<?php echo JText::_('COM_TIENDA_DELETE_FILE'); ?>	
					</a>
					]
				</td>
			</tr>
			<?php $i=$i+1; $k = (1 - $k); ?>
			<?php endforeach; ?>
			
			<?php if (!count(@$items)) : ?>
			<tr>
				<td colspan="10" align="center">
					<?php echo JText::_('COM_TIENDA_NO_ITEMS_FOUND'); ?>
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