<?php defined('_JEXEC') or die('Restricted access'); ?>

<div class="dsc-table dsc-full">
    <div class="dsc-row">
        <div class="dsc-cell dsc-half">
        
            <h4>
                <?php echo JText::_('COM_TIENDA_RETURNING_USERS'); ?>
            </h4>
                
            <!-- LOGIN FORM -->
            
            <?php 
            if (JPluginHelper::isEnabled('authentication', 'openid')) 
            {
                $lang->load( 'plg_authentication_openid', JPATH_ADMINISTRATOR );
                $langScript =   'var JLanguage = {};'.
                        ' JLanguage.WHAT_IS_OPENID = \''.JText::_('COM_TIENDA_WHAT_IS_OPENID').'\';'.
                        ' JLanguage.LOGIN_WITH_OPENID = \''.JText::_('COM_TIENDA_LOGIN_WITH_OPENID').'\';'.
                        ' JLanguage.NORMAL_LOGIN = \''.JText::_('COM_TIENDA_NORMAL_LOGIN').'\';'.
                        ' var modlogin = 1;';
                $document = JFactory::getDocument();
                $document->addScriptDeclaration( $langScript );
                JHTML::_('script', 'openid.js');
            }
            
            $modules = JModuleHelper::getModules("tienda_checkout_login");
    		$document	= JFactory::getDocument();
    		$renderer	= $document->loadRenderer('module');
    		$attribs 	= array();
    		$attribs['style'] = 'xhtml';
    		
    		foreach ( @$modules as $mod ) 
    		{
    			echo $renderer->render($mod, $attribs);
    		}
            
    		if (empty($modules)) {
    		    echo $this->loadTemplate('login');
            } 
            ?>
            
        </div>
        
        <div class="dsc-cell dsc-half">
        
            <h4>
                <?php echo JText::_('COM_TIENDA_NEW_USERS'); ?>
            </h4>
            
            <ul class="unstyled">
                <?php if (Tienda::getInstance()->get('guest_checkout_enabled')) : ?>
                <li class="control">
                    <label for="checkout-method-guest" class="radio">
                        <input type="radio" value="guest" id="checkout-method-guest" name="checkout_method">
                        <?php echo JText::_('COM_TIENDA_CHECKOUT_AS_A_GUEST'); ?>
                    </label>
                </li>
                <?php endif; ?>

                <li class="control">
                    <label for="checkout-method-register" class="radio">
                        <input type="radio" value="register" id="checkout-method-register" name="checkout_method">
                        <?php echo JText::_( "COM_TIENDA_REGISTER" ); ?>
                    </label>
                </li>
            
            </ul>
            
            <div>
                <?php echo JText::_('COM_TIENDA_PLEASE_REGISTER_TO_CONTINUE_SHOPPING'); ?>
            </div>
            
            <a id="opc-guest-register-button" class="btn btn-primary" onclick="Opc.setMethod();"><?php echo JText::_('COM_TIENDA_CONTINUE') ?></a>
        
        </div>
    </div>
</div>