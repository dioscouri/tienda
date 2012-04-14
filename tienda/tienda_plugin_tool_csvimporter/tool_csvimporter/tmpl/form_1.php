<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php echo @$vars->token; ?>

    <p><?php echo JText::_('COM_TIENDA_THIS_TOOL_IMPORTS_DATA_FROM_A_CSV_FILE_TO_TIENDA'); ?></p>

    <div class="note">
        <span style="float: right; font-size: large; font-weight: bold;"><?php echo JText::_('COM_TIENDA_STEP_ONE_OF_THREE'); ?></span>
        <p><?php echo JText::_('COM_TIENDA_PLEASE_PROVIDE_THE_REQUESTED_INFORMATION'); ?></p>
    </div>
    
    <fieldset>
        <legend><?php echo JText::_('COM_TIENDA_CSV_INFORMATION'); ?></legend>
            <table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_FILE'); ?>: *
                    </td>
                    <td>
                        <input type="file" name="file" id="file" size="48" value="<?php echo @$state->file; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
               
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_FIELD_SEPARATOR'); ?>: *
                    </td>
                    <td>
                        <input type="text" name="field_separator" id="field_separator" size="5" maxlength="5" value="<?php echo @$state->field_separator; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_SUBFIELD_SEPARATOR'); ?>: *
                    </td>
                    <td>
                        <input type="text" name="subfield_separator" id="subfield_separator" size="5" maxlength="5" value="<?php echo @$state->subfield_separator; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_SKIP_FIRST_ROW'); ?>?:
                    </td>
                    <td>
                        <input type="checkbox" name="skip_first" id="skip_first" value="1" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
            </table>    
    <br />
    * <?php echo JText::_('COM_TIENDA_INDICATES_A_REQUIRED_FIELD'); ?>
    </fieldset>
        