<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('stylesheet', 'component.css', 'media/com_tienda/css/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php $row = @$this->row; ?>

<h1 style="margin-left: 2%; margin-top: 2%;"><?php echo JText::_('COM_TIENDA_SET_RATES_FOR'); ?>: <?php echo $row->tax_class_name; ?></h1>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

    <?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>

<div class="note" style="width: 96%; margin-left: auto; margin-right: auto;">
    <div style="float: left; font-size: 1.3em; font-weight: bold; height: 30px;"><?php echo JText::_('COM_TIENDA_ADD_A_NEW_TAX_RATE'); ?></div>
    <div style="float: right;">
        <input type="hidden" name="tax_class_id" value="<?php echo $row->tax_class_id; ?>" />
        <button onclick="document.getElementById('task').value='createrate'; document.adminForm.submit();"><?php echo JText::_('COM_TIENDA_CREATE_RATE'); ?></button>
    </div>
    <div class="reset"></div>
    <table class="adminlist">
        <thead>
            <tr>
                <th><?php echo JText::_('COM_TIENDA_GEOZONE'); ?></th>
                <th><?php echo JText::_('COM_TIENDA_PREDECESSOR'); ?></th>
                <th><?php echo JText::_('COM_TIENDA_DESCRIPTION'); ?></th>
                <th><?php echo JText::_('COM_TIENDA_RATE'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center;">
                    <?php echo TiendaSelect::geozone( '', 'geozone_id', 1 ); ?>
                </td>
                <td style="text-align: center;">
                    <?php echo TiendaSelect::taxratespredecessors( 0, 'level', 1 ); ?>
                </td>
                <td style="text-align: center;">
                    <input id="tax_rate_description" name="tax_rate_description" value="" />
                </td>
                <td style="text-align: center;">
                    <input id="tax_rate" name="tax_rate" value="" />
                </td>
            </tr>
        </tbody>
    </table>

 </div>    
 
 <div class="note_green" style="width: 96%; margin-left: auto; margin-right: auto;">
    <div style="float: left; font-size: 1.3em; font-weight: bold; height: 30px;"><?php echo JText::_('COM_TIENDA_CURRENT_TAX_RATES'); ?></div>
    <div style="float: right;">
        <input type="hidden" name="tax_class_id" value="<?php echo $row->tax_class_id; ?>" />
        <button onclick="document.adminForm.toggle.checked=true; checkAll(<?php echo count( @$items ); ?>); document.getElementById('task').value='saverates'; document.adminForm.submit();"><?php echo JText::_('COM_TIENDA_SAVE_ALL_CHANGES'); ?></button>
    </div>
    <table class="adminlist" style="clear: both;">
        <thead>
            <tr>
                <th style="width: 20px;">
                    <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                </th>
                <th style="width: 50px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_ID', "tbl.tax_rate_id", @$state->direction, @$state->order ); ?>
                </th>                
                <th style="text-align: left;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_GEO_ZONE', "tbl.geozone_id", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: left;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_LEVEL', "tbl.level", @$state->direction, @$state->order ); ?>
                </th>
                <th>
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_TAX_RATE_DESCRIPTION', "tbl.tax_rate_description", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_TAX_RATE', "tbl.tax_rate", @$state->direction, @$state->order ); ?>
                </th>
                <th>
                </th>
            </tr>
        </thead>
        <tbody>
        <?php $i=0; $k=0; ?>
        <?php foreach (@$items as $item) : ?>
            <tr class='row<?php echo $k; ?>'>
                <td style="text-align: center;">
                    <?php echo TiendaGrid::checkedout( $item, $i, 'tax_rate_id' ); ?>
                </td>
                <td style="text-align: center;">
                    <?php echo $item->tax_rate_id; ?>
                </td>   
                <td style="text-align: left;">
                    <?php echo JText::_( $item->geozone_name ); ?>
                </td>
                <td style="text-align: left;">
                    <?php echo $this->listRateLevels( $item->level, $item->tax_rate_id, $item->tax_class_id ); ?><br />
                    (<?php 
                    		echo implode( ' | ', $this->getAssociatedTaxRates( $item->level, $item->geozone_id, $item->tax_class_id ) ); 
                    ?>)
                </td>
                <td style="text-align: center;">
                    <input type="text" name="description[<?php echo $item->tax_rate_id; ?>]" value="<?php echo $item->tax_rate_description; ?>" />
                </td>
                <td style="text-align: center;">
                    <input type="text" name="rate[<?php echo $item->tax_rate_id; ?>]" value="<?php echo $item->tax_rate; ?>" />
                </td>
                <td style="text-align: center;">
                    [<a href="index.php?option=com_tienda&controller=taxrates&task=delete&cid[]=<?php echo $item->tax_rate_id; ?>&return=<?php echo base64_encode("index.php?option=com_tienda&controller=taxclasses&task=setrates&id={$row->tax_class_id}&tmpl=component"); ?>">
                        <?php echo JText::_('COM_TIENDA_DELETE_RATE'); ?>   
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
    <input type="hidden" name="id" value="<?php echo $row->tax_class_id; ?>" />
    <input type="hidden" name="task" id="task" value="setrates" />
    <input type="hidden" name="boxchecked" value="" />
    <input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
    <input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
    
    <?php echo $this->form['validate']; ?>
</div>
</form>