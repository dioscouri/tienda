<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('behavior.tooltip'); ?>

<div style="background-color: white; padding: 5px;">
    
    <p><?php echo JText::_( "Please provide the requested information to connect to your Lightspeed installation" ); ?></p>
    
    <fieldset>
        <legend><?php echo JText::_('DATABASE CONNECTION'); ?></legend>
            <table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'HOST' ); ?>: *
                    </td>
                    <td>
                        <input type="text" name="lightspeed_host" id="host" size="48" maxlength="250" value="<?php echo @$vars->config->get( 'lightspeed_host' ); ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'USERNAME' ); ?>: *
                    </td>
                    <td>
                        <input type="text" name="lightspeed_user" id="user" size="48" maxlength="250" value="<?php echo @$vars->config->get( 'lightspeed_user' ); ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'PASSWORD' ); ?>: *
                    </td>
                    <td>
                        <input type="password" name="lightspeed_password" id="password" size="48" maxlength="250" value="<?php echo @$vars->config->get( 'lightspeed_password' ); ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'DATABASE NAME' ); ?>: *
                    </td>
                    <td>
                        <input type="text" name="lightspeed_database" id="database" size="48" maxlength="250" value="<?php echo @$vars->config->get( 'lightspeed_database' ); ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key hasTip" title="<?php echo JText::_("TABLE PREFIX").'::'.JText::_( "Include the trailing underscore" ); ?>" >
                        <?php echo JText::_( 'TABLE PREFIX' ); ?>:
                    </td>
                    <td>
                        <input type="text" name="lightspeed_prefix" id="prefix" size="48" maxlength="250" value="<?php echo @$vars->config->get( 'lightspeed_prefix' ); ?>" />
                        <br/>
                        <span style="color: grey;"><?php echo JText::_( "Include the trailing underscore" ); ?></span>
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'DATABASE TYPE' ); ?>:
                    </td>
                    <td>
                        <input type="text" name="lightspeed_driver" id="driver" size="48" maxlength="250" value="<?php echo @$vars->config->get( 'lightspeed_driver', 'mysql' ); ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'DATABASE PORT' ); ?>:
                    </td>
                    <td>
                        <input type="text" name="lightspeed_port" id="port" size="48" maxlength="250" value="<?php echo @$vars->config->get( 'lightspeed_port', '3306' ); ?>" />
                    </td>
                    <td>
                    
                    </td>
                </tr>
            </table>    
        <br />
        * <?php echo JText::_('INDICATES A REQUIRED FIELD'); ?>
    </fieldset>
</div>    