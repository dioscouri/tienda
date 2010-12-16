<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; JFilterOutput::objectHTMLSafe( $row ); ?>
<?php $histories = Tienda::getClass( 'TiendaHelperSubscription', 'helpers.subscription' )->getHistory( $row->subscription_id ); ?>

<?php Tienda::load( 'TiendaGrid', 'library.grid' );?>

<div class='componentheading'>
	<span><?php echo JText::_( "Subscription Details" ); ?></span>
</div>

    <?php if ($menu =& TiendaMenu::getInstance()) { $menu->display(); } ?>
    <?php
    echo "<< <a href='".JRoute::_("index.php?option=com_tienda&view=subscriptions")."'>".JText::_( 'Return to List' )."</a>";
    ?>

    <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onBeforeDisplaySubscriptionViewSubscriptionInfo', array( $row ) );                    
    ?>
    
    <div id="subscription_info">
        <h3><?php echo JText::_("Subscription Information"); ?></h3>
        <strong><?php echo JText::_("Product"); ?></strong>: <?php echo @$row->product_name; ?><br/>
        <strong><?php echo JText::_("Status"); ?></strong>: <?php echo TiendaGrid::boolean( @$row->subscription_enabled ); ?><br/>
        <strong><?php echo JText::_("Created"); ?></strong>: <?php echo JHTML::_('date', $row->created_datetime, TiendaConfig::getInstance()->get('date_format')); ?><br/>
        <strong><?php echo JText::_("Expires"); ?></strong>: <?php echo JHTML::_('date', $row->expires_datetime, TiendaConfig::getInstance()->get('date_format')); ?><br/>
    </div>
    
    <div id="order_info">
        <h3><?php echo JText::_("Order Information"); ?></h3>
        <strong><?php echo JText::_("Order ID"); ?></strong>: 
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
