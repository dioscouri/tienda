<?php defined('_JEXEC') or die('Restricted access'); ?>

<div class="note">
<?php echo JText::_('TIENDA VIRTUALMERCHANT PAYMENT PREPARATION MESSAGE');?>
</div>

<form action="<?php echo @$vars->payment_url; ?>" method="post" name="adminForm" enctype="multipart/form-data">

    <input type='hidden' name='ssl_merchant_id' value='<?php echo @$vars->ssl_merchant_id; ?>' />
    <input type='hidden' name='ssl_pin' value='<?php echo @$vars->ssl_pin; ?>' />
    
    <input type='hidden' name='ssl_amount' value='<?php echo @$vars->amount; ?>' />
    
    <?php if(@$vars->test_mode):?>
    <input type='hidden' name='ssl_test_mode' value='true' />
    <?php else: ?>
    <input type='hidden' name='ssl_test_mode' value='false' />
    <?php endif;?>

    <input type="submit" class="button" value="<?php echo JText::_('Click Here to Complete Order'); ?>" />
    
    <input type='hidden' name='ssl_receipt_link_method' value='GET' />
    <input type='hidden' name='ssl_receipt_link_url' value='<?php echo JRoute::_(@$vars->receipt_url); ?>' />
    <input type='hidden' name='ssl_result_format' value="ASCII" />
    
    <?php echo JHTML::_( 'form.token' ); ?>
</form>