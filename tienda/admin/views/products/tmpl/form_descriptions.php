<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
$form = @$this->form;
$row = @$this->row;
$helper_product = new TiendaHelperProduct();
?>

<div class="well options">
    <legend>
        <?php echo JText::_('COM_TIENDA_DESCRIPTION'); ?>
    </legend>

    <table class="table table-striped table-bordered" style="width: 100%;">
        <tr>
            <td style="width: 100px; text-align: right; vertical-align: top;" class="dsc-key"><?php echo JText::_('COM_TIENDA_FULL_DESCRIPTION'); ?>:</td>
            <td><?php $editor = JFactory::getEditor(); ?> <?php echo $editor->display( 'product_description',  @$row->product_description, '100%', '300', '75', '20' ) ; ?>
            </td>
        </tr>
        <tr>
            <td style="width: 100px; text-align: right; vertical-align: top;" class="dsc-key"><?php echo JText::_('COM_TIENDA_SHORT_DESCRIPTION'); ?>:</td>
            <td><?php $editor = JFactory::getEditor(); ?> <?php echo $editor->display( 'product_description_short',  @$row->product_description_short, '100%', '300', '75', '10' ) ; ?>
            </td>
        </tr>
        <?php 
        if (!empty($row->product_id))
        {
            $tagsHelper = new TiendaHelperTags();
            if ($tagsHelper->isInstalled())
            {
                ?>
        <tr>
            <td colspan="2"><?php echo $tagsHelper->getForm( $row->product_id ); ?>
            </td>
        </tr>
        <?php 
            }
        }
        ?>
    </table>
</div>
