<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php echo @$vars->token; ?>
	<p><?php echo JText::_('THIS TOOL INSTALL SAMPLE DATA TO TIENDA'); ?></p>
    <div class="note">
        <span style="float: right; font-size: large; font-weight: bold;"><?php echo JText::_('STEP TWO OF THREE'); ?></span>
        <p><?php echo JText::_('YOU PROVIDED THE FOLLOWING INFORMATION'); ?></p>
    </div>
    <fieldset>
        <legend><?php echo JText::_('Sample Data Information'); ?></legend>
            <table class="admintable">
            	<?php if($state->install_default == '0' || empty($state->install_default)) {?>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('File'); ?>: *
                    </td>
                    <td>
                    	<?php echo @$state->uploaded_file; ?>
                        <input type="hidden" name="uploaded_file" id="uploaded_file" size="48" value="<?php echo @$state->uploaded_file; ?>" />                        
                    </td>                    
                </tr>
                <?php }else{ ?>                  
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('Default Sample Data'); ?>:
                    </td>
                    <td>
                    	<?php echo JText::_(strtoupper($state->sampledata))." ".JText::_('STORE'); ?>
        				<input type="hidden" name="sampledata" id="host" size="48" value="<?php echo $state->sampledata; ?>" />        				
                    </td>                    
                </tr>
                <?php } ?>
            </table>                
            <input type="hidden" name="install_default" id="install_default" value="<?php echo $state->install_default; ?>" />
    <br />
    
    </fieldset>
        