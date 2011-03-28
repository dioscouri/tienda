<?php defined('_JEXEC') or die('Restricted access'); ?>

<div class="table">
    <div class="row">
        <div class="cell step_body inactive">
            <?php echo $this->step1_inactive; ?>
            
            <div class="go_back">
                <a href="index.php?option=com_tienda&view=pos"><?php echo JText::_( "Go Back" ); ?></a>
            </div>
        </div>
        
        <div class="cell step_title inactive">
            <h2><?php echo JText::_( "POS_STEP1_SELECT_USER" ); ?></h2>
        </div>
    </div>
    
    <div class="row">
        <div class="cell step_body active">
            <h2>
                <?php echo JText::_( "SELECT_PRODUCTS" ); ?>
                <span class="new_product">
                    <?php echo TiendaURL::popup( "index.php?option=com_tienda&view=pos&task=addproducts&tmpl=component", JText::_( "Add New Product to Order" ) ); ?>
                </span>
            </h2>
                        
            <div id="validation_message"></div>

            <div id="cart">
                <?php if (empty($this->cart)) { ?>
                    <?php echo JText::_( "No Items in Cart" ); ?>
                <?php } ?>
            </div>

            <div class="continue">
                <?php $onclick = "tiendaValidation( '" . $this->validation_url . "', 'validation_message', 'doStep2', document.adminForm, true, '".JText::_( 'Validating' )."' );"; ?> 
                <input onclick="<?php echo $onclick; ?>" value="<?php echo JText::_('Continue'); ?>" type="button" class="button" />
            </div>        
        </div>
        <div class="cell step_title active">
            <h2><?php echo JText::_( "POS_STEP2_SELECT_PRODUCTS" ); ?></h2>
        </div>
    </div>
    
    <div class="row">
        <div class="cell step_body inactive">
        </div>
        <div class="cell step_title inactive">
            <h2><?php echo JText::_( "POS_STEP3_SELECT_PAYMENT_SHIPPING_METHODS" ); ?></h2>
        </div>
    </div>
    
    <div class="row">
        <div class="cell step_body inactive">
        </div>
        <div class="cell step_title inactive">
            <h2><?php echo JText::_( "POS_STEP4_REVIEW_SUBMIT_ORDER" ); ?></h2>
        </div>
    </div>
    
    <div class="row">
        <div class="cell step_body inactive">
        </div>
        <div class="cell step_title inactive">
            <h2><?php echo JText::_( "POS_STEP5_PAYMENT_CONFIRMATION" ); ?></h2>
        </div>
    </div>
    
</div>

<input type="hidden" name="nextstep" id="nextstep" value="step3" />