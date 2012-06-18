<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php echo @$vars->token; ?>

    <p><?php echo JText::_('COM_TIENDA_THIS_TOOL_IMPORTS_DATA_FROM_A_CSV_FILE_TO_TIENDA'); ?></p>

    <div class="note">
        <span style="float: right; font-size: large; font-weight: bold;"><?php echo JText::_('COM_TIENDA_STEP_TWO_OF_THREE'); ?></span>
        <p><?php echo JText::_('COM_TIENDA_PLEASE_REVIEW_THE_FOLLOWING_INFORMATION'); ?></p>
    </div>
    
    <fieldset>
        <legend><?php echo JText::_('COM_TIENDA_CSV_INFORMATION'); ?></legend>
            <table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_FILE'); ?>: *
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
                        <?php echo JText::_('COM_TIENDA_FIELD_SEPARATOR'); ?>: *
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
                        <?php echo JText::_('COM_TIENDA_SUBFIELD_SEPARATOR'); ?>: *
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
                        <?php echo JText::_('COM_TIENDA_SKIP_FIRST_ROW'); ?>?:
                    </td>
                    <td>
                    	<?php if(@$state->skip_first) echo JText::_('COM_TIENDA_YES'); else echo JText::_('COM_TIENDA_NO') ; ?>
                        <input type="hidden" name="skip_first" id="skip_first" value="<?php echo @$state->skip_first; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
            </table>    
    <br />
    
    </fieldset>
        