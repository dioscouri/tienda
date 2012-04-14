<?php defined('_JEXEC') or die('Restricted access'); ?>

<div class="table">
    <div class="row">
        <div class="cell step_body inactive">
            <?php echo $this->step1_inactive; ?>
            
            <div class="go_back">
                <a href="index.php?option=com_tienda&view=pos"><?php echo JText::_('COM_TIENDA_GO_BACK'); ?></a>
            </div>
        </div>
        
        <div class="cell step_title inactive">
            <h2><?php echo JText::_('COM_TIENDA_POS_STEP1_SELECT_USER'); ?></h2>
        </div>
    </div>
    
    <div class="row">
        <div class="cell step_body active">
            <h2>
                <?php echo JText::_('COM_TIENDA_SELECT_PRODUCTS'); ?>
                <span class="new_product">
                    <?php echo TiendaURL::popup( "index.php?option=com_tienda&view=pos&task=addproducts&tmpl=component", JText::_('COM_TIENDA_ADD_NEW_PRODUCT_TO_ORDER') , array('width'=>700, 'height'=>400)); ?>
                </span>
            </h2>
                        
            <div id="validation_message"></div>

            <div id="cart">
                <?php if (empty($this->cart)): ?>
                    <?php echo JText::_('COM_TIENDA_NO_ITEMS_IN_CART'); ?>
                <?php else: ?>
                	<?php echo $this->cart;?>
                <?php endif; ?>
            </div>

            <div class="continue">
            	
            	<!--<input type="checkbox" value="1" name="skippayment" id="skippayment"> <?php echo JText::_('COM_TIENDA_SKIP_PAYMENT');?>--> 
            	<!--<a class="modal" href="" />[?]</a>-->
                <?php $onclick = "tiendaValidation( '" . $this->validation_url . "', 'validation_message', 'saveStep2', document.adminForm, true, '".JText::_('COM_TIENDA_VALIDATING')."' );"; ?> 
                <input onclick="<?php echo $onclick; ?>" value="<?php echo JText::_('COM_TIENDA_CONTINUE'); ?>" type="button" class="button" />
            </div>        
        </div>
        <div class="cell step_title active">
            <h2><?php echo JText::_('COM_TIENDA_POS_STEP2_SELECT_PRODUCTS'); ?></h2>
        </div>
    </div>
    
    <div class="row">
        <div class="cell step_body inactive">
        </div>
        <div class="cell step_title inactive">
            <h2><?php echo JText::_('COM_TIENDA_POS_STEP3_SELECT_PAYMENT_SHIPPING_METHODS'); ?></h2>
        </div>
    </div>
    
    <div class="row">
        <div class="cell step_body inactive">
        </div>
        <div class="cell step_title inactive">
            <h2><?php echo JText::_('COM_TIENDA_POS_STEP4_REVIEW_SUBMIT_ORDER'); ?></h2>
        </div>
    </div>
    
    <div class="row">
        <div class="cell step_body inactive">
        </div>
        <div class="cell step_title inactive">
            <h2><?php echo JText::_('COM_TIENDA_POS_STEP5_PAYMENT_CONFIRMATION'); ?></h2>
        </div>
    </div>
    
</div>

<input type="hidden" name="nextstep" id="nextstep" value="step3" />