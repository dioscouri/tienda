<?php defined('_JEXEC') or die('Restricted access');
	JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
	$state = @$this->state;
	$form = @$this->form;
	$items = @$this->items;
	Tienda::load( 'TiendaHelperSubscription', 'helpers.subscription' );
	$display_subnum = Tienda::getInstance()->get( 'display_subnum', 0 ); 
	$create_user_link = "index.php?option=com_users&task=add";
	if (version_compare(JVERSION, '1.6.0', 'ge'))
		$create_user_link = "index.php?option=com_users&task=user.add";
?>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>
	
   
    <?php $button = "<input type='button' class='btn btn-success pull-left' value='".JText::_('COM_TIENDA_CREATE_NEW_USER')."' />";
                	echo TiendaUrl::popup( $create_user_link, $button, array('update' => true) );
                ?>
     <?php echo TiendaGrid::searchform(@$state->filter,JText::_('COM_TIENDA_SEARCH'), JText::_('COM_TIENDA_RESET') ) ?>
	

	<table class="table table-striped table-bordered" style="clear: both;">
		<thead>
            <tr>
                <th style="width: 5px;">
                	<?php echo JText::_('COM_TIENDA_NUM'); ?>
                </th>
                <th style="width: 50px;">
                	<?php echo TiendaGrid::sort( 'COM_TIENDA_ID', "tbl.id", @$state->direction, @$state->order ); ?>
                </th>                
                <?php if( $display_subnum ): ?>
                <th style="width: 70px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_SUB_NUM', "ui.sub_number", @$state->direction, @$state->order ); ?>
                </th>
                <?php endif; ?>
                <th style="text-align: left;">
                	<?php echo TiendaGrid::sort( 'COM_TIENDA_NAME', "tbl.name", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: center;">
                	<?php echo TiendaGrid::sort( 'COM_TIENDA_USERNAME', "tbl.username", @$state->direction, @$state->order ); ?>
                </th>
				<th style="text-align: center;">
					<?php echo TiendaGrid::sort( 'COM_TIENDA_EMAIL', 'tbl.email', @$state->direction, @$state->order); ?>
				</th>
                <th>
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_GROUP', 'group.group_name', @$state->direction, @$state->order); ?>
                </th>
				<th>
				</th>
				<th>
				</th>
            </tr>
            <tr class="filterline">
                <th colspan="2">
                    <?php $attribs = array('class' => 'inputbox', 'size' => '1', 'onchange' => 'document.adminForm.submit();'); ?>
                   <div class="range">
                        <div class="rangeline">
                            <input type="text" placeholder="<?php echo JText::_('COM_TIENDA_FROM'); ?>" id="filter_id_from" name="filter_id_from" value="<?php echo @$state->filter_id_from; ?>" size="5" class="input input-tiny" />
                        </div>
                        <div class="rangeline">
                            <input type="text" placeholder="<?php echo JText::_('COM_TIENDA_TO'); ?>" id="filter_id_to" name="filter_id_to" value="<?php echo @$state->filter_id_to; ?>" size="5" class="input input-tiny" />
                        </div>
                    </div>
                </th>
                <?php if( $display_subnum ): ?>
                <th>
                    <input id="filter_subnum" type="text" name="filter_subnum" value="<?php echo @$state->filter_subnum; ?>" size="10"/>
                </th>
                <?php endif; ?>
                <th style="text-align: left;">
                    <input id="filter_name" type="text" name="filter_name" value="<?php echo @$state->filter_name; ?>" size="25"/>
                </th>
                <th>
                    <input id="filter_username" type="text" name="filter_username" value="<?php echo @$state->filter_username; ?>" size="25"/>
                </th>
                <th>
                    <input id="filter_email" type="text" name="filter_email" value="<?php echo @$state->filter_email; ?>" size="25"/>
                </th>
                <th>
                    <?php echo TiendaSelect::groups(@$state->filter_group, 'filter_group', $attribs, 'filter_group', true ); ?>
                </th>
                <th>
                </th>
                <th>
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
					<a href="<?php echo $item->link; ?>">
						<?php echo $item->id; ?>
					</a>
				</td>	
        <?php if( $display_subnum ): ?>
        <td style="text-align: center;">
        	<?php echo TiendaHelperSubscription::displaySubNum( $item->sub_number ); ?>
        </td>
        <?php endif; ?>
				<td style="text-align: left;">
					<a href="<?php echo $item->link; ?>">
						<?php echo $item->name; ?>
					</a>
				</td>	
				<td style="text-align: center;">
					<?php echo $item->username; ?>
				</td>
				<td style="text-align: center;">
					<?php echo @$item->email; ?>
				</td>
                <td style="text-align: center;">
                    <?php echo $item->group_name; ?>
                </td>
				<td style="text-align: center;">
					[
					<a href="<?php echo $item->link; ?>">
						<?php echo JText::_('COM_TIENDA_VIEW_DASHBOARD'); ?>
					</a>
					]
				</td>
                <td style="text-align: center;">
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
	</table>

	<input type="hidden" name="order_change" value="0" />
	<input type="hidden" name="id" value="" />
	<input type="hidden" name="task" id="task" value="" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
	
	<?php echo $this->form['validate']; ?>
</form>