<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $row = @$this -> row; ?>

<table class="table table-striped table-bordered">
    <tbody>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ONE_PAGE_CHECKOUT'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('one_page_checkout', '' ,  $this -> row -> get('one_page_checkout', '0')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ONE_PAGE_CHECKOUT_LAYOUT'); ?>
            </th>
            <td><?php
            echo TiendaSelect::opclayouts($this -> row -> get('one_page_checkout_layout', 'onepagecheckout'), 'one_page_checkout_layout');
            ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ENABLE_TOOLTIPS_ONE_PAGE_CHECKOUT'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('one_page_checkout_tooltips_enabled', '' , $this -> row -> get('one_page_checkout_tooltips_enabled', '0')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_FORCE_SSL_ON_CHECKOUT'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('force_ssl_checkout', '' , $this -> row -> get('force_ssl_checkout', '0')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_REQUIRE_ACCEPTANCE_OF_TERMS_ON_CHECKOUT'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('require_terms', 'class="inputbox"', $this -> row -> get('require_terms', '0')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_TERMS_AND_CONDITIONS_ARTICLE'); ?>
            </th>
            <td style="width: 280px;"><?php echo $this -> elementArticle_terms; ?> <?php echo $this -> resetArticle_terms; ?>
            </td>
            <td><?php echo JText::_('COM_TIENDA_ARTICLE_FOR_TERMS_AND_CONDITIONS_DESC'); ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_NO_ZONES_COUNTRIES'); ?>
            </th>
            <td style="width: 280px;"><input type="text" name="ignored_countries" value="<?php echo $this -> row -> get('ignored_countries', ''); ?>" class="inputbox" />
            </td>
            <td><?php echo JText::_('COM_TIENDA_COUNTRIES_THAT_WILL_BE_IGNORED_WHEN_VALIDATING_THE_ZONES_DURING_CHECKOUT_DESC'); ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_SHOW_TAXES'); ?>
            </th>
            <td><?php echo TiendaSelect::taxdisplaycheckout($this -> row -> get('show_tax_checkout', '3'), 'show_tax_checkout'); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_SHOW_SHIPPING_TAX_ON_ORDER_INVOICES_AND_CHECKOUT'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('display_shipping_tax', 'class="inputbox"', $this -> row -> get('display_shipping_tax', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_INITIAL_ORDER_STATE'); ?>
            </th>
            <td><?php echo TiendaSelect::orderstate($this -> row -> get('initial_order_state', '15'), 'initial_order_state'); ?>
            </td>
            <td><?php echo JText::_('COM_TIENDA_INITIAL_ORDER_STATE_DESC'); ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_PENDING_ORDER_STATE'); ?>
            </th>
            <td><?php echo TiendaSelect::orderstate($this -> row -> get('pending_order_state', '1'), 'pending_order_state'); ?>
            </td>
            <td><?php echo JText::_('COM_TIENDA_PENDING_ORDER_STATE_DESC'); ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DEFAULT_SHIPPING_METHOD'); ?>
            </th>
            <td><?php echo TiendaSelect::shippingtype($this -> row -> get('defaultShippingMethod', '2'), 'defaultShippingMethod'); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ENABLE_GUEST_CHECKOUT'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('guest_checkout_enabled', 'class="inputbox"', $this -> row -> get('guest_checkout_enabled', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ORDER_NUMBER_PREFIX'); ?>
            </th>
            <td><input type="text" name="order_number_prefix" value="<?php echo $this -> row -> get('order_number_prefix', ''); ?>" class="inputbox" size="10" />
            </td>
            <td><?php echo JText::_('COM_TIENDA_ORDER_NUMBER_PREFIX_DESC'); ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_GLOBAL_HANDLING_COST'); ?>
            </th>
            <td><input type="text" name="global_handling" value="<?php echo $this -> row -> get('global_handling', ''); ?>" class="inputbox" size="10" />
            </td>
            <td><?php echo JText::_('COM_TIENDA_GLOBAL_HANDLING_COST_DESC'); ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ARTICLE_TO_DISPLAY_AFTER_SUCCESSFUL_CHECKOUT'); ?>
            </th>
            <td style="width: 280px;"><?php echo $this -> elementArticleModel -> _fetchElement('article_checkout', $this -> row -> get('article_checkout')); ?> <?php echo $this -> elementArticleModel -> _clearElement('article_checkout', '0'); ?>
            </td>
            <td><?php echo JText::_('COM_TIENDA_ARTICLE_TO_DISPLAY_AFTER_SUCCESSFUL_CHECKOUT_DESC'); ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ARTICLE_TO_DISPLAY_AFTER_UNSUCCESSFUL_CHECKOUT'); ?>
            </th>
            <td style="width: 280px;"><?php echo $this -> elementArticleModel -> _fetchElement('article_default_payment_failure', $this -> row -> get('article_default_payment_failure')); ?> <?php echo $this -> elementArticleModel -> _clearElement('article_default_payment_failure', '0'); ?>
            </td>
            <td><?php echo JText::_('COM_TIENDA_ARTICLE_TO_DISPLAY_AFTER_UNSUCCESSFUL_CHECKOUT_DESC'); ?>
            </td>
        </tr>
    </tbody>
</table>
