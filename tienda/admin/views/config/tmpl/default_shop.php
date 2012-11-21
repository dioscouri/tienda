<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $row = @$this -> row; ?>

<table class="table table-striped table-bordered">
    <tbody>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ENABLE_SHOPPING'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('shop_enabled', '' , $this -> row -> get('shop_enabled', '1')) ; ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_SHOP_NAME'); ?>
            </th>
            <td><input type="text" name="shop_name" value="<?php echo $this -> row -> get('shop_name', ''); ?>" size="25" />
            </td>
            <td><?php echo JText::_('COM_TIENDA_THE_NAME_OF_THE_SHOP'); ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_COMPANY_NAME'); ?>
            </th>
            <td><input type="text" name="shop_company_name" value="<?php echo $this -> row -> get('shop_company_name', ''); ?>" size="25" />
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ADDRESS_LINE_1'); ?>
            </th>
            <td><input type="text" name="shop_address_1" value="<?php echo $this -> row -> get('shop_address_1', ''); ?>" size="35" />
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ADDRESS_LINE_2'); ?>
            </th>
            <td><input type="text" name="shop_address_2" value="<?php echo $this -> row -> get('shop_address_2', ''); ?>" size="35" />
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_CITY'); ?>
            </th>
            <td><input type="text" name="shop_city" value="<?php echo $this -> row -> get('shop_city', ''); ?>" size="25" />
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_COUNTRY'); ?>
            </th>
            <td><?php
            // TODO Change this to use a task within the checkout controller rather than creating a new zones controller
            $url = "index.php?option=com_tienda&format=raw&controller=addresses&task=getzones&name=shop_zone&country_id=";
            $attribs = array('onchange' => 'tiendaDoTask( \'' . $url . '\'+document.getElementById(\'shop_country\').value, \'zones_wrapper\', \'\');');
            echo TiendaSelect::country($this -> row -> get('shop_country', ''), 'shop_country', $attribs, 'shop_country', true);
            ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_STATE_REGION'); ?>
            </th>
            <td>
                <div id="zones_wrapper">
                    <?php
                    $shop_zone = $this -> row -> get('shop_zone', '');
                    if (empty($shop_zone)) {
                        echo JText::_('COM_TIENDA_SELECT_COUNTRY_FIRST');
                    } else {
                        echo TiendaSelect::zone($shop_zone, 'shop_zone', $this -> row -> get('shop_country', ''));
                    }
                    ?>
                </div>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_POSTAL_CODE'); ?>
            </th>
            <td><input type="text" name="shop_zip" value="<?php echo $this -> row -> get('shop_zip', ''); ?>" />
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_TAX_NUMBER_1'); ?>
            </th>
            <td><input type="text" name="shop_tax_number_1" value="<?php echo $this -> row -> get('shop_tax_number_1', ''); ?>" size="25" />
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_TAX_NUMBER_2'); ?>
            </th>
            <td><input type="text" name="shop_tax_number_2" value="<?php echo $this -> row -> get('shop_tax_number_2', ''); ?>" size="25" />
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_PHONE'); ?>
            </th>
            <td><input type="text" name="shop_phone" value="<?php echo $this -> row -> get('shop_phone', ''); ?>" />
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_SHOP_OWNER_NAME'); ?>
            </th>
            <td><input type="text" name="shop_owner_name" value="<?php echo $this -> row -> get('shop_owner_name', ''); ?>" size="35" />
            </td>
            <td></td>
        </tr>

    </tbody>
</table>
