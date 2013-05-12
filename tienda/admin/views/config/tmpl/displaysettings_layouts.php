<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $row = @$this -> row; ?>

<table class="table table-striped table-bordered">
    <tbody>
        <tr>
            <th class="dsc-key">
                <?php echo JText::_('COM_TIENDA_DEFAULT_CATEGORY_LAYOUT'); ?>
            </th>
            <td>
                <?php echo TiendaSelect::categorylayout( $this->row->get('default_category_layout'), 'default_category_layout' ); ?>
            </td>
            <td>
                
            </td>
        </tr>
        <tr>
            <th class="dsc-key">
                <?php echo JText::_('COM_TIENDA_DEFAULT_PRODUCT_DETAIL_LAYOUT'); ?>
            </th>
            <td>
                <?php echo TiendaSelect::productlayout( $this->row->get('default_product_layout'), 'default_product_layout' ); ?>
            </td>
            <td>
                
            </td>
        </tr>
    </tbody>
</table>
