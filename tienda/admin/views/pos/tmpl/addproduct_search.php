<?php defined('_JEXEC') or die('Restricted access'); ?>

<div class="table">
    <div class="row">
        <div class="cell step_body">
            <h2><?php echo JText::_('COM_TIENDA_SEARCH_FOR_PRODUCT'); ?></h2>
            
            <input type="text" name="filter" value="<?php echo @$this->state->filter; ?>" size="40" />
            <input type="submit" class="button" name="submit_search" value="<?php echo JText::_('COM_TIENDA_CONTINUE'); ?>" />
            
        </div>
    </div>
</div>