<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php echo @$vars->token; ?>

    <p><?php echo JText::_('COM_TIENDA_THIS_TOOL_IMPORTS_DATA_FROM_AN_XML_FILE_TO_TIENDA'); ?></p>

    <div class="note">
        <span style="float: right; font-size: large; font-weight: bold;"><?php echo JText::_('COM_TIENDA_STEP_ONE_OF_THREE'); ?></span>
        <p><?php echo JText::_('COM_TIENDA_PLEASE_PROVIDE_THE_REQUESTED_INFORMATION'); ?></p>
    </div>
    
    <fieldset>
        <legend><?php echo JText::_('COM_TIENDA_FILE_INFORMATION'); ?></legend>
            <table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_XML_FILE'); ?>: *
                    </td>
                    <td>
                        <input type="file" name="file" id="file" size="48" value="<?php echo @$state->file; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                 <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_IMAGES_ZIP_FILE'); ?>:
                    </td>
                    <td>
                        <input type="file" name="images_zip_file" id="images_zip_file" size="48" value="<?php echo @$state->images_zip_file; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_FILES_ZIP_FILE'); ?>:
                    </td>
                    <td>
                        <input type="file" name="files_zip_file" id="files_zip_file" size="48" value="<?php echo @$state->files_zip_file; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
               
            </table>    
    <br />
    * <?php echo JText::_('COM_TIENDA_INDICATES_A_REQUIRED_FIELD'); ?>
    </fieldset>
        