<?php defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); ?>

<div class='componentheading'>
    <span><?php echo JText::_( "COM_TIENDA_SHARE_WISHLIST_ITEMS" ); ?></span>
</div>

<div class="share_wishlist">
<form action="<?php echo JRoute::_('index.php?option=com_tienda&view=wishlists&task=shareitems' ); ?>" method="post" name="adminForm" enctype="multipart/form-data">
    <table class="adminForm">
    <tr>
        <td class="key">
            <?php echo JText::_( "COM_TIENDA_EMAIL_RECIPIENTS" ); ?>
        </td>
        <td>
            <p class="tip"><?php echo JText::_( "COM_TIENDA_EMAIL_RECIPIENTS_TIP" ); ?></p>
            <textarea name="share_emails"></textarea>
        </td>
    </tr>
    <tr>
        <td class="key">
            <?php echo JText::_( "COM_TIENDA_MESSAGE" ); ?>
        </td>
        <td>
            <p class="tip"><?php echo JText::_( "COM_TIENDA_MESSAGE_TIP" ); ?></p>
            <textarea name="share_message"></textarea>
        </td>
    </tr>
    <tr>
        <td class="key">
            <?php echo JText::_( "COM_TIENDA_ITEMS_BEING_SHARED" ); ?>
        </td>
        <td>
            list of items being shared
            <?php echo Tienda::dump( $this->items, true ); ?>
        </td>
    </tr>
    </table>
</form>
</div>