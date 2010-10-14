<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php echo @$vars->token; ?>

    <p><?php echo JText::_( "THIS TOOL IMPORT DATA FROM A CSV FILE TO TIENDA" ); ?></p>

    <div class="note">
        <span style="float: right; font-size: large; font-weight: bold;"><?php echo JText::_( "STEP ONE OF THREE" ); ?></span>
        <p><?php echo JText::_( "PLEASE PROVIDE THE REQUESTED INFORMATION" ); ?></p>
    </div>
    
    <fieldset>
        <legend><?php echo JText::_('CSV INFORMATION'); ?></legend>
            <table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'File' ); ?>: *
                    </td>
                    <td>
                        <input type="file" name="file" id="file" size="48" value="<?php echo @$state->file; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
               
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Field Separator' ); ?>: *
                    </td>
                    <td>
                        <input type="text" name="field_separator" id="field_separator" size="5" maxlength="5" value="<?php echo @$state->field_separator; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'SubField Separator' ); ?>: *
                    </td>
                    <td>
                        <input type="text" name="subfield_separator" id="subfield_separator" size="5" maxlength="5" value="<?php echo @$state->subfield_separator; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Skip First Row' ); ?>?:
                    </td>
                    <td>
                        <input type="checkbox" name="skip_first" id="skip_first" value="1" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
            </table>    
    <br />
    * <?php echo JText::_('INDICATES A REQUIRED FIELD'); ?>
    </fieldset>
        