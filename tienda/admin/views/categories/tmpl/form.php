<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; 
JFilterOutput::objectHTMLSafe( $row );
?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >

	<fieldset>
		<legend><?php echo JText::_('Form'); ?></legend>
			<table class="admintable">
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<label for="category_name">
						<?php echo JText::_( 'Name' ); ?>:
						</label>
					</td>
					<td>
						<input type="text" name="category_name" id="category_name" size="48" maxlength="250" value="<?php echo @$row->category_name; ?>" />
					</td>
				</tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'Alias' ); ?>:
                    </td>
                    <td>
                        <input name="category_alias" id="category_alias" value="<?php echo @$row->category_alias; ?>" type="text" size="48" maxlength="250" />
                    </td>
                </tr>
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<label for="parent_id">
						<?php echo JText::_( 'Parent' ); ?>:
						</label>
					</td>
					<td>
						<?php echo TiendaSelect::category( @$row->parent_id, 'parent_id', '', 'parent_id', false, true ); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<label for="enabled">
						<?php echo JText::_( 'Enabled' ); ?>:
						</label>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', 'category_enabled', '', @$row->category_enabled ); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<label for="category_full_image">
						<?php echo JText::_( 'Current Image' ); ?>:
						</label>
					</td>
					<td>
						<?php
						jimport('joomla.filesystem.file');
						if (!empty($row->category_full_image) && JFile::exists( Tienda::getPath( 'categories_images').DS.$row->category_full_image ))
						{
							echo TiendaUrl::popup( TiendaHelperCategory::getImage($row->category_id, '', '', 'full', true), TiendaHelperCategory::getImage($row->category_id), array('update' => false, 'img' => true));
						}
						?>
						<br />
						<input type="text" name="category_full_image" id="category_full_image" size="48" maxlength="250" value="<?php echo @$row->category_full_image; ?>" />
					</td>
				</tr>
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<label for="category_full_image_new">
						<?php echo JText::_( 'Upload New Image' ); ?>:
						</label>
					</td>
					<td>
						<input name="category_full_image_new" type="file" size="40" />
					</td>
				</tr>
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<label for="category_description">
						<?php echo JText::_( 'Description' ); ?>:
						</label>
					</td>
					<td>
						<?php $editor = &JFactory::getEditor(); ?>
						<?php echo $editor->display( 'category_description',  @$row->category_description, '100%', '450', '100', '20' ) ; ?>
					</td>
				</tr>
				
							<?php 

if(!empty($this->shippingHtml)){

?>

				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<label for="shippingPlugins">
						<?php echo JText::_( 'Shipping Informations' ); ?>:
						</label>
					</td>
					<td>
						<?php echo $this->shippingHtml ?>
					</td>
				</tr>		

<?php 
}
?>
			</table>

			<input type="hidden" name="id" value="<?php echo @$row->category_id?>" />
			<input type="hidden" name="task" value="" />
	</fieldset>
</form>