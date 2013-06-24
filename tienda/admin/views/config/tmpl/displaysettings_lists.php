<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $row = @$this -> row; ?>

<table class="table table-striped table-bordered">
    <tbody>
        <tr>
            <th class="dsc-key">
                <?php echo JText::_( 'COM_TIENDA_DISABLE_CHANGING_LIST_LIMIT' ); ?>
            </th>
            <td class="dsc-value">
                <?php echo TiendaSelect::btbooleanlist( 'disable_changing_list_limit', 'class="inputbox"', $this->row->get('disable_changing_list_limit', '0') ); ?>
            </td>
            <td>
                
            </td>
        </tr>
        <tr>
            <th class="dsc-key">
                <?php echo JText::_('COM_TIENDA_DEFAULT_LIST_LIMIT'); ?>
            </th>
            <td>
                <input type="text" name="default_list_limit" value="<?php echo $this->row->get('default_list_limit', JFactory::getApplication()->getCfg('list_limit')); ?>" class="input-small" />
            </td>
            <td>
            
            </td>
        </tr>        
    </tbody>
</table>
