<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $row = @$this -> row; ?>

<table class="table table-striped table-bordered">
    <tbody>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ENABLE_PRODUCT_COMPARE'); ?>
            </th>
            <td style="width: 150px;"><?php  echo TiendaSelect::btbooleanlist( 'enable_product_compare', 'class="inputbox"', $this -> row -> get('enable_product_compare', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_PRODUCT_COMPARED_LIMIT'); ?>
            </th>
            <td style="width: 150px;"><input type="text" name="compared_products" value="<?php echo $this -> row -> get('compared_products', ''); ?>" />
            </td>
            <td><?php echo JText::_('COM_TIENDA_NUMBER_OF_PRODUCTS_THAT_CAN_BE_COMPARED_AT_ONCE'); ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_SHOW_ADD_TO_CART'); ?>
            </th>
            <td style="width: 150px;"><?php  echo TiendaSelect::btbooleanlist( 'show_addtocart_productcompare', 'class="inputbox"', $this -> row -> get('show_addtocart_productcompare', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_SHOW_AVERAGE_CUSTOMER_RATING'); ?>
            </th>
            <td style="width: 150px;"><?php  echo TiendaSelect::btbooleanlist( 'show_rating_productcompare', 'class="inputbox"', $this -> row -> get('show_rating_productcompare', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_SHOW_MANUFACTURER'); ?>
            </th>
            <td style="width: 150px;"><?php  echo TiendaSelect::btbooleanlist( 'show_manufacturer_productcompare', 'class="inputbox"', $this -> row -> get('show_manufacturer_productcompare', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_SHOW_PRODUCT_MODEL'); ?>
            </th>
            <td style="width: 150px;"><?php  echo TiendaSelect::btbooleanlist( 'show_model_productcompare', 'class="inputbox"', $this -> row -> get('show_model_productcompare', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_SHOW_PRODUCT_SKU'); ?>
            </th>
            <td style="width: 150px;"><?php  echo TiendaSelect::btbooleanlist( 'show_sku_productcompare', 'class="inputbox"', $this -> row -> get('show_sku_productcompare', '1')); ?>
            </td>
            <td></td>
        </tr>
    </tbody>
</table>
