<?php defined('_JEXEC') or die('Restricted access'); ?>

<div class="showcvv" style="margin: 10px">
    <h2><?php echo JText::_('CVV HEADER'); ?></h2>
    <?php echo JText::_('CVV GENERAL DESCRIPTION'); ?>
    
    <div class="cvv_back" style="margin: 10px">
        <h3 class="cvv_header"><?php echo JText::_('CVV BACK HEADER'); ?></h3>
        <img src="plugins/tienda/payment_authorizedotnet/images/cvv_back.png" />
        <br/>
        <?php echo JText::_('CVV BACK DESCRIPTION'); ?>
    </div>
    
    <div class="cvv_front" style="margin: 10px">
        <h3 class="cvv_header"><?php echo JText::_('CVV FRONT HEADER'); ?></h3>
        <img src="plugins/tienda/payment_authorizedotnet/images/cvv_front.png" />
        <br/>
        <?php echo JText::_('CVV FRONT DESCRIPTION'); ?>
    </div>
</div>

