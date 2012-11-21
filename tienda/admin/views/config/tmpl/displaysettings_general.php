<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $row = @$this -> row; ?>

<table class="table table-striped table-bordered">
    <tbody>
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
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DISPLAY_PRODUCT_SORT_BY'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('display_sort_by', 'class="inputbox"', $this -> row -> get('display_sort_by', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_PRODUCT_SORTINGS'); ?>
            </th>
            <td><input type="text" name="display_sortings" value="<?php echo $this -> row -> get('display_sortings', 'Name|product_name,Price|price,Rating|product_rating'); ?>" class="inputbox" size="45" />
            </td>
            <td><?php echo JText::_('COM_TIENDA_PRODUCT_SORTINGS_DESC')?>
            </td>
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
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DISPLAY_FACEBOOK_LIKE_BUTTON'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('display_facebook_like', 'class="inputbox"', $this -> row -> get('display_facebook_like', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DISPLAY_TWITTER_BUTTON'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('display_tweet', 'class="inputbox"', $this -> row -> get('display_tweet', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DEFAULT_TWITTER_MESSAGE'); ?>
            </th>
            <td><input type="text" name="display_tweet_message" value="<?php echo $this -> row -> get('display_tweet_message', 'Check this out!'); ?>" class="inputbox" size="35" />
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DISPLAY_GOOGLE_PLUS1_BUTTON'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('display_google_plus1', 'class="inputbox"', $this -> row -> get('display_google_plus1', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DISPLAY_GOOGLE_PLUS1_BUTTON_SIZE'); ?>
            </th>
            <td><?php
            $google_sizes = array();
            $google_sizes[] = JHTML::_('select.option', 'small', JText::_('COM_TIENDA_GOOGLE_SMALL'));
            $google_sizes[] = JHTML::_('select.option', 'medium', JText::_('COM_TIENDA_GOOGLE_MEDIUM'));
            $google_sizes[] = JHTML::_('select.option', '', JText::_('COM_TIENDA_GOOGLE_STANDARD'));
            $google_sizes[] = JHTML::_('select.option', 'tall', JText::_('COM_TIENDA_GOOGLE_TALL'));
            echo JHTML::_('select.genericlist', $google_sizes, 'display_google_plus1_size', array('class' => 'inputbox', 'size' => '1'), 'value', 'text', $this -> row -> get('display_google_plus1_size', 'medium'));
            ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_USE_URI_FOR_SOCIAL_BOOKMARK_INTEGRATION'); ?>
            </th>
            <td><?php
            $social_uri_types = array();
            $social_uri_types[] = JHTML::_('select.option', 0, JText::_('COM_TIENDA_LONG_URI'));
            $social_uri_types[] = JHTML::_('select.option', 1, JText::_('COM_TIENDA_BITLY'));
            echo JHTML::_('select.genericlist', $social_uri_types, 'display_bookmark_uri', array('class' => 'inputbox', 'size' => '1'), 'value', 'text', $this -> row -> get('display_bookmark_uri', 0));
            ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_BITLY_LOGIN'); ?>
            </th>
            <td><input type="text" name="bitly_login" value="<?php echo $this -> row -> get('bitly_login', ''); ?>" class="inputbox" size="35" />
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_BITLY_KEY'); ?>
            </th>
            <td><input type="text" name="bitly_key" value="<?php echo $this -> row -> get('bitly_key', ''); ?>" class="inputbox" size="35" />
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
            <td><?php echo JText::_('COM_TIENDA_SHOW_THE_ASK_A_QUESTION_ABOUT_THIS_PRODUCT_FORM_IN_MODAL'); ?>
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
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DISPLAY_WORKING_IMAGE_PRODUCT'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('dispay_working_image_product', 'class="inputbox"', $this -> row -> get('dispay_working_image_product', '1')); ?>
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
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DEFAULT_TAX_GEOZONE'); ?>
            </th>
            <td><?php echo TiendaSelect::geozone($this -> row -> get('default_tax_geozone'), 'default_tax_geozone', 1); ?>
            </td>
            <td><?php echo JText::_('COM_TIENDA_DEFAULT_TAX_GEOZONE_DESC'); ?>
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
            <td><?php echo TiendaSelect::cartbutton($this -> row -> get('cartbutton', 'image'), 'cartbutton'); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_WIDTH_OF_UI_LIGHTBOXES'); ?>
            </th>
            <td><input type="text" name="lightbox_width" value="<?php echo $this -> row -> get('lightbox_width', '800'); ?>" class="inputbox" size="10" />
            </td>
            <td><?php echo JText::_('COM_TIENDA_WIDTH_OF_UI_LIGHTBOXES_DESC'); ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_HEIGHT_OF_UI_LIGHTBOXES'); ?>
            </th>
            <td><input type="text" name="lightbox_height" value="<?php echo $this -> row -> get('lightbox_height', '480'); ?>" class="inputbox" size="10" />
            </td>
            <td><?php echo JText::_('COM_TIENDA_HEIGHT_OF_UI_LIGHTBOXES_DESC'); ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_SHOW_DIOSCOURI_LINK_IN_FOOTER'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist( 'show_linkback', 'class="inputbox"', $this -> row -> get('show_linkback', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_YOUR_DIOSCOURI_AFFILIATE_ID'); ?>
            </th>
            <td><input type="text" name="amigosid" value="<?php echo $this -> row -> get('amigosid', ''); ?>" class="inputbox" />
            </td>
            <td><a href='http://www.dioscouri.com/index.php?option=com_amigos' target='_blank'> <?php echo JText::_('COM_TIENDA_NO_AMIGOSID'); ?>
            </a>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_CONFIG_PROCESS_CONTENT_PLUGIN_PRODUCT_DESC'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('content_plugins_product_desc', 'class="inputbox"', $this -> row -> get('content_plugins_product_desc', '0')); ?>
            </td>
            <td></td>
        </tr>
    </tbody>
</table>
