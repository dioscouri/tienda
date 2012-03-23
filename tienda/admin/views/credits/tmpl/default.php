<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>
	
    <table>
        <tr>
            <td align="left" width="100%">
            </td>
            <td nowrap="nowrap">
                <input name="filter" value="<?php echo @$state->filter; ?>" />
                <button onclick="this.form.submit();"><?php echo JText::_('COM_TIENDA_SEARCH'); ?></button>
                <button onclick="tiendaFormReset(this.form);"><?php echo JText::_('COM_TIENDA_RESET'); ?></button>
            </td>
        </tr>
    </table>

	<table class="adminlist" style="clear: both;">
		<thead>
            <tr>
                <th style="width: 5px;">
                	<?php echo JText::_('Num'); ?>
                </th>
                <th style="width: 20px;">
                	<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                </th>
                <th style="width: 50px;">
                	<?php echo TiendaGrid::sort( 'ID', "tbl.credit_id", @$state->direction, @$state->order ); ?>
                </th>                
                <th style="text-align: left;">
                    <?php echo TiendaGrid::sort( 'User', "u.name", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'Type', "tbl.credittype_code", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'Amount', "tbl.credit_amount", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'Created', "tbl.created_date", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'Enabled', "tbl.credit_enabled", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'Withdrawable', "tbl.credit_withdrawable", @$state->direction, @$state->order ); ?>
                </th>
            </tr>
            <tr class="filterline">
                <th colspan="3">
                	<?php $attribs = array('class' => 'inputbox', 'size' => '1', 'onchange' => 'document.adminForm.submit();'); ?>
                	<div class="range">
	                	<div class="rangeline">
	                		<span class="label"><?php echo JText::_('COM_TIENDA_FROM'); ?>:</span> <input id="filter_id_from" name="filter_id_from" value="<?php echo @$state->filter_id_from; ?>" size="5" class="input" />
	                	</div>
	                	<div class="rangeline">
	                		<span class="label"><?php echo JText::_('COM_TIENDA_TO'); ?>:</span> <input id="filter_id_to" name="filter_id_to" value="<?php echo @$state->filter_id_to; ?>" size="5" class="input" />
	                	</div>
                	</div>
                </th>                
                <th style="text-align: left;">
                    <input id="filter_user" name="filter_user" value="<?php echo @$state->filter_user; ?>" size="25"/>
                </th>
                <th>
                </th>
                <th>
                    <div class="range">
                        <div class="rangeline">
                            <span class="label"><?php echo JText::_('COM_TIENDA_FROM'); ?>:</span> <input id="filter_amount_from" name="filter_amount_from" value="<?php echo @$state->filter_amount_from; ?>" size="5" class="input" />
                        </div>
                        <div class="rangeline">
                            <span class="label"><?php echo JText::_('COM_TIENDA_TO'); ?>:</span> <input id="filter_amount_to" name="filter_amount_to" value="<?php echo @$state->filter_amount_to; ?>" size="5" class="input" />
                        </div>
                    </div>
                </th>
                <th>
                    <div class="range">
                        <div class="rangeline">
                            <span class="label"><?php echo JText::_('COM_TIENDA_FROM'); ?>:</span>
                            <?php echo JHTML::calendar( @$state->filter_date_from, "filter_date_from", "filter_date_from", '%Y-%m-%d %H:%M:%S' ); ?>
                        </div>
                        <div class="rangeline">
                            <span class="label"><?php echo JText::_('COM_TIENDA_TO'); ?>:</span>
                            <?php echo JHTML::calendar( @$state->filter_date_to, "filter_date_to", "filter_date_to", '%Y-%m-%d %H:%M:%S' ); ?>
                        </div>
                        <div class="rangeline">
                            <span class="label"><?php echo JText::_('Type'); ?>:</span>
                            <?php echo TiendaSelect::datetype( @$state->filter_datetype, 'filter_datetype', '', 'datetype' ); ?>
                        </div>
                    </div>
                </th>
                <th>
                    <?php echo TiendaSelect::booleans(@$state->filter_enabled, 'filter_enabled', $attribs, 'filter_enabled', true ); ?>
                </th>
                <th>
                    <?php echo TiendaSelect::booleans(@$state->filter_withdraw, 'filter_withdraw', $attribs, 'filter_withdraw', true ); ?>
                </th>
            </tr>
			<tr>
				<th colspan="20" style="font-weight: normal;">
					<div style="float: right; padding: 5px;"><?php echo @$this->pagination->getResultsCounter(); ?></div>
					<div style="float: left;"><?php echo @$this->pagination->getListFooter(); ?></div>
				</th>
			</tr>
		</thead>
        <tbody>
		<?php $i=0; $k=0; ?>
        <?php foreach (@$items as $item) : ?>
            <tr class='row<?php echo $k; ?>'>
				<td align="center">
					<?php echo $i + 1; ?>
				</td>
				<td style="text-align: center;">
					<?php echo TiendaGrid::checkedout( $item, $i, 'credit_id' ); ?>
				</td>
				<td style="text-align: center;">
					<a href="<?php echo $item->link; ?>">
						<?php echo $item->credit_id; ?>
					</a>
				</td>
                <td style="text-align: left;">
                    <?php if (!empty($item->user_name)) { ?>
                        <a href="<?php echo $item->link; ?>">
                        <?php echo $item->user_name .' [ '.$item->user_id.' ]'; ?>
                        </a>
                        <br/>
                        &nbsp;&nbsp;&bull;&nbsp;&nbsp;
                        <a href="<?php echo $item->link; ?>">
                        <?php echo $item->email .' [ '.$item->user_username.' ]'; ?>
                        </a>
                        <br/>
                    <?php } ?>
                    
                    <?php
                    if (!empty($item->credit_enabled))
                    {
                        echo "<b>" . JText::_('Balance Before') . ":</b> " . TiendaHelperBase::currency( $item->credit_balance_before ). "<br/>";
                        echo "<b>" . JText::_('Balance After') . ":</b> " . TiendaHelperBase::currency( $item->credit_balance_after );
                    }
                    ?>
                    
                    <?php
                    if (!empty($item->credit_withdrawable))
                    {
                        echo "<br/>";
                        echo "<b>" . JText::_('Withdrawable Balance After') . ":</b> " . TiendaHelperBase::currency( $item->withdrawable_balance_after );
                    }
                    ?>
                </td>
                <td style="text-align: center;">
                    <a href="<?php echo $item->link; ?>">
                        <?php echo JText::_( $item->credittype_name ); ?>
                    </a>
                </td>
				<td style="text-align: center;">
					<h2><?php echo TiendaHelperBase::currency( $item->credit_amount ); ?></h2>
				</td>
                <td style="text-align: center;">
                   <?php echo JHTML::_('date', $item->created_date, TiendaConfig::getInstance()->get('date_format')); ?>
                </td>
                <td style="text-align: center;">
                    <?php echo TiendaGrid::boolean( $item->credit_enabled ); ?>
                </td>
                <td style="text-align: center;">
                    <?php echo TiendaGrid::boolean( $item->credit_withdrawable ); ?>
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
	<input type="hidden" name="id" value="" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
	
	<?php echo $this->form['validate']; ?>
</form>