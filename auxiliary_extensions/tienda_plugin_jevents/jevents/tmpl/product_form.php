<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $message = @$vars->message; ?>
<?php $product = $vars->product; ?>
<?php $event_details = $vars->event_details; ?>

<fieldset><legend><?php echo JText::_( "JEvents Integration" ); ?></legend>
<table class="admintable" style="width: 100%;">
	<tr>
		<td style="vertical-align: top; width: 100px; text-align: right;"
			class="key"><?php echo JText::_( 'This Product Corresponds With' ); ?>:
		</td>
		<td><?php if( !empty($event_details) && $event_details !=null) {
			?>
		<fieldset>
		<table class="admintable" style="width: 100%;">
			<tr>
				<td style="vertical-align: top; width: 100px; text-align: right;"
			class="key"> <b> <?php  echo  JText::_('JEvent Summary');?> </b> </td>
				<td> <?php echo $event_details->summary; ?></td>
			</tr>
			<tr>
				<td style="vertical-align: top; width: 100px; text-align: right;"
			class="key"> <b> <?php  echo  JText::_('JEvent Start Date');?> </b> </td>
				<td> <?php  echo JHTML::date( $event_details->dtstart);?></td>
			</tr>
			<tr>
				<td style="vertical-align: top; width: 100px; text-align: right;"
			class="key"> <b> <?php  echo  JText::_('JEvent End Date');?> </b> </td>
				<td> <?php  echo JHTML::date( $event_details->dtend);?></td>
			</tr>
		</table>
		</fieldset>
		<?php } else {
			  echo  JText::_('No JEvent Selected');
		} ?>
		
		</td>
	</tr>
	<tr>
		<td style="vertical-align: top; width: 100px; text-align: right;"
			class="key"><?php echo JText::_( 'Select Event' ); ?>:</td>
		<td><?php echo @$vars->elementEvent_terms ?> <?php echo @$vars->resetEvent_terms ?>
		</td>
	</tr>

</table>
</fieldset>




