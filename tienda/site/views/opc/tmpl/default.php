<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $active = false; ?>

<ol id="opc-checkout-steps">
    
    <?php if (empty($this->user->id)) { ?>
    <li id="opc-checkout-method" class="section <?php if (empty($active)) { $active = true; echo 'active'; } ?>">
        <div class="section-title">
            <h3><?php echo JText::_( "COM_TIENDA_CHECKOUT_METHOD" ); ?></h3>
            <a class="opc-change" style="display: none;"><?php echo JText::_( "COM_TIENDA_CHANGE" ); ?></a>
        </div>
        <div id="opc-checkout-method-body" class="opc-open">
            <?php $this->setLayout('loginregister'); echo $this->loadTemplate(); ?>
        </div>
        <div id="opc-checkout-method-summary" class="opc-summary opc-hidden"></div>
    </li>
    <?php } ?>
    
    <li id="opc-billing" class="section <?php if (empty($active)) { $active = true; echo 'active'; } ?>">
        <div class="section-title">
            <h3><?php echo JText::_( "COM_TIENDA_BILLING_INFORMATION" ); ?></h3>
            <a class="opc-change" style="display: none;"><?php echo JText::_( "COM_TIENDA_CHANGE" ); ?></a>
        </div>
        <div id="opc-billing-body" class="opc-open">
            <?php $this->setLayout('billing'); echo $this->loadTemplate(); ?>
        </div>
        <div id="opc-billing-summary" class="opc-summary opc-hidden"></div>
    </li>    
    
    <?php if (!empty($this->showShipping)) { ?>
    <li id="opc-shipping" class="section <?php if (empty($active)) { $active = true; echo 'active'; } ?>">
        <div class="section-title">
            <h3><?php echo JText::_( "COM_TIENDA_SHIP_TO" ); ?></h3>
            <a class="opc-change" style="display: none;"><?php echo JText::_( "COM_TIENDA_CHANGE" ); ?></a>
        </div>
        <div id="opc-shipping-body" class="opc-open">
            <?php $this->setLayout('shipping'); echo $this->loadTemplate(); ?>
        </div>
        <div id="opc-shipping-summary" class="opc-summary opc-hidden"></div>
    </li>
    
    <li id="opc-shipping-method" class="section <?php if (empty($active)) { $active = true; echo 'active'; } ?>">
        <div class="section-title">
            <h3><?php echo JText::_( "COM_TIENDA_SHIPPING_METHOD" ); ?></h3>
            <a class="opc-change" style="display: none;"><?php echo JText::_( "COM_TIENDA_CHANGE" ); ?></a>
        </div>
        <div id="opc-shipping-method-body" class="opc-open">
            <?php $this->setLayout('shippingmethod'); echo $this->loadTemplate(); ?>
        </div>
        <div id="opc-shipping-method-summary" class="opc-summary opc-hidden"></div>
    </li>
    <?php } ?>

    <li id="opc-payment" class="section <?php if (empty($active)) { $active = true; echo 'active'; } ?>">
        <div class="section-title">
            <h3><?php echo JText::_( "COM_TIENDA_PAYMENT_INFORMATION" ); ?></h3>
            <a class="opc-change" style="display: none;"><?php echo JText::_( "COM_TIENDA_CHANGE" ); ?></a>
        </div>
        <div id="opc-payment-body" class="opc-open">
            <?php $this->setLayout('payment'); echo $this->loadTemplate(); ?>
        </div>
        <div id="opc-payment-summary" class="opc-summary opc-hidden"></div>
    </li>

    <li id="opc-review" class="section <?php if (empty($active)) { $active = true; echo 'active'; } ?>">
        <div class="section-title">
            <h3><?php echo JText::_( "COM_TIENDA_ORDER_REVIEW" ); ?></h3>
            <a class="opc-change" style="display: none;"><?php echo JText::_( "COM_TIENDA_CHANGE" ); ?></a>
        </div>
        <div id="opc-review-body" class="opc-open">
            <?php $this->setLayout('review'); echo $this->loadTemplate(); ?>
        </div>
        <div id="opc-review-summary" class="opc-summary opc-hidden"></div>
    </li>
</ol>

<?php FB::log($this); ?>