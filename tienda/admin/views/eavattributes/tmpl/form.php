<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row;
JFilterOutput::objectHTMLSafe( $row );
?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" >

	<fieldset>
		<legend><?php echo JText::_('Form'); ?></legend>
			<table class="admintable">
				<tr>
					<td width="100" align="right" class="key">
						<?php echo JText::_('Name'); ?>:
					</td>
					<td>
						<input type="text" name="eavattribute_label" id="eavattribute_label" size="48" maxlength="250" value="<?php echo @$row->eavattribute_label; ?>" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<?php echo JText::_('Alias'); ?>:
					</td>
					<td>
						<input type="text" name="eavattribute_alias" id="eavattribute_alias" size="48" maxlength="250" value="<?php echo @$row->eavattribute_alias; ?>" />
					</td>
				</tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <label for="enabled">
                        <?php echo JText::_('Enabled'); ?>:
                        </label>
                    </td>
                    <td>
                        <?php echo JHTML::_('select.booleanlist', 'enabled', '', @$row->enabled ); ?>
                    </td>
                </tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="eaventity_type">
						<?php echo JText::_('Entity Type'); ?>:
						</label>
					</td>
					<td>
						<?php echo TiendaSelect::entitytype(@$row->eaventity_type, 'eaventity_type'); ?>
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="eaventity_id">
						<?php echo JText::_('Entity'); ?>:
						</label>
					</td>
					<td>
						<?php 
							if(@$row->eaventity_type)
							{
								$allowed_types = array('products');
								if(in_array(@$row->eaventity_type, $allowed_types))
								{
									$url = JRoute::_("index.php?option=com_tienda&controller=eavattributes&task=selectentities&tmpl=component&eaventity_type=".@$row->eaventity_type."&id=".@$row->eavattribute_id);
									echo TiendaUrl::popup($url, JText::_('Select Entities')); 
								}
							}
							else
							{
						?>
							<div class="note">
								<?php echo JText::_('Click apply to add a link to an entity for this product'); ?>
							</div>
						<?php 
							}
						?>
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="eavattribute_type">
						<?php echo JText::_('Data Type'); ?>:
						</label>
					</td>
					<td>
						<?php echo TiendaSelect::attributetype(@$row->eavattribute_type, 'eavattribute_type'); ?>
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="editable_by">
						<?php echo JText::_('Editable By'); ?>:
						</label>
					</td>
					<td>
						<?php echo TiendaSelect::editableby(@$row->editable_by, 'editable_by'); ?>
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="eavattribute_required">
						<?php echo JText::_('Required'); ?>:
						</label>
					</td>
					<td>
						<?php echo TiendaSelect::booleans(@$row->eavattribute_required, 'eavattribute_required') ?>
					</td>
				</tr>
			</table>
			<input type="hidden" name="id" value="<?php echo @$row->eaventity_type; ?>" />
			<input type="hidden" name="task" value="" />
	</fieldset>
</form>