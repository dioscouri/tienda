<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $items = @$vars->results; ?>

<p><?php echo JText::_('COM_TIENDA_THIS_TOOL_MIGRATES_DATA_FROM_XCART_TO_TIENDA'); ?></p>

    <div class="note">
        <span style="float: right; font-size: large; font-weight: bold;"><?php echo JText::_('COM_TIENDA_FINAL'); ?></span>
        <p><?php echo JText::_('COM_TIENDA_MIGRATION_RESULTS'); ?></p>
    </div>

    <table class="adminlist" style="clear: both;">
        <thead>
            <tr>
                <th style="width: 5px;">
                    <?php echo JText::_('COM_TIENDA_NUM'); ?>
                </th>
                <th style="text-align: left;">
                    <?php echo JText::_('COM_TIENDA_TITLE'); ?>
                </th>
                <th style="width: 50px;">
                    <?php echo JText::_('COM_TIENDA_AFFECTED_ROWS'); ?>
                </th>
                <th>
                    <?php echo JText::_('COM_TIENDA_ERRORS'); ?>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="20">

                </td>
            </tr>
        </tfoot>
        <tbody>
        <?php $i=0; $k=0; ?>
        <?php foreach (@$items as $item) : ?>
            <tr class='row<?php echo $k; ?>'>
                <td align="center">
                    <?php echo $i + 1; ?>
                </td>
                <td style="text-align: left;">
                        <?php echo JText::_($item->title); ?>
                </td>
                <td style="text-align: center;">
                    <?php echo $item->affectedRows; ?>
                </td>
                <td style="text-align: center;">
                    <?php echo $item->error ? $item->error : "-"; ?>
                </td>
            </tr>
            <?php ++$i; $k = (1 - $k); ?>
            <?php endforeach; ?>

            <?php if (!count(@$items)) : ?>
            <tr>
                <td colspan="10" align="center">
                    <?php echo JText::_('COM_TIENDA_NO_ITEMS_FOUND'); ?>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>


