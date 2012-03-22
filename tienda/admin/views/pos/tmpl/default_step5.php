<?php defined('_JEXEC') or die('Restricted access');?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); ?>
<?php 
$order_link = @$this->order_link;
$plugin_html = @$this->plugin_html;
?>
<div class="table">
	<div class="row">
		<div class="cell step_body inactive">
		</div>
		<div class="cell step_title inactive">
			<h2>
			<?php echo JText::_('POS_STEP1_SELECT_USER');?>
			</h2>
		</div>
	</div>
	<div class="row">
		<div class="cell step_body inactive">
		</div>
		<div class="cell step_title inactive">
			<h2>
			<?php echo JText::_('POS_STEP2_SELECT_PRODUCTS');?>
			</h2>
		</div>
	</div>
	<div class="row">
		<div class="cell step_body inactive">
		</div>
		<div class="cell step_title inactive">
			<h2>
			<?php echo JText::_('POS_STEP3_SELECT_PAYMENT_SHIPPING_METHODS');?>
			</h2>
		</div>
	</div>
	<div class="row">
		<div class="cell step_body inactive">
		</div>
		<div class="cell step_title inactive">
			<h2>
			<?php echo JText::_('POS_STEP4_REVIEW_SUBMIT_ORDER');?>
			</h2>
		</div>
	</div>
	<div class="row">
		<div class="cell step_body active">
			<?php echo $plugin_html; ?>
			
			<div class="note">
				<a href="<?php echo JRoute::_($order_link);?>">
				<?php echo JText::_('CLICK TO VIEW OR EDIT ORDER');?>
				</a>
			</div>
			<?php foreach ($this->articles as $article) : ?>
			<div class="postpayment_article">
				<?php echo $article;?>
			</div>
			<?php endforeach;?>
		</div>
		<div class="cell step_title active">
			<h2>
			<?php echo JText::_('POS_STEP5_PAYMENT_CONFIRMATION');?>
			</h2>
		</div>
	</div>
</div>
<div>
	<input type="hidden" name="nextstep" id="nextstep" value="step1" />
</div>
