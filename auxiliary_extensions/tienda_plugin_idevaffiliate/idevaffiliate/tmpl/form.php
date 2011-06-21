<?php defined('_JEXEC') or die('Restricted access'); ?>
<div style="float: left; width: 50%;">
	<fieldset>
		<legend>
			<?php echo JText::_("iDevAffiliate Integration");?>
		</legend>
		<?php if (Tienda::getClass('TiendaHelperAmigos', 'helpers.amigos')->isInstalled()) : ?>
		<table class="admintable" style="width: 100%;">
			<tr>
				<td style="width: 125px; text-align: right;" class="key hasTip" title="<?php echo JText::_("Commission Rate Override") . '::' . JText::_("Commission Rate Override Tip");?>" >
				<?php echo JText::_('Commission Rate Override');?>:
				</td>
				<td>
				<input name="amigos_commission_override" id="amigos_commission_override" value="<?php echo @$row->product_parameters->get('amigos_commission_override');?>" size="10" maxlength="10" type="text" />
				</td>
			</tr>
		</table>
		<?php else :?>
		<div class="note">
			<?php echo JText::_("Amigos Installation Notice");?>
		</div>
		<?php endif;?>
	</fieldset>
</div>