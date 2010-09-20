<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php $row = @$this->row; ?>

<h1 style="margin-left: 2%; margin-top: 2%;"><?php echo JText::_( "Select Users for" ); ?>: <?php echo $row->group_name; ?></h1>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">
<div class="note_green" style="width: 96%; text-align: center; margin-left: auto; margin-right: auto;">
    <?php echo JText::_( "For Checked Items" ); ?>:
    <button onclick="document.getElementById('task').value='selected_switch'; document.adminForm.submit();"> <?php echo JText::_( "Change Status" ); ?></button>

    <table class="adminlist" style="clear: both;">
		<thead>
            <tr>
                <th style="width: 5px;">
                	<?php echo JText::_("Num"); ?>
                </th>
                <th style="width: 20px;">
                	<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                </th>
                <th style="width: 50px;">
                	<?php echo TiendaGrid::sort( 'ID', "tbl.id", @$state->direction, @$state->order ); ?>
                </th>                
                <th style="text-align: left;">
                	<?php echo TiendaGrid::sort( 'Name', "tbl.name", @$state->direction, @$state->order ); ?>
                </th>
                <th>
                	<?php echo TiendaGrid::sort( 'Username', "tbl.username", @$state->direction, @$state->order ); ?>
                </th>
				<th>
					<?php echo TiendaGrid::sort( 'Email', 'tbl.email', @$state->direction, @$state->order); ?>
				</th>
				<th>
				</th>
            </tr>
            <tr class="filterline">
                <th colspan="3">
                    <?php $attribs = array('class' => 'inputbox', 'size' => '1', 'onchange' => 'document.adminForm.submit();'); ?>
                    <div class="range">
                        <div class="rangeline">
                            <span class="label"><?php echo JText::_("From"); ?>:</span> <input id="filter_id_from" name="filter_id_from" value="<?php echo @$state->filter_id_from; ?>" size="5" class="input" />
                        </div>
                        <div class="rangeline">
                            <span class="label"><?php echo JText::_("To"); ?>:</span> <input id="filter_id_to" name="filter_id_to" value="<?php echo @$state->filter_id_to; ?>" size="5" class="input" />
                        </div>
                    </div>
                </th>
                <th style="text-align: left;">
                    <input id="filter_name" name="filter_name" value="<?php echo @$state->filter_name; ?>" size="25"/>
                    <input type="button" name="filter-search" onclick="document.getElementById('task').value='selectusers'; document.adminForm.submit();" value="<?php echo JText::_('Filter');?>" />
                </th>
                <th>
                    <input id="filter_username" name="filter_username" value="<?php echo @$state->filter_username; ?>" size="25"/>
                    <input type="button" name="filter-search" onclick="document.getElementById('task').value='selectusers'; document.adminForm.submit();" value="<?php echo JText::_('Filter');?>" />
                </th>
                <th>
                    <input id="filter_email" name="filter_email" value="<?php echo @$state->filter_email; ?>" size="25"/>
                    <input type="button" name="filter-search" onclick="document.getElementById('task').value='selectusers'; document.adminForm.submit();" value="<?php echo JText::_('Filter');?>" />
                </th>
                 <th>
	                <?php echo JText::_( 'Status' ); ?>
                </th>
            </tr>
            <tr>
                <th colspan="20" style="font-weight: normal;">
                    <div style="float: right; padding: 5px;"><?php echo @$this->pagination->getResultsCounter(); ?></div>
                    <div style="float: left;"><?php echo @$this->pagination->getListFooter(); ?></div>
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
				<td style="text-align: center;">
					<?php echo TiendaGrid::checkedout( $item, $i, 'id' ); ?>
				</td>
				<td style="text-align: center;">
					<a href="<?php echo $item->link; ?>">
						<?php echo $item->id; ?>
					</a>
				</td>	
				<td style="text-align: left;">
					<a href="<?php echo $item->link; ?>">
						<?php echo $item->name; ?>
					</a>
				</td>	
				<td style="text-align: center;">
					<?php echo $item->username; ?>
				</td>
				<td style="text-align: center;">
					<?php echo $item->email; ?>
				</td>
				<td style="text-align: center;">
					<?php $table = JTable::getInstance('UserGroups', 'TiendaTable'); ?>
					<?php
                    $keynames = array();
                    $keynames['user_id'] = $item->id;
                    $keynames['group_id'] = $row->group_id;
					?>
					<?php $table->load( $keynames ); ?>
					<?php echo TiendaGrid::enable(isset($table->user_id), $i, 'selected_'); ?>
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
	</table>
	

	<input type="hidden" name="task" id="task" value="selectusers" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
	
	<?php echo $this->form['validate']; ?>
</div>
</form>