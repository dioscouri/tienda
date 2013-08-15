<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">

    <?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>
    	
    <?php echo TiendaGrid::searchform(@$state->filter,JText::_('COM_TIENDA_SEARCH'), JText::_('COM_TIENDA_RESET') ) ?>

	<table class="table table-striped table-bordered" style="clear: both;">
		<thead>
            <tr>
                <th style="width: 5px;">
                	<?php echo JText::_('COM_TIENDA_NUM'); ?>
                </th>
                <th style="width: 20px;">
                	<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                </th>
                <th style="width: 50px;">
                	<?php echo TiendaGrid::sort( 'COM_TIENDA_ID', "tbl.address_id", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 50px;">
                	<?php echo TiendaGrid::sort( 'COM_TIENDA_USER_ID', "tbl.user_id", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: left;">
                	<?php echo JText::_('COM_TIENDA_USER'); ?>
                </th>
                <th style="text-align: left;">
    	            <?php echo JText::_('COM_TIENDA_ADDRESS'); ?>
                </th>
            </tr>
            <tr class="filterline">
                <th colspan="3">
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
                <th>
                    <input type="text" id="filter_userid" name="filter_userid" value="<?php echo @$state->filter_userid; ?>" size="15" class="input-mini" />
                </th>
                <th style="text-align: left;">
                    <input type="text" id="filter_user" name="filter_user" value="<?php echo @$state->filter_user; ?>" size="25" class="input-medium" />
                </th>
                <th style="text-align: left;">
                    <input type="text" id="filter_address" name="filter_address" value="<?php echo @$state->filter_address; ?>" size="25" class="input-medium" />
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
					<?php echo TiendaGrid::checkedout( $item, $i, 'address_id' ); ?>
				</td>
				<td style="text-align: center;">
					<a href="<?php echo $item->link; ?>">
						<?php echo $item->address_id; ?>
					</a>
				</td>	
				<td style="text-align: center;">
					<a href="<?php echo $item->link; ?>">
						<?php echo $item->user_id; ?>
					</a>
				</td>				
				<td style="text-align: left;">
					<?php
					if ($item->user_id > 0) {
					    $user = JFactory::getUser( $item->user_id );
					    echo $user->name . " [ " . $user->email . " ] ";
					} else {
					    echo JText::_( "COM_TIENDA_GUEST" );
					}
					?>
				</td>
				<td style="text-align: left;">
				    <a href="<?php echo $item->link; ?>">
                        <?php echo $item->getSummary(); ?>
                    </a>
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
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
	
	<?php echo $this->form['validate']; ?>
</form>