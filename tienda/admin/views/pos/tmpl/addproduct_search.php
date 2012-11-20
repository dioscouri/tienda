<?php defined('_JEXEC') or die('Restricted access'); ?>


            <h2><?php echo JText::_('COM_TIENDA_SEARCH_FOR_PRODUCT'); ?></h2>
            <table class="table table-striped table-bordered">
            	<tr><td> <input type="text" name="filter" value="<?php echo @$this->state->filter; ?>" size="240" /></td> <td> <input type="submit" class="btn btn-primary pull-left" name="submit_search" value="<?php echo JText::_('COM_TIENDA_CONTINUE'); ?>" /></td></tr>
            </table>
           
 