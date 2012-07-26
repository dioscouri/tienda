<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php Tienda::load( 'TiendaHelperBase', 'helpers._base' ); ?>
<?php $display_subnum = Tienda::getInstance()->get( 'display_subnum', 0 ); ?>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>
	
  <table>
        <tr>
            <td align="left" width="100%">
            </td>
            <td nowrap="nowrap">
                <input type="text" name="filter" value="<?php echo @$state->filter; ?>" />
                <button class="btn btn-primary" onclick="this.form.submit();"><?php echo JText::_('COM_TIENDA_SEARCH'); ?></button>
                <button class="btn btn-danger"onclick="tiendaFormReset(this.form);"><?php echo JText::_('COM_TIENDA_RESET'); ?></button>
            </td>
        </tr>
    </table>

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
                	<?php echo TiendaGrid::sort( 'COM_TIENDA_ID', "tbl.subscription_id", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 50px;">
                </th>
                <th style="width: 150px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_TYPE', "p.product_name", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 200px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_CREATED', "tbl.created_datetime", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 50px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_ORDER', "tbl.order_id", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: left;" <?php if( $display_subnum ) echo 'nowrap'; ?>>
                	<?php echo TiendaGrid::sort( 'COM_TIENDA_CUSTOMER', "u.name", @$state->direction, @$state->order ); ?>
                <?php if( $display_subnum ) : ?>
                    + <?php echo TiendaGrid::sort( 'COM_TIENDA_SUB_NUM', "tbl.sub_number", @$state->direction, @$state->order ); ?>
                <?php endif; ?>

                </th>
                <th style="width: 200px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_EXPIRES', "tbl.expires_datetime", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_TRANSACTION_ID', "tbl.transaction_id", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_ENABLED', "tbl.subscription_enabled", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_LIFETIME', "tbl.lifetime_enabled", @$state->direction, @$state->order ); ?>
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
                </th>
                <th>
                    <input id="filter_type" name="filter_type" value="<?php echo @$state->filter_type; ?>" size="25"/>
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
                    </div>
                </th>
                <th>
                    <input id="filter_orderid" name="filter_orderid" value="<?php echo @$state->filter_orderid; ?>" size="10"/>
                </th>
                <th style="text-align: left;">
                	<?php if( $display_subnum ) : ?>
                	<div class="range">
                    <div class="rangeline">
		                		<span class="label"><?php echo JText::_('COM_TIENDA_NAME_OR_ID')?></span>:
                	<?php endif; ?>
                	<input id="filter_user" name="filter_user" value="<?php echo @$state->filter_user; ?>" size="<?php echo $display_subnum ? '10' : '25' ?>"/>
                	<?php if( $display_subnum ) : ?>
  		              </div>
                    <div class="rangeline">
	                		<span class="label"><?php echo JText::_('COM_TIENDA_SUB_NUM')?></span>:
  		              	<input id="filter_subnum" name="filter_subnum" value="<?php echo @$state->filter_subnum; ?>" size="10"/>
  		              </div>
  		            </div>
                	<?php endif; ?>
                </th>
                <th>
                    <div class="range">
                        <div class="rangeline">
                            <span class="label"><?php echo JText::_('COM_TIENDA_FROM'); ?>:</span>
                            <?php echo JHTML::calendar( @$state->filter_date_from_expires, "filter_date_from_expires", "filter_date_from_expires", '%Y-%m-%d %H:%M:%S' ); ?>
                        </div>
                        <div class="rangeline">
                            <span class="label"><?php echo JText::_('COM_TIENDA_TO'); ?>:</span>
                            <?php echo JHTML::calendar( @$state->filter_date_to_expires, "filter_date_to_expires", "filter_date_to_expires", '%Y-%m-%d %H:%M:%S' ); ?>
                        </div>
                    </div>
                </th>
                <th>
                    <input id="filter_transaction" name="filter_transactionid" value="<?php echo @$state->filter_transactionid; ?>" size="10"/>
                </th>
                <th>
                    <?php echo TiendaSelect::booleans( @$state->filter_enabled, 'filter_enabled', $attribs, 'enabled', true, 'COM_TIENDA_ENABLED_STATE' ); ?>
                </th>
                <th>
                    <?php echo TiendaSelect::booleans( @$state->filter_lifetime, 'filter_lifetime', $attribs, 'lifetime', true, 'COM_TIENDA_LIFETIME_STATE' ); ?>
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
					<?php echo TiendaGrid::checkedout( $item, $i, 'subscription_id' ); ?>
				</td>
				<td style="text-align: center;">
					<a href="<?php echo $item->link_view; ?>">
						<?php echo $item->subscription_id; ?>
					</a>
				</td>
                <td style="text-align: center; width: 50px;">
                    <a href="<?php echo $item->link; ?>">
                        <img src="<?php echo Tienda::getURL('images').'page_edit.png' ?>" title="<?php echo JText::_('COM_TIENDA_EDIT'); ?>"/>
                    </a>
                </td>
                <td style="text-align: center;">
                    <a href="<?php echo $item->link_view; ?>">
                        <?php echo $item->product_name; ?>
                    </a>
                </td>
                <td style="text-align: center;">
                    <a href="<?php echo $item->link_view; ?>">
                        <?php echo JHTML::_('date', $item->created_datetime, Tienda::getInstance()->get('date_format')); ?>
                    </a>
                </td>
                <td style="text-align: center;">
                    <a href="<?php echo $item->link_view; ?>">
                        <?php echo $item->order_id; ?>
                    </a>
                </td>
				<td style="text-align: left;">
            <?php if( $display_subnum && strlen( $item->sub_number ) ) : ?>
            	<?php Tienda::load( 'TiendaHelperSubscription', 'helpers.subscription' ); ?>
            	<b><?php echo JText::_('COM_TIENDA_SUB_NUM'); ?>:</b> <?php echo TiendaHelperSubscription::displaySubNum( $item->sub_number ); ?><br />
            <?php endif; ?>
				    <?php if (!empty($item->user_name)) { ?>
    					<?php echo $item->user_name .' [ '.$item->user_id.' ]'; ?>
    					<br/>
    					&nbsp;&nbsp;&bull;&nbsp;&nbsp;<?php echo $item->email .' [ '.$item->user_username.' ]'; ?>
    					<br/>
					<?php } ?>
				</td>
                <td style="text-align: center;">
                    <a href="<?php echo $item->link_view; ?>">
                        <?php echo JHTML::_('date', $item->expires_datetime, Tienda::getInstance()->get('date_format')); ?>
                    </a>
                </td>
                <td style="text-align: center;">
                    <?php echo $item->transaction_id; ?>
                </td>
                <td style="text-align: center;">
                    <?php echo TiendaGrid::enable($item->subscription_enabled, $i, 'subscription_enabled.' ); ?>
                </td>
                <td style="text-align: center;">
                    <?php echo TiendaGrid::enable($item->lifetime_enabled, $i, 'lifetime_enabled.' ); ?>
                </td>
			</tr>
			<?php $i=$i+1; $k = (1 - $k); ?>
			<?php endforeach; ?>
			
			<?php if (!count(@$items)) : ?>
			<tr>
				<td colspan="20" align="center">
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
