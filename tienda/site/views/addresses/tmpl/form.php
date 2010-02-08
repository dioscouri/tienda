<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; ?>

<div class='componentheading'>
    <span><?php echo JText::_( "Edit Address" ); ?></span>
</div>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" onsubmit="tiendaFormValidation( '<?php echo @$form['validation']; ?>', 'validationmessage', document.adminForm.task.value, document.adminForm )" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >
    <div style="float: right;">
        <input type="button" onclick="tiendaSubmitForm('save');" value="<?php echo JText::_('Submit'); ?>" />    
    </div>

    <?php
    echo "<< <a href='".JRoute::_("index.php?option=com_tienda&view=addresses")."'>".JText::_( 'Cancel and Return to List' )."</a>";
    ?>
    
    <div id="validationmessage"></div>
	<?php JLoader::import( 'com_tienda.views.addresses.tmpl.form_inner', JPATH_SITE.DS.'components' ); ?>
    <input type="button" onclick="tiendaSubmitForm('save');" value="<?php echo JText::_('Submit'); ?>" />

    <input type="hidden" name="id" value="<?php echo @$row->address_id; ?>" />
    <input type="hidden" name="task" id="task" value="" />

    <?php echo @$form['validate']; ?>

</form>