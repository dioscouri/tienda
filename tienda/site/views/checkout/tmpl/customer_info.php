<?php defined('_JEXEC') or die('Restricted access'); ?>
<fieldset class="tienda-expanded" id="customer-pane">
	<legend class="tienda-collapse-processed"><?php echo JText::_('Customer Information')?></legend>
	<div id="tienda_customer">
		<div class="note">
			<?php echo JText::_('Order information will be sent to your account e-mail listed below.')?>	
		</div>
	<?php if($this->user->id):?>
	<?php echo JText::_('E-mail address');?>: <?php echo $this->user->email;?> ( <?php echo TiendaUrl::popup( "index.php?option=com_user&view=user&task=edit&tmpl=component", JText::_('edit'), array('update' => true) );  ?>)
	<?php else : ?>
	<?php echo JText::_('Email Address')?>: <input type="text" maxlength="250" size="48" id="email_address" name="email_address">
	<?php endif;?>
	</div>		
</fieldset> 