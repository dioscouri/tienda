<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $row = @$this -> row; ?>

<table class="table table-striped table-bordered">
    <tbody>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ENABLE_PRODUCT_REVIEWS'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('product_review_enable', 'class="inputbox"', $this -> row -> get('product_review_enable', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_AUTOMATICALLY_APPROVE_REVIEWS'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('product_reviews_autoapprove', 'class="inputbox"', $this -> row -> get('product_reviews_autoapprove', '0')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_REQUIRE_LOGIN_TO_LEAVE_REVIEW'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('login_review_enable', 'class="inputbox"', $this -> row -> get('login_review_enable', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_REQUIRE_PURCHASE_TO_LEAVE_REVIEW'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('purchase_leave_review_enable', 'class="inputbox"', $this -> row -> get('purchase_leave_review_enable', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_USE_CAPTCHA'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('use_captcha', 'class="inputbox"', $this -> row -> get('use_captcha', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ENABLE_REVIEW_HELPFULNESS_VOTING'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('review_helpfulness_enable', 'class="inputbox"', $this -> row -> get('review_helpfulness_enable', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ENABLE_SHARE_THIS_LINK'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('share_review_enable', 'class="inputbox"', $this -> row -> get('share_review_enable', '1')); ?>
            </td>
            <td></td>
        </tr>
    </tbody>
</table>
