<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; JFilterOutput::objectHTMLSafe( $row ); ?>
<?php $histories = Tienda::getClass( 'TiendaHelperSubscription', 'helpers.subscription' )->getHistory( $row->subscription_id ); ?>

<?php Tienda::load( 'TiendaGrid', 'library.grid' );?>

<div class='componentheading'>
	<span><?php echo JText::_( "Subscription Details" ); ?></span>
</div>
   <?php if ($menu =& TiendaMenu::getInstance()) { $menu->display(); } ?>

<table style="width: 100%;">
<tr>
    <td style="width: 65%; vertical-align: top;">
    
    <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onBeforeDisplaySubscriptionViewSubscriptionInfo', array( $row ) );                    
    ?>
    
	<fieldset>
			<table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Subscription Enabled' ); ?>:
                    </td>
                    <td>
                        <?php echo TiendaGrid::boolean( @$row->subscription_enabled ); ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Lifetime Subscription' ); ?>?
                    </td>
                    <td>
                        <?php echo TiendaGrid::boolean( @$row->lifetime_enabled ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'Created' ); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::_('date', $row->created_datetime, TiendaConfig::getInstance()->get('date_format')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'Expiration Date' ); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::_('date', $row->expires_datetime, TiendaConfig::getInstance()->get('date_format')); ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Transaction ID' ); ?>:
                    </td>
                    <td>
                        <?php echo @$row->transaction_id; ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Product' ); ?>:
                    </td>
                    <td>
                        <?php echo @$row->product_name; ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Product ID' ); ?>:
                    </td>
                    <td>
                        <?php echo @$row->product_id; ?>
                    </td>
                </tr>
                
            </table>
    </fieldset>
    
    <fieldset>
        <legend><?php echo JText::_('Order Information'); ?></legend>
            <table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Order ID' ); ?>:
                    </td>
                    <td>
                       <a href="index.php?option=com_tienda&view=orders&task=view&id= <?php echo @$row->order_id; ?>"  >
                       <?php echo @$row->order_id; ?>
                       </a>
                    </td>
                </tr>
            </table>
	</fieldset>
	
    <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onAfterDisplaySubscriptionViewSubscriptionInfo', array( $row ) );                    
    ?>
	
    </td>
    <td style="width: 35%; vertical-align: top;">
    
        <?php
            // fire plugin event here to enable extending the form
            JDispatcher::getInstance()->trigger('onBeforeDisplaySubscriptionViewSubscriptionHistory', array( $row ) );                    
        ?>
    
         <fieldset>
          
           <div id="order_products_div">
           <?php if ($row->subscription_enabled == "1") {?>
           <div>
           <?php echo JText::_( 'NOTE FOR UNSUBSCRIBE' ); ?>
          </div>
          <div>
	         <?php include("form.php"); ?>
	         </div> 
	        <?php } else  {
	        	?>
	        	<div>
           <?php echo JText::_( 'NOTE FOR SUBSCRIBE' ); ?>
          </div>
          <div>
	       <a href="index.php?option=com_tienda&view=products&task=view&&id= <?php echo @$row->product_id ;?>" > <?php echo JText::_( 'SUBSCRIBE' ); ?>  </a>
	         </div> 
	       <?php  }

	        ?>  
	        </div> 
        </fieldset>
        
        <?php
            // fire plugin event here to enable extending the form
            JDispatcher::getInstance()->trigger('onAfterDisplaySubscriptionViewSubscriptionHistory', array( $row ) );                    
        ?>
    </td>
</tr>
</table>

    <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onAfterDisplaySubscriptionView', array( $row ) );                    
    ?>
