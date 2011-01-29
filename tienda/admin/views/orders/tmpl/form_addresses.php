<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; ?>
<?php $order = @$this->order; ?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >

    <h3><?php echo JText::_('Edit Addresses for Order') . " " . $row->order_id; ?></h3>
    
    <fieldset style="width: 48%; float: left;">
        <legend><?php echo JText::_( "Billing Address" ); ?></legend>
        <table class="admintable">
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Company' ); ?>:
                </td>
                <td>
                    <input type="text" name="billing_company" value="<?php echo @$row->billing_company; ?>" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Line 1' ); ?>:
                </td>
                <td>
                    <input type="text" name="billing_address_1" value="<?php echo @$row->billing_address_1; ?>" size="48" maxlength="250" />
                </td>
            </tr>
        </table>
    </fieldset>
    
    <fieldset style="width: 48%; float: left;">
        <legend><?php echo JText::_( "Shipping Address" ); ?></legend>
        <table class="admintable">
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Company' ); ?>:
                </td>
                <td>
                    <input type="text" name="shipping_company" value="<?php echo @$row->shipping_company; ?>" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Line 1' ); ?>:
                </td>
                <td>
                    <input type="text" name="shipping_address_1" value="<?php echo @$row->shipping_address_1; ?>" size="48" maxlength="250" />
                </td>
            </tr>
        </table>
    </fieldset>

    <input type="hidden" name="id" value="<?php echo @$row->order_id; ?>" />
    <input type="hidden" name="task" id="task" value="saveAddresses" />
</form>

<div style="clear: both;"></div>
<?php echo Tienda::dump($row); ?>