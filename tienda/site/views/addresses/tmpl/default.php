<?php
	defined('_JEXEC') or die('Restricted access');
	JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
	JHTML::_('stylesheet', 'menu.css', 'media/com_tienda/css/');
	JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
	$state = @$this->state;
	$form = @$this->form;
	$items = @$this->items;
	$tmpl = @$this->tmpl;
	$menu = TiendaMenu::getInstance();
?>

<div class='componentheading'>
    <span><?php echo JText::_('COM_TIENDA_MANAGE_YOUR_ADDRESSES'); ?></span>
</div>

    <?php if ($menu && $tmpl == '') { $menu->display(); } ?>


<form action="<?php echo JRoute::_( @$form['action'].$tmpl )?>" method="post" name="adminForm" enctype="multipart/form-data">

    <?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>
    
    <table>
        <tr>
            <td align="left" width="100%">
                <?php $attribs = array('class' => 'inputbox', 'size' => '1', 'onchange' => "document.getElementById('task').value=this.options[this.selectedIndex].value; document.adminForm.submit();"); ?>
                <?php echo TiendaSelect::addressaction( '', 'apply_action', $attribs, 'apply_action', true, false, 'COM_TIENDA_SELECT_ACTION' ); ?>
            </td>
            <td nowrap="nowrap">
                <a href="<?php echo JRoute::_("index.php?option=com_tienda&view=addresses&task=edit".$tmpl); ?>">
                    <?php echo JText::_('COM_TIENDA_ENTER_A_NEW_ADDRESS'); ?>
                </a>
            </td>
        </tr>
    </table>
    
    <table class="adminlist" style="clear: both;">
        <thead>
            <tr>
                <th style="width: 20px;">
                    <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                </th>
                <th style="text-align: center;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_NAME', "tbl.address_name", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: left;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_ADDRESS', "tbl.address_1", @$state->direction, @$state->order ); ?>
                </th>
                <th>
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
                    <?php echo TiendaGrid::checkedout( $item, $i, 'address_id' ); ?>
                </td>
                <td style="text-align: center;">
                    <a href="<?php echo JRoute::_( $item->link.$tmpl ); ?>">
                        <?php echo $item->address_name; ?>
                    </a>
                </td>
                <td style="text-align: left;">
                    <?php // TODO Use sprintf to enable formatting?  How best to display addresses? ?>
                    <!-- ADDRESS -->
                    <b><?php echo @$item->first_name; ?> <?php echo @$item->middle_name; ?> <?php echo @$item->last_name; ?></b><br/>
                    <?php if (!empty($item->company)) { echo $item->company; ?><br/><?php } ?>
                    <?php echo $item->address_1; ?><br/>
                    <?php if (!empty($item->address_2)) { echo $item->address_2; ?><br/><?php } ?>
                    <?php echo @$item->city; ?>, <?php echo @$item->zone_name; ?> <?php echo @$item->postal_code; ?><br/>
                    <?php echo @$item->country_name; ?><br/>
                    <!-- PHONE NUMBERS -->
                    <?php // if ($item->phone_1 || $item->phone_2 || $item->fax) { echo "<hr/>"; } ?>
                    <?php if (!empty($item->phone_1)) { echo "&nbsp;&bull;&nbsp;<b>".JText::_('COM_TIENDA_PHONE')."</b>: ".$item->phone_1; ?><br/><?php } ?>
                    <?php if (!empty($item->phone_2)) { echo "&nbsp;&bull;&nbsp;<b>".JText::_('COM_TIENDA_ALT_PHONE')."</b>: ".$item->phone_2; ?><br/><?php } ?>
                    <?php if (!empty($item->fax)) { echo "&nbsp;&bull;&nbsp;<b>".JText::_('COM_TIENDA_FAX')."</b>: ".$item->fax; ?><br/><?php } ?>
                </td>
                <td style="text-align: center;">
                    <?php if ($item->is_default_shipping && $item->is_default_billing)
                    {
                        echo JText::_('COM_TIENDA_DEFAULT_BILLING_AND_SHIPPING_ADDRESS');
                    }
                    elseif ($item->is_default_shipping) 
                    {
                    	echo JText::_('COM_TIENDA_DEFAULT_SHIPPING_ADDRESS');
                    }
                    elseif ($item->is_default_billing) 
                    {
                    	echo JText::_('COM_TIENDA_DEFAULT_BILLING_ADDRESS');
                    }
                    ?>
                </td>
                <td style="text-align: center;">
                    <a href="<?php echo JRoute::_( $item->link.$tmpl ); ?>">
                        <?php echo JText::_( "COM_TIENDA_EDIT" ); ?>
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
        <tfoot>
            <tr>
                <td colspan="20">
                    <?php echo @$this->pagination->getListFooter(); ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <input type="hidden" name="order_change" value="0" />
    <input type="hidden" name="boxchecked" value="" />
    <input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
    <input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
    <input type="hidden" name="task" id="task" value="" />
    <?php echo $this->form['validate']; ?>
</form>