<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
$form = @$this->form;
$row = @$this->row;
$helper_product = new TiendaHelperProduct();
?>

<div class="note well">
    <?php echo JText::_('COM_TIENDA_ADVANCED_PANEL_NOTICE'); ?>
</div>

<div style="clear: both;"></div>

<div style="float: left; width: 50%;">
    <div class="well options">
        <legend>
            <?php echo JText::_('COM_TIENDA_PRODUCT_PARAMETERS'); ?>
        </legend>
        <table class="table table-striped table-bordered" style="width: 100%;">
            <tr>
                <td style="vertical-align: top; width: 100px; text-align: right;" class="dsc-key"><?php echo JText::_('COM_TIENDA_PRODUCT_PARAMS'); ?>:</td>
                <td><textarea name="product_params" id="product_params" rows="10" cols="55">
                        <?php echo @$row->product_params; ?>
                    </textarea>
                </td>
            </tr>
        </table>
    </div>
</div>

<div style="float: left; width: 50%;">
    <div class="well options">
        <legend>
            <?php echo JText::_('COM_TIENDA_SQL_FOR_AFTER_PURCHASE'); ?>
        </legend>
        <table class="table table-striped table-bordered" style="width: 100%;">
            <tr>
                <td title="<?php echo JText::_('COM_TIENDA_PRODUCT_SQL').'::'.JText::_('COM_TIENDA_PRODUCT_SQL_TIP'); ?>" style="width: 100px; text-align: right;" class="key hasTip"><?php echo JText::_('COM_TIENDA_PRODUCT_SQL'); ?>:</td>
                <td><textarea name="product_sql" rows="10" cols="55">
                        <?php echo @$row->product_sql; ?>
                    </textarea>
                </td>
            </tr>
            <tr>
                <td title="<?php echo JText::_('COM_TIENDA_AVAILABLE_OBJECTS').'::'.JText::_('COM_TIENDA_AVAILABLE_OBJECTS_TIP'); ?>" style="width: 100px; text-align: right;" class="key hasTip"><?php echo JText::_('COM_TIENDA_AVAILABLE_OBJECTS'); ?>:</td>
                <td>{user} = JFactory::getUser( <?php echo "$"."order->user_id"; ?> )<br /> {date} = JFactory::getDate()<br /> {request} = JRequest::getVar()<br /> {order} = TiendaTableOrders()<br /> {orderitem} = TiendaTableOrderItems()<br /> {product} = TiendaTableProducts()<br />
                </td>
            </tr>
            <tr>
                <td title="<?php echo JText::_('COM_TIENDA_NORMAL_USAGE').'::'.JText::_('COM_TIENDA_NORMAL_USAGE_TIP'); ?>" style="width: 100px; text-align: right;" class="key hasTip"><?php echo JText::_('COM_TIENDA_NORMAL_USAGE'); ?>:</td>
                <td><br /> <?php echo "{user.name} == JFactory::getUser()->name"; ?><br /> <?php echo "{user.username} == JFactory::getUser()->username"; ?><br /> <?php echo "{user.email} == JFactory::getUser()->email"; ?><br /> <?php echo "{date.toMySQL()} == JFactory::getDate()->toMySQL()"; ?><br /> <?php echo "{request.task} == JRequest::getVar('task');"; ?><br />
                </td>
            </tr>
        </table>
    </div>
</div>

<?php
// fire plugin event here to enable extending the form
JDispatcher::getInstance()->trigger('onDisplayProductFormAdvanced', array( $row ) );
?>

<div style="clear: both;"></div>
