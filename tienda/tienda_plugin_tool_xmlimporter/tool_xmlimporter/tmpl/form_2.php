<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php echo @$vars->token; ?>

    <p><?php echo JText::_('THIS TOOL IMPORTS DATA FROM A XML FILE TO TIENDA'); ?></p>

    <div class="note">
        <span style="float: right; font-size: large; font-weight: bold;"><?php echo JText::_('STEP TWO OF THREE'); ?></span>
        <p><?php echo JText::_('PLEASE REVIEW THE FOLLOWING INFORMATION'); ?></p>
    </div>
    
    <fieldset>
        <legend><?php echo JText::_('FILE INFORMATION'); ?></legend>
            <table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('XML File'); ?>: *
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
                        <?php echo JText::_('Images ZIP File'); ?>: *
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
                        <?php echo JText::_('Files ZIP File'); ?>: *
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
        