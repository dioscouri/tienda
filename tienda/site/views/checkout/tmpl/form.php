<?php defined('_JEXEC') or die('Restricted access');?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');?>
<?php $url = JRoute::_( "index.php?option=com_tienda&view=checkout", false ); ?>

<table style="width: 100%;">
<tr>
    <td style="vertical-align: top; padding: 5px; border-right: 1px solid #CCC;">
    
        <div class='componentheading'>
            <span><?php echo JText::_('COM_TIENDA_RETURNING_USERS'); ?></span>
        </div>
            
        <!-- LOGIN FORM -->
        
        <?php if (JPluginHelper::isEnabled('authentication', 'openid')) :
                $lang->load( 'plg_authentication_openid', JPATH_ADMINISTRATOR );
                $langScript =   'var JLanguage = {};'.
                                ' JLanguage.WHAT_IS_OPENID = \''.JText::_('COM_TIENDA_WHAT_IS_OPENID').'\';'.
                                ' JLanguage.LOGIN_WITH_OPENID = \''.JText::_('COM_TIENDA_LOGIN_WITH_OPENID').'\';'.
                                ' JLanguage.NORMAL_LOGIN = \''.JText::_('COM_TIENDA_NORMAL_LOGIN').'\';'.
                                ' var modlogin = 1;';
                $document = &JFactory::getDocument();
                $document->addScriptDeclaration( $langScript );
                JHTML::_('script', 'openid.js');
        endif; ?>
        
        <form action="<?php echo JRoute::_( 'index.php', true, Tienda::getInstance()->get('usesecure', '0') ); ?>" method="post" name="login" id="form-login" >
        
            <table>
            <tr>
                <td style="height: 40px;">
                    <?php echo JText::_('COM_TIENDA_USERNAME'); ?> <span class>*</span>
                </td>
                <td>
                    <input type="text" name="username" class="inputbox" size="18" alt="username" />
                </td>
            </tr>
            <tr>
                <td style="height: 40px;">
                    <?php echo JText::_('COM_TIENDA_PASSWORD'); ?><span>*</span>
                </td>
                <td>
                    <input type="password" name="passwd" class="inputbox" size="18" alt="password" />
                </td>
            </tr>
            <?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
            <tr>
                <td>
                    <?php echo JText::_('COM_TIENDA_REMEMBER_ME'); ?>
                </td>
                <td>
                    <span style="float: left">
                        <input type="checkbox" name="remember" class="inputbox" value="yes"/>
                    </span>
                </td>
            </tr>
            <?php endif; ?>
            <tr>
                <td>
                </td>
                <td style="text-align: right;">
                    <input type="submit" name="submit" class="button" value="<?php echo JText::_('COM_TIENDA_LOGIN') ?>" />
                </td>
            </tr>
            <tr>
                <td style="height: 40px;">
                    <ul>
                        <li>
                            <?php // TODO Can we do this in a lightbox or something? Why does the user have to leave? ?>
                            <a href="<?php echo JRoute::_( 'index.php?option=com_user&view=reset' ); ?>">
                            <?php echo JText::_('COM_TIENDA_FORGOT_YOUR_PASSWORD'); ?></a>
                        </li>
                        <li>
                            <?php // TODO Can we do this in a lightbox or something? Why does the user have to leave? ?>
                            <a href="<?php echo JRoute::_( 'index.php?option=com_user&view=remind' ); ?>">
                            <?php echo JText::_('COM_TIENDA_FORGOT_YOUR_USERNAME'); ?></a>
                        </li>
                    </ul>
                </td>
                <td>
                </td>
            </tr>
            </table>
        
            <input type="hidden" name="option" value="com_user" />
            <input type="hidden" name="task" value="login" />
            <input type="hidden" name="return" value="<?php echo base64_encode( $url ); ?>" />
            <?php echo JHTML::_( 'form.token' ); ?>
        </form>
    
    </td>
    <td style="vertical-align: top; padding: 5px; width: 50%;">
    
        <div class='componentheading'>
            <span><?php echo JText::_('COM_TIENDA_NEW_USERS'); ?></span>
        </div>
        <!-- REGISTRATION -->

        <table>
        <tr>
            <td style="height: 40px; padding: 5px;">
                <?php echo JText::_('COM_TIENDA_PLEASE_REGISTER_TO_CONTINUE_SHOPPING'); ?>
            </td>
        </tr>
        <tr>
            <td>            
            <?php if (Tienda::getInstance()->get('one_page_checkout')){ ?>	
             	<input type="button" class="button" onclick="tiendaGetRegistrationForm( 'tienda_checkout_method', '', '' ); " value="<?php echo JText::_('COM_TIENDA_REGISTER'); ?>" />
            <?php }else{?>	
                <input type="button" class="button" onclick="window.location='<?php echo JRoute::_( "index.php?option=com_tienda&view=checkout&register=1&Itemid=".$this->checkout_itemid, false ); ?>'" value="<?php echo JText::_('COM_TIENDA_REGISTER'); ?>" />
            <?php }?>
            </td>
        </tr>
        </table>

        <div class="reset"></div>
        
        <?php if (Tienda::getInstance()->get('guest_checkout_enabled')) : ?>
            <div class='componentheading' style="margin-top:15px;">
                <span><?php echo JText::_('COM_TIENDA_CHECKOUT_AS_A_GUEST'); ?></span>
            </div>
            <!-- REGISTRATION -->
        
            <table>
            <tr>
                <td style="height: 40px; padding: 5px;">
                    <?php echo JText::_('COM_TIENDA_CHECKOUT_AS_A_GUEST_DESC'); ?>
                </td>
            </tr>
            <tr>
                <td>
                <?php  if (Tienda::getInstance()->get('one_page_checkout')){?>
				<input id="tienda_btn_register" type="button" class="button" onclick="tiendaGetCustomerInfo( 'onShowCustomerInfo');" value="<?php echo JText::_('COM_TIENDA_CHECKOUT_AS_A_GUEST'); ?>" />
          
				<?php }else{?>
                    <input type="button" class="button" onclick="window.location='<?php echo JRoute::_( "index.php?option=com_tienda&view=checkout&guest=1&Itemid=".$this->checkout_itemid, false ); ?>'" value="<?php echo JText::_('COM_TIENDA_CHECKOUT_AS_A_GUEST'); ?>" />
               	<?php }?>
                </td>
            </tr>
            </table>
        <?php endif; ?>        
    </td>
</tr>
</table>