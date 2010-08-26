<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $form = @$this->form; ?>
<script type="text/javascript">
<!--
    Window.onDomReady(function(){
        document.formvalidator.setHandler('passverify', function (value) { return ($('password').value == value); } );
    });
// -->
</script>
<div id="message-container" style="width:100%;"></div>
<?php
    if(isset($this->message)){
        $this->display('message');
    }
?>
<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" id="adminForms" name="adminForms" class="form-validate" >
	
<!--  
<?php //if ( $this->params->def( 'show_page_title', 1 ) ) : ?>
<div class="componentheading<?php //echo $this->escape($this->params->get('pageclass_sfx')); ?>"><?php // echo $this->escape($this->params->get('page_title')); ?></div>
<?php // endif; ?>
-->
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="contentpane">
<tr>
    <td height="40">
        <label id="emailmsg" for="email">
            <?php echo JText::_( 'Email' ); ?>:
        </label>
    </td>
    <td>
        <input type="text" id="email" name="email" size="40" value="" class="inputbox required validate-email" maxlength="100" /> *
    </td>
</tr>
<tr>
    <td width="30%" height="40">
        <label id="namemsg" for="name">
            <?php echo JText::_( 'Name' ); ?>:
        </label>
    </td>
    <td>
        <input type="text" name="name" id="name" size="40" value="" class="inputbox required" maxlength="50" /> *
    </td>
</tr>
<tr>
    <td height="40">
        <label id="usernamemsg" for="username">
            <?php echo JText::_( 'User name' ); ?>:
        </label>
    </td>
    <td>
        <input type="text" id="username" name="username" size="40" value="" class="inputbox required validate-username" maxlength="25" /> *
    </td>
</tr>
<tr>
    <td height="40">
        <label id="pwmsg" for="password">
            <?php echo JText::_( 'Password' ); ?>:
        </label>
    </td>
    <td>
        <input class="inputbox required validate-password" type="password" id="password" name="password" size="40" value="" /> *
    </td>
</tr>
<tr>
    <td height="40">
        <label id="pw2msg" for="password2">
            <?php echo JText::_( 'Verify Password' ); ?>:
        </label>
    </td>
    <td>
        <input class="inputbox required validate-passverify" type="password" id="password2" name="password2" size="40" value="" /> *
    </td>
</tr>
<tr>
    <td colspan="2" height="40">
        <?php echo JText::_( 'REGISTER_REQUIRED' ); ?>
    </td>
</tr>
</table>
    <button class="button validate" type="submit"><?php echo JText::_('Register'); ?></button>
    <?php $verifyUrl = "index.php?option=com_tienda&controller=users&format=raw&task=verifyFields"; ?>
	<input type='button' class='button' onclick='tiendaFormValidation("<?php echo $verifyUrl; ?>", "message-container" ,"save", document.adminForms)' value='<?php echo JText::_( 'Submit' ); ?>' />
	
    <input type="hidden" name="task" value="save" />
    <input type="hidden" name="return" value="<?php echo JRequest::getVar('return'); ?>" />
    <?php echo JHTML::_( 'form.token' ); ?>
</form>
