<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
$form = @$this->form;
$row = @$this->row;
$helper_product = new TiendaHelperProduct();
?>
<table class="table table-bordered">
    <tr>
        <td class="dsc-key"><?php echo JText::_('COM_TIENDA_REQUIRES_SHIPPING'); ?>:</td>
        <td class="dsc-value"><?php // Make the shipping options div only display if yes ?>
            <div class="control-group">
                <div class="controls">
                    <fieldset id="shipoptions" class="radio btn-group">
                        <input class="input" type="radio" <?php if (empty($row->product_ships)) { echo "checked='checked'"; } ?> value="0" name="product_ships" id="product_ships0" /> <label onclick="tiendaShowHideDiv('shipping_options');" for="product_ships0"><?php echo JText::_('COM_TIENDA_NO'); ?> </label> <input class="input" type="radio" <?php if (!empty($row->product_ships)) { echo "checked='checked'"; } ?> value="1" name="product_ships" id="product_ships1" /><label onclick="tiendaShowHideDiv('shipping_options');" for="product_ships1"><?php echo JText::_('COM_TIENDA_YES'); ?> </label>
                    </fieldset>
                </div>
            </div>
        </td>
    </tr>
</table>

<?php // Only display if product ships ?>
<div id="shipping_options" style='width: 100%; <?php if (empty($row->product_ships)) { echo "display: none;"; } ?>' >
    <table class="table table-striped table-bordered" style="width: 100%;">
        <tr>
            <td style="width: 100px; text-align: right;" class="dsc-key"><label for="product_weight"> <?php echo JText::_('COM_TIENDA_WEIGHT'); ?>:
            </label>
            </td>
            <td><input type="text" name="product_weight" id="product_weight" value="<?php echo @$row->product_weight; ?>" size="30" maxlength="250" />
            </td>
        </tr>
        <tr>
            <td style="width: 100px; text-align: right;" class="dsc-key"><label for="product_length"> <?php echo JText::_('COM_TIENDA_LENGTH'); ?>:
            </label>
            </td>
            <td><input type="text" name="product_length" id="product_length" value="<?php echo @$row->product_length; ?>" size="30" maxlength="250" />
            </td>
        </tr>

        <tr>
            <td style="width: 100px; text-align: right;" class="dsc-key"><label for="product_width"> <?php echo JText::_('COM_TIENDA_WIDTH'); ?>:
            </label>
            </td>
            <td><input type="text" name="product_width" id="product_width" value="<?php echo @$row->product_width; ?>" size="30" maxlength="250" />
            </td>
        </tr>
        <tr>
            <td style="width: 100px; text-align: right;" class="dsc-key"><label for="product_height"> <?php echo JText::_('COM_TIENDA_HEIGHT'); ?>:
            </label>
            </td>
            <td><input type="text" name="product_height" id="product_height" value="<?php echo @$row->product_height; ?>" size="30" maxlength="250" />
            </td>
        </tr>
    </table>
</div>
