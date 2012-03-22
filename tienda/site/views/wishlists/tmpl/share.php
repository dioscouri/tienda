<?php defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); ?>

<div class='componentheading'>
    <span><?php echo JText::_('COM_TIENDA_SHARE_WISHLIST_ITEMS'); ?></span>
</div>

<form action="<?php echo JRoute::_('index.php?option=com_tienda&view=wishlists&task=shareitems' ); ?>" method="post" name="adminForm" enctype="multipart/form-data">
    <div class="wrap">
        <div class="form_key">
            <?php echo JText::_('COM_TIENDA_ITEMS_BEING_SHARED'); ?>
        </div>
        <div class="form_input">
            <ul>
            <?php foreach ($this->items as $item) { ?>
                <li>
                    <?php echo $item->product_name; ?>
                    <input type="hidden" name="cid[]" value="<?php echo $item->wishlist_id; ?>" />
                </li>
            <?php } ?>
            </ul>
        </div>
    </div>
    
    <div class="wrap">
        <div class="form_key">
            <?php echo JText::_('COM_TIENDA_EMAIL_RECIPIENTS'); ?>
        </div>
        <div class="form_input">
            <p class="tip"><?php echo JText::_('COM_TIENDA_EMAIL_RECIPIENTS_TIP'); ?></p>
            <textarea name="share_emails" style="width: 100%;"></textarea>
        </div>
    </div>
    
    <div class="wrap">
        <div class="form_key">
            <?php echo JText::_('COM_TIENDA_MESSAGE'); ?>
        </div>
        <div class="form_input">
            <p class="tip"><?php echo JText::_('COM_TIENDA_MESSAGE_TIP'); ?></p>
            <textarea name="share_message" style="width: 100%; height: 150px;"></textarea>
        </div>
    </div>

    <div class="wrap">
        <input type="submit" class="submit" name="shareitems" value="<?php echo JText::_('COM_TIENDA_SHARE_NOW'); ?>" />
        <?php echo JHTML::_( 'form.token' ); ?>
    </div>
</form>
