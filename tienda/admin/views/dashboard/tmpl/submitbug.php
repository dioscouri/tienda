<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $link = '<a href="'.JRoute::_('index.php?option=com_admin&task=sysinfo').'">System Informations</a>';?>
<div class="note">
	<?php echo JText::_("Warning! Along with the bug informations that you write here will be sent also all the informations gathered from {$link}"); ?>
</div>

<form action="<?php echo JRoute::_( 'index.php?option=com_tienda&task=sendBug' ) ?>" method="post" class="adminform" name="adminForm" >

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
            <input type="submit" value="<?php echo JText::_('Send'); ?>">
    </fieldset>
</form>