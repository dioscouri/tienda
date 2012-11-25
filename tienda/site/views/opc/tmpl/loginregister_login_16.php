<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php $url = JRoute::_( "index.php?option=com_tienda&view=opc", false ); ?>

<form action="<?php echo JRoute::_( 'index.php', true, Tienda::getInstance()->get('usesecure', '0') ); ?>" method="post" name="login" id="form-login">

    <ul>
        <li>
            <label>
                <?php echo JText::_('COM_TIENDA_USERNAME'); ?> <span class>*</span>
            </label>
            <input id="tienda-username" type="text" name="username" class="inputbox" size="18" alt="username" />
        </li>
        <li>
            <label>
                <?php echo JText::_('COM_TIENDA_PASSWORD'); ?><span>*</span>
            </label>
            <input id="tienda-password" type="password" name="password" class="inputbox" size="18" alt="password" />
        </li>
        <?php if (JPluginHelper::isEnabled('system', 'remember')) { ?>
        <li>
            <label>
                <?php echo JText::_('COM_TIENDA_REMEMBER_ME'); ?>
            </label>
            <input id="tienda-remember" type="checkbox" name="remember" class="inputbox" value="yes" />
        </li>
        <?php } ?>
    </ul>

    <ul>
        <li> 
            <a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>"> 
                <?php echo JText::_('COM_TIENDA_FORGOT_YOUR_PASSWORD'); ?>
            </a>
        </li>
        <li>
            <a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>"> 
                <?php echo JText::_('COM_TIENDA_FORGOT_YOUR_USERNAME'); ?>
            </a>
        </li>
    </ul>

    <input type="submit" name="submit" class="button" value="<?php echo JText::_('COM_TIENDA_LOGIN') ?>" />

    <input type="hidden" name="option" value="com_users" /> <input type="hidden" name="task" value="user.login" /> <input type="hidden" name="return" value="<?php echo base64_encode( $url ); ?>" />
    <?php echo JHTML::_( 'form.token' ); ?>
</form>