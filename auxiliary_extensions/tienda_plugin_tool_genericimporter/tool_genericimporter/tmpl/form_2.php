<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = $this->state; ?>
<?php echo @$this->vars->token; ?>
<p><?php echo JText::_( $this->_importer->get( 'tool_description' ) ); ?></p>

<div class="note">
	<span style="float: right; font-size: large; font-weight: bold;"><?php echo JText::_('COM_TIENDA_STEP_TWO_OF_THREE'); ?></span>
	<p><?php echo JText::_( $this->_importer->get( 'form_2_note' ) ); ?></p>
</div>
    
<fieldset>
	<legend><?php echo JText::_( $this->_importer->get( 'form_2_fieldset' ) ); ?></legend>
		<?php echo $this->getHtmlStep( 2, 1); ?>
	<input type="hidden" name="importer" value="<?php echo $state->importer;?>" />
</fieldset>        