<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $message = @$vars->message; ?>
<?php $product = $vars->product; ?>

<fieldset>
<legend><?php echo JText::_( "JEvents Integration" ); ?></legend>
<table class="admintable" style="width: 100%;">
    <tr>
        <td style="vertical-align: top; width: 100px; text-align: right;" class="key">
            <?php echo JText::_( 'This Product Corresponds With' ); ?>:
        </td>
        <td>
            [Here Display Summary Data About the JEvent Event, such as Date, etc, if there is an associated event]
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top; width: 100px; text-align: right;" class="key">
            <?php echo JText::_( 'Select Event' ); ?>:
        </td>
        <td>
            <?php echo @$vars->elementEvent_terms ?>
            <?php echo @$vars->resetEvent_terms ?>               
        </td>
    </tr>
    
</table>
</fieldset>




        