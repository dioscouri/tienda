<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $form = @$this->form2; ?>
<?php $row = @$this->item;
JFilterOutput::objectHTMLSafe( $row );
?>

<form action="<?php echo JRoute::_( @$form['action'] ); ?>" method="post" name="adminForm" enctype="multipart/form-data">
<fieldset>
	<legend><?php echo JText::_('Form'); ?></legend>
	
	<div style="width: 65%; float: left;">
	<table class="admintable">
		<tr>
			<td width="100" align="right" class="key">
				<label for="service_name">
				<?php echo JText::_( 'Name' ); ?>:
				</label>
			</td>
			<td>
				<input type="text" name="service_name" id="service_name" value="<?php echo @$row->service_name; ?>" size="48" maxlength="250" />
			</td>
		</tr>
        <tr>
            <td width="100" align="right" class="key">
                <label for="service_code">
                <?php echo JText::_( 'Service Code' ); ?>:
                </label>
            </td>
            <td>
                <input type="text" name="service_code" id="service_code" value="<?php echo @$row->service_code; ?>" size="48" maxlength="250" />
            </td>
        </tr>
        
       </table>
       </div> 
	<input type="hidden" name="service_id" value="<?php echo @$row->service_id; ?>" />
	<input type="hidden" id="shippingTask" name="shippingTask" value="<?php echo @$form->shippingTask; ?>" />
	
	
	</fieldset>
</form>