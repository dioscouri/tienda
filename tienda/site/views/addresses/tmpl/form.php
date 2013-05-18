<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; ?>
<?php $tmpl = @$this->tmpl; ?>
<?php JFilterOutput::objectHTMLSafe( $row ); ?>

<div class='componentheading'>
    <span><?php echo JText::_('COM_TIENDA_EDIT_ADDRESS'); ?></span>
</div>

<form action="<?php echo JRoute::_( @$form['action'].$tmpl ) ?>" onsubmit="tiendaFormValidation( '<?php echo @$form['validation']; ?>', 'validationmessage', document.adminForm.task.value, document.adminForm )" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >
    <div style="float: right;">
        <input type="button" onclick="tiendaSubmitForm('save');" value="<?php echo JText::_('COM_TIENDA_SUBMIT'); ?>" />    
    </div>

    <?php
    echo "<< <a href='".JRoute::_("index.php?option=com_tienda&view=addresses".$tmpl)."'>".JText::_('COM_TIENDA_CANCEL_AND_RETURN_TO_LIST')."</a>";
    ?>
    
    <div id="validationmessage"></div>
	<?php echo $this->form_inner; ?>
    <input type="button" onclick="tiendaSubmitForm('save');" value="<?php echo JText::_('COM_TIENDA_SUBMIT'); ?>" />

    <input type="hidden" name="id" value="<?php echo @$row->address_id; ?>" />
    <input type="hidden" name="task" id="task" value="" />

    <?php echo @$form['validate']; ?>

</form>