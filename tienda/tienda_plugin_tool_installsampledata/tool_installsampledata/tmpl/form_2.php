<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php echo @$vars->token; 
debug(31241, $state);
debug(312312, 'form2');
?>

     <p><?php echo JText::_( "THIS TOOL INSTALL SAMPLE DATA TO TIENDA" ); ?></p>

    <div class="note">
        <span style="float: right; font-size: large; font-weight: bold;"><?php echo JText::_( "STEP TWO OF THREE" ); ?></span>
        <p><?php echo JText::_( "YOU PROVIDED THE FOLLOWING INFORMATION" ); ?></p>
    </div>

    <fieldset>
        <legend><?php echo JText::_('SAMPLE DATA SET/S'); ?></legend>
            <table class="admintable">
            	<?php foreach($state->sampledata as $sample):?>
                <tr>                    
                    <td>
                        <?php echo ucfirst($sample); ?>
                        <input type="hidden" name="sampledata[<?php echo $sample;?>]" id="host" size="48" maxlength="250" value="<?php echo $sample; ?>" />
                    </td>                  
                </tr>     
                <?php endforeach;?>
            </table>    
    </fieldset>