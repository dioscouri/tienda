<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php echo @$vars->token; ?>

    <p><?php echo JText::_( "THIS TOOL IMPORTS DATA FROM A CSV FILE TO TIENDA" ); ?></p>

    <div class="note">
        <span style="float: right; font-size: large; font-weight: bold;"><?php echo JText::_( "STEP TWO OF THREE" ); ?></span>
        <p><?php echo JText::_( "PLEASE REVIEW THE FOLLOWING INFORMATION" ); ?></p>
    </div>
    
    <fieldset>
        <legend><?php echo JText::_('CSV INFORMATION'); ?></legend>
            <table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'File' ); ?>: *
                    </td>
                    <td>
                    	<?php echo @$state->uploaded_file; ?>
                        <input type="hidden" name="uploaded_file" id="uploaded_file" size="48" value="<?php echo @$state->uploaded_file; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
               
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Field Separator' ); ?>: *
                    </td>
                    <td>
                    	<?php echo @$state->field_separator; ?>
                        <input type="hidden" name="field_separator" id="field_separator" size="5" maxlength="5" value="<?php echo @$state->field_separator; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'SubField Separator' ); ?>: *
                    </td>
                    <td>
                    	<?php echo @$state->subfield_separator; ?>
                        <input type="hidden" name="subfield_separator" id="subfield_separator" size="5" maxlength="5" value="<?php echo @$state->subfield_separator; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Skip First Row' ); ?>?:
                    </td>
                    <td>
                    	<?php if(@$state->skip_first) echo JText::_('Yes'); else echo JText::_('No') ; ?>
                        <input type="hidden" name="skip_first" id="skip_first" value="<?php echo @$state->skip_first; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
            </table>    
    <br />
    
    </fieldset>
        