<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('stylesheet', 'tienda_admin.css', 'media/com_tienda/css/'); ?>

<?php $link = '<a href="'.JRoute::_('index.php?option=com_admin&task=sysinfo').'">'.JText::_( "System Information" ).'</a>'; ?>

<div class="note">
	<?php echo sprintf( JText::_('SUBMIT BUG TIP'), $link); ?>
</div>

<form action="<?php echo JRoute::_( 'index.php?option=com_tienda&task=doTask&element=bug_report&elementTask=sendBug' ) ?>" method="post" class="adminform" name="adminForm" >

    <fieldset>
        <legend><?php echo JText::_('Submit Bug'); ?></legend>
            <table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <label for="title">
                        <?php echo JText::_( 'Bug Title' ); ?>:
                        </label>
                    </td>
                    <td>
                        <input type="text" name="title" id="title" size="48" maxlength="250" value="" />
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <label for="body">
                        <?php echo JText::_( 'Bug Description' ); ?>:
                        </label>
                    </td>
                    <td>
                        <textarea name="body" id="body" rows="5" cols="25"></textarea>
                    </td>
                </tr>
            </table>
            <input class="button" type="submit" value="<?php echo JText::_('Send'); ?>">
            <input type="button" class="button" onclick="window.location = '<?php echo JRoute::_('index.php?option=com_tienda&view=dashboard'); ?>'" value="<?php echo JText::_('Cancel'); ?>" />
    </fieldset>
</form>