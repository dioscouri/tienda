<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; JFilterOutput::objectHTMLSafe( $row ); ?>
<?php $histories = Tienda::getClass( 'TiendaHelperSubscription', 'helpers.subscription' )->getHistory( $row->subscription_id ); ?>

<?php Tienda::load( 'TiendaGrid', 'library.grid' );?>

<div class='componentheading'>
	<span><?php echo JText::_('COM_TIENDA_SUBSCRIPTION_DETAILS'); ?></span>
</div>

    <?php if ($menu =& TiendaMenu::getInstance()) { $menu->display(); } ?>
    <?php
    echo "<< <a href='".JRoute::_("index.php?option=com_tienda&view=subscriptions")."'>".JText::_('COM_TIENDA_RETURN_TO_LIST')."</a>";
    ?>

    <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onBeforeDisplaySubscriptionViewSubscriptionInfo', array( $row ) );                    
    ?>
    
    <div id="subscription_info">
        <h3><?php echo JText::_('COM_TIENDA_SUBSCRIPTION_INFORMATION'); ?></h3>
        <strong><?php echo JText::_('COM_TIENDA_PRODUCT'); ?></strong>: <?php echo @$row->product_name; ?><br/>
        <strong><?php echo JText::_('COM_TIENDA_STATUS'); ?></strong>: <?php echo TiendaGrid::boolean( @$row->subscription_enabled ); ?><br/>
        <strong><?php echo JText::_('COM_TIENDA_CREATED'); ?></strong>: <?php echo JHTML::_('date', $row->created_datetime, Tienda::getInstance()->get('date_format')); ?><br/>
        <strong><?php echo JText::_('COM_TIENDA_EXPIRES'); ?></strong>: <?php echo JHTML::_('date', $row->expires_datetime, Tienda::getInstance()->get('date_format')); ?><br/>
    </div>
    
    <div id="order_info">
        <h3><?php echo JText::_('COM_TIENDA_ORDER_INFORMATION'); ?></h3>
        <strong><?php echo JText::_('COM_TIENDA_ORDER_ID'); ?></strong>: 
           <a href="<?php echo JRoute::_( "index.php?option=com_tienda&view=orders&task=view&id=".$row->order_id ); ?>">
           <?php echo @$row->order_id; ?>
           </a>
           <br/>
    </div>
	
    <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onAfterDisplaySubscriptionViewSubscriptionInfo', array( $row ) );                    
    ?>
    
    <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onAfterDisplaySubscriptionView', array( $row ) );                    
    ?>
