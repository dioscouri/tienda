<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; ?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >

	<fieldset>
		<legend><?php echo JText::_('Form'); ?></legend>
			<table class="admintable">
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<?php echo JText::_( 'Name' ); ?>:
					</td>
					<td>
						<input type="text" name="manufacturer_name" id="manufacturer_name" value="<?php echo @$row->manufacturer_name; ?>" size="48" maxlength="250" />
					</td>
				</tr>
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<?php echo JText::_( 'Enabled' ); ?>:
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', 'manufacturer_enabled', '', @$row->manufacturer_enabled ); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<?php echo JText::_( 'Current Image' ); ?>:
					</td>
					<td>
						<?php
						jimport('joomla.filesystem.file');
						if (!empty($row->manufacturer_image) && JFile::exists( Tienda::getPath( 'manufacturers_images').DS.$row->manufacturer_image ))
						{
							?>
							<img src="<?php echo Tienda::getURL( 'manufacturers_images').$row->manufacturer_image; ?>" style="display: block;" />
							<?php	
						}
						?>
						<input type="text" name="manufacturer_image" id="manufacturer_image" value="<?php echo @$row->manufacturer_image; ?>" size="48" maxlength="250" />
					</td>
				</tr>
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<?php echo JText::_( 'Upload New Image' ); ?>:
					</td>
					<td>
						<input name="manufacturer_image_new" type="file" size="40" />
					</td>
				</tr>
			</table>
			<input type="hidden" name="id" value="<?php echo @$row->manufacturer_id; ?>" />
			<input type="hidden" name="task" value="" />
	</fieldset>
</form>