<?php defined('_JEXEC') or die('Restricted access');?>
<div class="table">
	<div class="row">
		<div class="cell step_body inactive">
			<?php echo $this->step1_inactive;?>

			<div class="go_back">
				<a href="index.php?option=com_tienda&view=pos">
				<?php echo JText::_("Go Back");?>
				</a>
			</div>
		</div>
		<div class="cell step_title inactive">
			<h2>
			<?php echo JText::_("POS_STEP1_SELECT_USER");?>
			</h2>
		</div>
	</div>
	<div class="row">
		<div class="cell step_body inactive">
			<div class="go_back">
				<a href="index.php?option=com_tienda&view=pos&nextstep=step2">
				<?php echo JText::_("Go Back");?>
				</a>
			</div>
			<div id="orderSummary">
				<?php echo $this->orderSummary;?>				
			</div>
		</div>
		<div class="cell step_title inactive">
			<h2>
			<?php echo JText::_("POS_STEP2_SELECT_PRODUCTS");?>
			</h2>
		</div>
	</div>
	<div class="row">
		<div class="cell step_body inactive">
			            <div id="validation_message"></div>			
			<div id="addresses">				
				<?php if($this->showShipping):?>
				<div>
					<h4 id='shipping_address_header' class="address_header">
					<?php echo JText::_("Shipping Information") ?>
					</h4>					
				</div>
				<div class="reset"></div>
				<?php endif;?>
			</div>			
		</div>
		<div class="cell step_title inactive">
			<h2>
			<?php echo JText::_("POS_STEP3_SELECT_PAYMENT_SHIPPING_METHODS");?>
			</h2>
		</div>
	</div>
	<div class="row">
		<div class="cell step_body inactive">
			<div class="continue">			
                <?php $onclick = "tiendaValidation( '" . $this->validation_url . "', 'validation_message', 'saveStep4', document.adminForm, true, '".JText::_( 'Validating' )."' );"; ?> 
                <input onclick="<?php echo $onclick; ?>" value="<?php echo JText::_('Continue'); ?>" type="button" class="button" />
            </div>
		</div>
		<div class="cell step_title inactive">
			<h2>
			<?php echo JText::_("POS_STEP4_REVIEW_SUBMIT_ORDER");?>
			</h2>
		</div>
	</div>
	<div class="row">
		<div class="cell step_body inactive">
		</div>
		<div class="cell step_title inactive">
			<h2>
			<?php echo JText::_("POS_STEP5_PAYMENT_CONFIRMATION");?>
			</h2>
		</div>
	</div>
</div>

<input type="hidden" name="nextstep" id="nextstep" value="step5" />