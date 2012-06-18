<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('stylesheet', 'tienda_admin.css', 'media/com_tienda/css/'); ?>

<?php $link = '<a href="'.JRoute::_('index.php?option=com_admin&task=sysinfo').'">'.JText::_('COM_TIENDA_SYSTEM_INFORMATION').'</a>'; ?>

<div class="note">
	<?php echo sprintf( JText::_('COM_TIENDA_SUBMIT_BUG_TIP'), $link); ?>
</div>

<form action="<?php echo JRoute::_( 'index.php?option=com_tienda&task=doTask&element=bug_report&elementTask=sendBug' ) ?>" method="post" class="adminform" name="adminForm" >

    <fieldset>
        <legend><?php echo JText::_('COM_TIENDA_SUBMIT_BUG'); ?></legend>
            <table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <label for="title">
                        <?php echo JText::_('COM_TIENDA_BUG_TITLE'); ?>:
                        </label>
                    </td>
                    <td>
                        <input type="text" name="title" id="title" size="48" maxlength="250" value="" />
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <label for="body">
                        <?php echo JText::_('COM_TIENDA_BUG_DESCRIPTION'); ?>:
                        </label>
                    </td>
                    <td>
                        <textarea name="body" id="body" rows="5" cols="25"></textarea>
                    </td>
                </tr>
            </table>
            <input class="button" type="submit" value="<?php echo JText::_('COM_TIENDA_SEND'); ?>">
            <input type="button" class="button" onclick="window.location = '<?php echo JRoute::_('index.php?option=com_tienda&view=dashboard'); ?>'" value="<?php echo JText::_('COM_TIENDA_CANCEL'); ?>" />
    </fieldset>
</form>