<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; JFilterOutput::objectHTMLSafe( $row ); ?>

<form action ="index.php?option=com_tienda&view=subscriptions&task=unsubscribe"  method="post" class="adminform" name="adminForm" enctype="multipart/form-data" > 
<input type="hidden" name="id" value="<?php echo @$row->subscription_id; ?>" />
<div>

</div>
<input type="submit" value="Unsubscribe">
</form>
