<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php echo @$vars->token; ?>

    <p><?php echo JText::_('COM_TIENDA_THIS_TOOL_MIGRATES_DATA_FROM_VM_TO_TIENDA'); ?></p>

    <div class="note">
        <span style="float: right; font-size: large; font-weight: bold;"><?php echo JText::_('COM_TIENDA_STEP_ONE_OF_THREE'); ?></span>
        <p><?php echo JText::_('COM_TIENDA_PLEASE_PROVIDE_THE_REQUESTED_INFORMATION'); ?></p>
    </div>
    
    <fieldset>
        <legend><?php echo JText::_('COM_TIENDA_DATABASE_CONNECTION'); ?></legend>
            <table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_HOST'); ?>: *
                    </td>
                    <td>
                        <input type="text" name="host" id="host" size="48" maxlength="250" value="<?php echo @$state->host; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_USERNAME'); ?>: *
                    </td>
                    <td>
                        <input type="text" name="user" id="user" size="48" maxlength="250" value="<?php echo @$state->user; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_PASSWORD'); ?>: *
                    </td>
                    <td>
                        <input type="password" name="password" id="password" size="48" maxlength="250" value="<?php echo @$state->password; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_DATABASE_NAME'); ?>: *
                    </td>
                    <td>
                        <input type="text" name="database" id="database" size="48" maxlength="250" value="<?php echo @$state->database; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_JOOMLA_TABLE_PREFIX'); ?>: *
                    </td>
                    <td>
                        <input type="text" name="prefix" id="prefix" size="48" maxlength="250" value="<?php echo @$state->prefix; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_VIRTUEMART_TABLE_PREFIX'); ?>: *
                    </td>
                    <td>
                        <input type="text" name="vm_prefix" id="vm_prefix" size="48" maxlength="250" value="<?php echo @$state->vm_prefix; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_DATABASE_TYPE'); ?>:
                    </td>
                    <td>
                        <input type="text" name="driver" id="driver" size="48" maxlength="250" value="<?php echo @$state->driver; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_DATABASE_PORT'); ?>:
                    </td>
                    <td>
                        <input type="text" name="port" id="port" size="48" maxlength="250" value="<?php echo @$state->port; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_EXTERNAL_SITE_URL'); ?>:
                    </td>
                    <td>
                        <input type="text" name="external_site_url" id="external_site_url" size="48" maxlength="250" value="<?php echo @$state->external_site_url; ?>" />
                    </td>
                    <td>
                    	<?php echo JText::_('COM_TIENDA_EXTERNAL_SITE_URL_DESC');?>
                    </td>
                </tr>
            </table>    
    <br />
    * <?php echo JText::_('COM_TIENDA_INDICATES_A_REQUIRED_FIELD'); ?>
    </fieldset>
        