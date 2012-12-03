<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
$form = @$this->form;
$row = @$this->row;
$helper_product = new TiendaHelperProduct();
?>

<div style="width: 100%;">
    <div class="well options">
        <legend>
            <?php echo JText::_('COM_TIENDA_ADD_NEW_RELATIONSHIP'); ?>
        </legend>
        <div id="new_relationship" class="dsc-wrap dsc-table">
            <div class="dsc-row">
                <div class="dsc-cell">
                    <?php echo TiendaSelect::relationship('', 'new_relationship_type'); ?>
                </div>
                <div class="dsc-cell">
                    <?php 
                    DSCModel::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_tienda/models' );
                    $model = DSCModel::getInstance( 'ElementProduct', 'TiendaModel' );
                    echo $model->fetchElement( 'new_relationship_productid_to' );
                    echo $model->clearElement( 'new_relationship_productid_to' );
                    //<input name="new_relationship_productid_to" size="15" type="text" />
                    ?>

                    <input name="new_relationship_productid_from" value="<?php echo @$row->product_id; ?>" type="hidden" />
                </div>
                <div class="dsc-cell">
                    <input type="button" value="<?php echo JText::_('COM_TIENDA_ADD'); ?>" class="btn btn-success" onclick="tiendaAddRelationship('existing_relationships', '<?php echo JText::_('COM_TIENDA_UPDATING_RELATIONSHIPS'); ?>');" value="<?php echo JText::_('COM_TIENDA_ADD'); ?>" />
                </div>
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>
</div>

<div style="width: 100%;">
    <div class="well options">
        <legend>
            <?php echo JText::_('COM_TIENDA_EXISTING_RELATIONSHIPS'); ?>
        </legend>
        <div id="existing_relationships">
            <?php echo $this->product_relations; ?>
        </div>
    </div>
</div>

<?php
// fire plugin event here to enable extending the form
JDispatcher::getInstance()->trigger('onDisplayProductFormRelations', array( $row ) );
?>

<div style="clear: both;"></div>
