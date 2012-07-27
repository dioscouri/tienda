<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row;
JFilterOutput::objectHTMLSafe( $row );
?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" id="adminForm" >


			<table class="table table-striped table-bordered">
				<tr>
					<td width="100" align="right" class="key">
						<?php echo JText::_('COM_TIENDA_NAME'); ?>:
					</td>
					<td>
						<input type="text" name="eavattribute_label" id="eavattribute_label" size="48" maxlength="250" value="<?php echo @$row->eavattribute_label; ?>" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<?php echo JText::_('COM_TIENDA_ALIAS'); ?>:
					</td>
					<td>
						<input type="text" name="eavattribute_alias" id="eavattribute_alias" size="48" maxlength="250" value="<?php echo @$row->eavattribute_alias; ?>" />
					</td>
				</tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <label for="enabled">
                        <?php echo JText::_('COM_TIENDA_ENABLED'); ?>:
                        </label>
                    </td>
                    <td>
                        <?php echo TiendaSelect::btbooleanlist( 'enabled', '', @$row->enabled ); ?>
                    </td>
                </tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="eaventity_type">
						<?php echo JText::_('COM_TIENDA_ENTITY_TYPE'); ?>:
						</label>
					</td>
					<td>
						<?php echo TiendaSelect::entitytype(@$row->eaventity_type, 'eaventity_type'); ?>
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="eaventity_id">
						<?php echo JText::_('COM_TIENDA_ENTITY'); ?>:
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
									echo TiendaUrl::popup($url, JText::_('COM_TIENDA_SELECT_ENTITIES')); 
								}
							}
							else
							{
						?>
							<div class="note well">
								<?php echo JText::_('COM_TIENDA_CLICK_APPLY_TO_ADD_A_LINK_TO_AN_ENTITY_FOR_THIS_PRODUCT'); ?>
							</div>
						<?php 
							}
						?>
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="eavattribute_type">
						<?php echo JText::_('COM_TIENDA_DATA_TYPE'); ?>:
						</label>
					</td>
					<td>
						<?php echo TiendaSelect::attributetype(@$row->eavattribute_type, 'eavattribute_type'); ?>
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="editable_by">
						<?php echo JText::_('COM_TIENDA_EDITABLE_BY'); ?>:
						</label>
					</td>
					<td>
						<?php echo TiendaSelect::editableby(@$row->editable_by, 'editable_by'); ?>
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="eavattribute_required">
						<?php echo JText::_('COM_TIENDA_REQUIRED'); ?>:
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