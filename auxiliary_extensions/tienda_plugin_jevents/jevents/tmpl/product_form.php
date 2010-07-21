<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $message = @$vars->message; ?>
<?php $product = $vars->product; ?>
<?php $event = $vars->event; ?>

<fieldset>
    <legend><?php echo JText::_( "JEvents Integration" ); ?></legend>
    
    <table class="admintable" style="width: 100%;">
    <?php if ( !empty($event->event_id) ) { ?>
        <tr>
            <td colspan="2"> 
                <b><?php echo JText::_( "This Product Corresponds with the Following JEvents Event" ); ?></b>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top; width: 100px; text-align: right;" class="key"> 
                <b> <?php  echo  JText::_('Summary');?> </b> 
            </td>
            <td> 
            <a href="index.php?option=com_jevents&task=icalevent.edit&evid=<?php echo $event->ev_id; ?>" target="_blank">
                <?php echo $event->summary; ?>
            </a>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top; width: 100px; text-align: right;" class="key"> 
                <b> <?php  echo  JText::_('Location');?> </b> 
            </td>
            <td> 
                <?php echo $event->location; ?>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top; width: 100px; text-align: right;" class="key"> 
                <b> <?php  echo  JText::_('Start Date');?> </b> 
            </td>
            <td> <?php  echo JHTML::date( $event->dtstart);?></td>
        </tr>
        <tr>
            <td style="vertical-align: top; width: 100px; text-align: right;" class="key"> 
                <b> <?php  echo  JText::_('End Date');?> </b> 
            </td>
            <td> <?php  echo JHTML::date( $event->dtend);?></td>
        </tr>
        <?php } ?>
    	<tr>
    		<td style="vertical-align: top; width: 100px; text-align: right;" class="key">
    			<?php echo JText::_( 'Select Event' ); ?>:
    		</td>
    		<td>
                <?php echo @$vars->elementEvent_terms ?> <?php echo @$vars->resetEvent_terms ?>
    		</td>
    	</tr>
    </table>
</fieldset>




