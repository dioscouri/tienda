<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
$form = @$this->form;
$row = @$this->row;
$helper_product = new TiendaHelperProduct();
?>

<div style="float: left; width: 50%;">
    <div class="well options">
        <legend>
            <?php echo JText::_('COM_TIENDA_TEMPLATE'); ?>
        </legend>
        <table class="table table-striped table-bordered" style="width: 100%;">
            <tr>
                <td style="vertical-align: top; width: 100px; text-align: right;" class="dsc-key"><?php echo JText::_('COM_TIENDA_PRODUCT_LAYOUT_FILE'); ?>:</td>
                <td><?php echo TiendaSelect::productlayout( @$row->product_layout, 'product_layout' ); ?>
                    <div class="note well">
                        <?php echo JText::_('COM_TIENDA_PRODUCT_LAYOUT_FILE_DESC'); ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="well options">
        <legend>
            <?php echo JText::_('COM_TIENDA_EXTRA'); ?>
        </legend>
        <table class="table table-striped table-bordered" style="width: 100%;">
            <tr>
                <td style="vertical-align: top; width: 100px; text-align: right;" class="dsc-key"><?php echo JText::_('COM_TIENDA_FEATURE_COMPARISON'); ?>:</td>
                <td><?php  echo TiendaSelect::btbooleanlist( 'param_show_product_compare', 'class="inputbox"', @$row->product_parameters->get('show_product_compare', '1') ); ?>
                </td>
            </tr>
        </table>
    </div>
</div>

<div style="float: right; width: 50%;">
    <div class="well options">
        <legend>
            <?php echo JText::_('COM_TIENDA_POST_PURCHASE_ARTICLE'); ?>
        </legend>
        <table class="table table-striped table-bordered" style="width: 100%;">
            <tr>
                <td style="vertical-align: top; width: 100px; text-align: right;" class="dsc-key"><?php echo JText::_('COM_TIENDA_SELECT_AN_ARTICLE_TO_DISPLAY_AFTER_PURCHASE'); ?>:</td>
                <td><?php echo $this->elementArticleModel->_fetchElement( 'product_article', @$row->product_article ); ?> <?php echo $this->elementArticleModel->_clearElement( 'product_article', 0 ); ?>
                </td>
            </tr>
        </table>
    </div>
</div>

<?php
// fire plugin event here to enable extending the form
JDispatcher::getInstance()->trigger('onDisplayProductFormDisplay', array( $row ) );
?>

<div style="clear: both;"></div>
