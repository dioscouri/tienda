<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php echo @$vars->token; ?>

    <p><?php echo JText::_('COM_TIENDA_THIS_TOOL_IMPORTS_DATA_FROM_AN_XML_FILE_TO_TIENDA'); ?></p>

    <div class="note">
        <span style="float: right; font-size: large; font-weight: bold;"><?php echo JText::_('COM_TIENDA_STEP_TWO_OF_THREE'); ?></span>
        <p><?php echo JText::_('COM_TIENDA_PLEASE_REVIEW_THE_FOLLOWING_INFORMATION'); ?></p>
    </div>
    
    <fieldset>
        <legend><?php echo JText::_('COM_TIENDA_FILE_INFORMATION'); ?></legend>
            <table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_XML_FILE'); ?>: *
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
                        <?php echo JText::_('COM_TIENDA_IMAGES_ZIP_FILE'); ?>: *
                    </td>
                    <td>
                    	<?php echo @$state->uploaded_images_zip_file; ?>
                        <input type="hidden" name="uploaded_images_zip_file" id="uploaded_images_zip_file" size="48" value="<?php echo @$state->uploaded_images_zip_file; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_FILES_ZIP_FILE'); ?>: *
                    </td>
                    <td>
                    	<?php echo @$state->uploaded_files_zip_file; ?>
                        <input type="hidden" name="uploaded_files_zip_file" id="uploaded_files_zip_file" size="48" value="<?php echo @$state->uploaded_files_zip_file; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
               
            </table>    
    <br />
    
    </fieldset>
        