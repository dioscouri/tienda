<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" >
    <fieldset>
        <legend><?php echo JText::_('Form'); ?></legend>
    
        <?php echo sprintf( JText::_('SHIPPING_PLUGIN_FEDEX_CONFIG_HELP'), $vars->link ); ?>
        
    </fieldset>
    
    <input type="hidden" name="id" value="<?php echo @$vars->id; ?>" />
    <input type="hidden" name="task" value="" />
</form>

