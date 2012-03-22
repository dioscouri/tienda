<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php echo @$vars->token; ?>

    <p><?php echo JText::_('THIS TOOL IMPORTS DATA FROM AN XML FILE TO TIENDA'); ?></p>

    <div class="note">
        <span style="float: right; font-size: large; font-weight: bold;"><?php echo JText::_('STEP ONE OF THREE'); ?></span>
        <p><?php echo JText::_('PLEASE PROVIDE THE REQUESTED INFORMATION'); ?></p>
    </div>
    
    <fieldset>
        <legend><?php echo JText::_('FILE INFORMATION'); ?></legend>
            <table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('XML File'); ?>: *
                    </td>
                    <td>
                        <input type="file" name="file" id="file" size="48" value="<?php echo @$state->file; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                 <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('Images ZIP File'); ?>:
                    </td>
                    <td>
                        <input type="file" name="images_zip_file" id="images_zip_file" size="48" value="<?php echo @$state->images_zip_file; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('Files ZIP File'); ?>:
                    </td>
                    <td>
                        <input type="file" name="files_zip_file" id="files_zip_file" size="48" value="<?php echo @$state->files_zip_file; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
               
            </table>    
    <br />
    * <?php echo JText::_('INDICATES A REQUIRED FIELD'); ?>
    </fieldset>
        