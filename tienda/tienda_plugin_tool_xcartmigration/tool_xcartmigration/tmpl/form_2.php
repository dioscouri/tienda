<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php echo @$vars->token; ?>

    <p><?php echo JText::_( "THIS TOOL MIGRATES DATA FROM XCART TO TIENDA" ); ?></p>

    <div class="note">
        <span style="float: right; font-size: large; font-weight: bold;"><?php echo JText::_( "STEP TWO OF THREE" ); ?></span>
        <p><?php echo JText::_( "YOU PROVIDED THE FOLLOWING INFORMATION" ); ?></p>
    </div>

    <fieldset>
        <legend><?php echo JText::_('DATABASE CONNECTION'); ?></legend>
            <table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'HOST' ); ?>:
                    </td>
                    <td>
                        <?php echo @$state->host; ?>
                        <input type="hidden" name="host" id="host" size="48" maxlength="250" value="<?php echo @$state->host; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_("COM_TIENDA_USERNAME"); ?>:
                    </td>
                    <td>
                        <?php echo @$state->user; ?>
                        <input type="hidden" name="user" id="user" size="48" maxlength="250" value="<?php echo @$state->user; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'PASSWORD' ); ?>:
                    </td>
                    <td>
                       *****
                        <input type="hidden" name="password" id="password" size="48" maxlength="250" value="<?php echo @$state->password; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'DATABASE NAME' ); ?>:
                    </td>
                    <td>
                        <?php echo @$state->database; ?>
                        <input type="hidden" name="database" id="database" size="48" maxlength="250" value="<?php echo @$state->database; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'TABLE PREFIX' ); ?>:
                    </td>
                    <td>
                        <?php echo @$state->prefix; ?>
                        <input type="hidden" name="prefix" id="prefix" size="48" maxlength="250" value="<?php echo @$state->prefix; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'DATABASE TYPE' ); ?>:
                    </td>
                    <td>
                        <?php echo @$state->driver; ?>
                        <input type="hidden" name="driver" id="driver" size="48" maxlength="250" value="<?php echo @$state->driver; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'DATABASE PORT' ); ?>:
                    </td>
                    <td>
                        <?php echo @$state->port; ?>
                        <input type="hidden" name="port" id="port" size="48" maxlength="250" value="<?php echo @$state->port; ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
            </table>    
    </fieldset>