<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $row = @$this -> row; ?>

<table class="table table-striped table-bordered">
    <tbody>
        <tr>
            <th class="dsc-key">
                <?php echo JText::_( 'COM_TIENDA_INCLUDE_SITE_CSS' ); ?>
            </th>
            <td class="dsc-value">
                <?php echo TiendaSelect::btbooleanlist( 'include_site_css', 'class="inputbox"', $this->row->get('include_site_css', '1') ); ?>
            </td>
            <td>
                
            </td>
        </tr>    
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_USE_BOOTSTRAP'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('use_bootstrap', 'class="inputbox"', $this -> row -> get('use_bootstrap', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DISPLAY_FRONT_END_SUBMENU'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('show_submenu_fe', 'class="inputbox"', $this -> row -> get('show_submenu_fe', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DISPLAY_OUT_OF_STOCK_PRODUCTS'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('display_out_of_stock', 'class="inputbox"', $this -> row -> get('display_out_of_stock', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DISPLAY_ROOT_CATEGORY_IN_JOOMLA_BREADCRUMB'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('include_root_pathway', 'class="inputbox"', $this -> row -> get('include_root_pathway', '0')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DISPLAY_TIENDA_BREADCRUMB'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('display_tienda_pathway', 'class="inputbox"', $this -> row -> get('display_tienda_pathway', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DISPLAY_PRODUCT_QUANTITY'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('display_product_quantity', 'class="inputbox"', $this -> row -> get('display_product_quantity', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DISPLAY_RELATED_ITEMS'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('display_relateditems', 'class="inputbox"', $this -> row -> get('display_relateditems', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DISPLAY_ASK_A_QUESTION_ABOUT_THIS_PRODUCT'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('ask_question_enable', 'class="inputbox"', $this -> row -> get('ask_question_enable', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ENABLE_CAPTCHA_ON_ASK_A_QUESTION_ABOUT_THIS_PRODUCT'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('ask_question_showcaptcha', 'class="inputbox"', $this -> row -> get('ask_question_showcaptcha', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ASK_A_QUESTION_ABOUT_THIS_PRODUCT_IN_MODAL'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('ask_question_modal', 'class="inputbox"', $this -> row -> get('ask_question_modal', '1')); ?>
            </td>
            <td>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DISPLAY_PRODUCT_PRICES_WITH_TAX'); ?>
            </th>
            <td><?php echo TiendaSelect::displaywithtax($this -> row -> get('display_prices_with_tax', '0'), 'display_prices_with_tax'); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_NUMBER_OF_SUBCATEGORIES_PER_LINE'); ?>
            </th>
            <td><input type="text" name="subcategories_per_line" id="subcategories_per_line" value="<?php echo $this -> row -> get('subcategories_per_line', 5); ?>" />
            </td>
            <td><?php echo JText::_('COM_TIENDA_NUMBER_OF_SUBCATEGORIES_PER_LINE_DESC'); ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DISPLAY_PRODUCT_PRICES_WITH_LINK_TO_SHIPPING_COSTS_ARTICLE'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('display_prices_with_shipping', 'class="inputbox"', $this -> row -> get('display_prices_with_shipping', '0')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_SHIPPING_COSTS_ARTICLE'); ?>
            </th>
            <td style="width: 280px;"><?php echo $this -> elementArticle_shipping; ?> <?php echo $this -> resetArticle_shipping; ?>
            </td>
            <td><?php echo JText::_('COM_TIENDA_ARTICLE_FOR_SHIPPING_COSTS_DESC'); ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ADD_TO_CART_ACTION'); ?>
            </th>
            <td><?php echo TiendaSelect::addtocartaction($this -> row -> get('addtocartaction', 'lightbox'), 'addtocartaction'); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DISPLAY_ADD_TO_CART_BUTTON_IN_CATEGORY_LISTINGS'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('display_category_cartbuttons', 'class="inputbox"', $this -> row -> get('display_category_cartbuttons', '1')); ?>
            </td>
            <td></td>
        </tr>
        <!--  Add Display Add to Cart Button in Product -->
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DISPLAY_ADD_TO_CART_BUTTON_IN_PRODUCT'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('display_product_cartbuttons', 'class="inputbox"', $this -> row -> get('display_product_cartbuttons', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_SELECT_CART_BUTTON_TYPE'); ?>
            </th>
            <td><?php echo TiendaSelect::cartbutton($this -> row -> get('cartbutton', 'button'), 'cartbutton'); ?>
            </td>
            <td></td>
        </tr>
    </tbody>
</table>
