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
                $document = JFactory::getDocument();
                $document->addScriptDeclaration( $langScript );
                JHTML::_('script', 'openid.js');
        endif; ?>
        
        <?php
        
        $modules = JModuleHelper::getModules("tienda_checkout_login");
		$document	= JFactory::getDocument();
		$renderer	= $document->loadRenderer('module');
		$attribs 	= array();
		$attribs['style'] = 'xhtml';
		
		foreach ( @$modules as $mod ) 
		{
			echo $renderer->render($mod, $attribs);
		}
        
		
         ?>
        <?php if(empty($modules)) : ?>
        
        <?php echo  $this->loadTemplate('login'); ?>
       
    <?php endif; ?>
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