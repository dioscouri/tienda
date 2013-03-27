<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php Tienda::load( "TiendaHelperPlugin", 'helpers.plugin' );?>

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
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_ID', "tbl.geozone_id", @$state->direction, @$state->order ); ?>
                </th>                
                <th style="text-align: left;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_NAME', "tbl.geozone_name", @$state->direction, @$state->order ); ?>
                </th>
                <th>
                    <?php echo JText::_('COM_TIENDA_ASSIGNED_ZONES'); ?>
                </th>
                <th>
                    <?php echo JText::_('COM_TIENDA_ASSIGNED_PAYMENT_SHIPPING'); ?>
                </th>
                <th>
                    <?php echo JText::_('COM_TIENDA_TYPE'); ?>
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
                <th style="text-align: left;">
                    <input id="filter_name" name="filter_name" value="<?php echo @$state->filter_name; ?>" size="25"/>
                </th>
                <th>
                </th>
                <th>
                </th>
                <th>
                    <?php echo TiendaSelect::geozonetypes( @$state->filter_geozonetype, 'filter_geozonetype', $attribs, 'geozonetype', true ); ?>
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
                    <?php echo TiendaGrid::checkedout( $item, $i, 'geozone_id' ); ?>
                </td>
                <td style="text-align: center;">
                    <a href="<?php echo $item->link; ?>">
                        <?php echo $item->geozone_id; ?>
                    </a>
                </td>    
                <td style="text-align: left;">
                    <a href="<?php echo $item->link; ?>">
                        <?php echo JText::_($item->geozone_name); ?>
                    </a>
                    <br/>
                    <?php echo $item->geozone_description; ?>
                </td>
                <td style="text-align: center;">
                    <?php echo JText::_('COM_TIENDA_ZONES_ASSIGNED'); ?>:
                    <?php $model = DSCModel::getInstance( 'Zonerelations', 'TiendaModel' ); ?>
                    <?php $model->setState( 'filter_geozoneid', $item->geozone_id); ?>
                    <?php echo $model->getTotal(); ?>
                    <br/>
                    [<?php echo TiendaUrl::popup( @$item->link_zones, JText::_('COM_TIENDA_SELECT_ZONES'), array('update' => true) ); ?>]
                </td>
                <td style="text-align: center;">
                	<?php $text = '';?>
                	<?php if($item->geozonetype_id == 1):?>
                		<?php echo JText::_('COM_TIENDA_PAYMENTS_ASSIGNED');?>
                		<?php $text = JText::_('COM_TIENDA_SELECT_PAYMENTS');?>
                	<?php elseif($item->geozonetype_id == 2):?>
                		<?php echo JText::_('COM_TIENDA_SHIPPINGS_ASSIGNED');?>
                		<?php $text = JText::_('COM_TIENDA_SELECT_SHIPPINGS');?>
                	<?php endif;?>:                	
                    <?php echo TiendaHelperPlugin::countPlgtoGeozone($item); ?>
                    <br/>
                    [<?php echo TiendaUrl::popup( @$item->link_plugins, $text, array('update' => true) ); ?>]
                </td>
                <td style="text-align: center;">
                    <?php echo $item->geozonetype_name; ?>
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