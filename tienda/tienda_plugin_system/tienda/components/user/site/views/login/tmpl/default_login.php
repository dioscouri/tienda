<?php 
defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
$lang = &JFactory::getLanguage();
$lang->load( 'com_tienda', JPATH_SITE ); 
?>

<table style="width: 100%;"  >
<tr>
    <td style="vertical-align: top; padding: 5px;">
    
        <div class='componentheading'>
            <span><?php echo JText::_( "Returning Users" ); ?></span>
        </div>
            
        <!-- LOGIN FORM -->
        
        <?php if (JPluginHelper::isEnabled('authentication', 'openid')) :
                $lang->load( 'plg_authentication_openid', JPATH_ADMINISTRATOR );
                $langScript =   'var JLanguage = {};'.
                                ' JLanguage.WHAT_IS_OPENID = \''.JText::_( 'WHAT_IS_OPENID' ).'\';'.
                                ' JLanguage.LOGIN_WITH_OPENID = \''.JText::_( 'LOGIN_WITH_OPENID' ).'\';'.
                                ' JLanguage.NORMAL_LOGIN = \''.JText::_( 'NORMAL_LOGIN' ).'\';'.
                                ' var modlogin = 1;';
                $document = &JFactory::getDocument();
                $document->addScriptDeclaration( $langScript );
                JHTML::_('script', 'openid.js');
        endif; ?>
        
        <form action="<?php echo JRoute::_( 'index.php', true, TiendaConfig::getInstance()->get('usesecure', '0') ); ?>" method="post" name="login" id="form-login" >
        
            <table>
            <tr>
                <td style="height: 40px;">
                    <?php echo JText::_('USERNAME'); ?>
                </td>
                <td>
                    <input type="text" name="username" class="inputbox" size="18" alt="username" />
                </td>
            </tr>
            <tr>
                <td style="height: 40px;">
                    <?php echo JText::_('PASSWORD'); ?>
                </td>
                <td>
                    <input type="password" name="passwd" class="inputbox" size="18" alt="password" />
                </td>
            </tr>
            <?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
            <tr>
                <td>
                    <?php echo JText::_('REMEMBER ME'); ?>
                </td>
                <td style="text-align: right;">
                    <span style="float: left">
                        <input type="checkbox" name="remember" class="inputbox" value="yes"/>
                    </span>
                    <input type="submit" name="submit" class="button" value="<?php echo JText::_('LOGIN') ?>" />
                </td>
            </tr>
            <?php endif; ?>
            <tr>
                <td style="height: 40px;">
                    <ul>
                        <li>
                            <?php // TODO Can we do this in a lightbox or something? Why does the user have to leave? ?>
                            <a href="<?php echo JRoute::_( 'index.php?option=com_user&view=reset' ); ?>">
                            <?php echo JText::_('FORGOT_YOUR_PASSWORD'); ?></a>
                        </li>
                        <li>
                            <?php // TODO Can we do this in a lightbox or something? Why does the user have to leave? ?>
                            <a href="<?php echo JRoute::_( 'index.php?option=com_user&view=remind' ); ?>">
                            <?php echo JText::_('FORGOT_YOUR_USERNAME'); ?></a>
                        </li>
                    </ul>
                </td>
                <td>
                </td>
            </tr>
            </table>
        
            <input type="hidden" name="option" value="com_user" />
            <input type="hidden" name="task" value="login" />
            <input type="hidden" name="return" value="<?php echo $this->return; ?>" />
            <?php echo JHTML::_( 'form.token' ); ?>
        </form>
    
    </td>
    <td style="vertical-align: top; padding: 5px; width: 50%;">
    
        <div class='componentheading'>
            <span><?php echo JText::_( "New Users" ); ?></span>
        </div>
        <!-- REGISTRATION -->

        <table>
        <tr>
            <td style="height: 40px; padding: 5px;">
                <?php echo JTEXT::_('PLEASE REGISTER TO CONTINUE SHOPPING'); ?>
            </td>
        </tr>
        <tr>
            <td>
                <input type="button" class="button" onclick="window.location='<?php echo JRoute::_( "index.php?option=com_user&view=register" ); ?>'" value="<?php echo JText::_( "REGISTER" ); ?>" />
            </td>
        </tr>
        </table>
        
    </td>
</tr>
</table>
