<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row;
JFilterOutput::objectHTMLSafe( $row );
Tienda::load( 'TiendaHelperManufacturer', 'helpers.manufacturer' );
?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >

	<fieldset>
		<legend><?php echo JText::_('Form'); ?></legend>
			<table class="admintable">
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<?php echo JText::_('COM_TIENDA_NAME'); ?>:
					</td>
					<td>
						<input type="text" name="manufacturer_name" id="manufacturer_name" value="<?php echo @$row->manufacturer_name; ?>" size="48" maxlength="250" />
					</td>
				</tr>
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<?php echo JText::_('Enabled'); ?>:
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', 'manufacturer_enabled', '', @$row->manufacturer_enabled ); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<?php echo JText::_('Current Image'); ?>:
					</td>
					<td>
						<?php
						jimport('joomla.filesystem.file');
						if (!empty($row->manufacturer_image) && JFile::exists( Tienda::getPath( 'manufacturers_images').DS.$row->manufacturer_image ))
						{
							echo TiendaUrl::popup( TiendaHelperManufacturer::getImage($row->manufacturer_id, '', '', 'full', true), TiendaHelperManufacturer::getImage($row->manufacturer_id), array('update' => false, 'img' => true));	
						}
						?>
						<br />
						<input type="text" name="manufacturer_image" id="manufacturer_image" value="<?php echo @$row->manufacturer_image; ?>" size="48" maxlength="250" />
					</td>
				</tr>
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<?php echo JText::_('Upload New Image'); ?>:
					</td>
					<td>
						<input name="manufacturer_image_new" type="file" size="40" />
					</td>
				</tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_DESCRIPTION'); ?>:
                    </td>
                    <td>
                        <?php $editor = &JFactory::getEditor(); ?>
                        <?php echo $editor->display( 'manufacturer_description',  @$row->manufacturer_description, '100%', '450', '100', '20' ) ; ?>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top; width: 100px; text-align: right;" class="key">
                        <?php echo JText::_('Parameters'); ?>:
                    </td>
                    <td>
                        <textarea name="manufacturer_params" id="manufacturer_params" rows="10" cols="35"><?php echo @$row->manufacturer_params; ?></textarea>
                    </td>
                </tr>
			</table>
			<input type="hidden" name="id" value="<?php echo @$row->manufacturer_id; ?>" />
			<input type="hidden" name="task" value="" />
	</fieldset>
</form>