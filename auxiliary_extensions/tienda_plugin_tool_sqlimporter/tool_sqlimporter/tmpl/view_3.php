<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $items = @$vars->results; ?>

<p><?php echo JText::_( "THIS TOOL IMPORTS DATA FROM A SQL FILE TO TIENDA" ); ?></p>

    <div class="note">
        <span style="float: right; font-size: large; font-weight: bold;"><?php echo JText::_( "FINAL" ); ?></span>
        <p><?php echo JText::_( "MIGRATION RESULTS"); ?></p>
    </div>

    <table class="adminlist" style="clear: both;">
        <thead>
            <tr>
                <th style="width: 5px;">
                    <?php echo JText::_("NUM"); ?>
                </th>
                <th style="text-align: left;">
                    <?php echo JText::_("TITLE"); ?>
                </th>
                <th style="width: 50px;">
                    <?php echo JText::_("AFFECTED ROWS"); ?>
                </th>
                <th>
                    <?php echo JText::_("ERRORS"); ?>
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
        <?php $c=count($items); $k=0; ?>
        <?php for($i = 0; $i < $c; $i++) : ?>
            <tr class='row<?php echo $k; ?>'>
                <td align="center">
                    <?php echo $i + 1; ?>
                </td>
                <td style="text-align: left;">
                        <?php echo JText::_($items[$i]['title']); ?>
                </td>
                <td style="text-align: center;">
                    <?php echo $items[$i]['num']; ?>
                </td>
                <td style="text-align: center;">
                    <?php echo @$items[$i]['error'] ? $items[$i]['error'] : "-"; ?>
                </td>
            </tr>
            <?php $k = (1 - $k); ?>
            <?php endfor; ?>

            <?php if (!count(@$items)) : ?>
            <tr>
                <td colspan="10" align="center">
                    <?php echo JText::_('NO ITEMS FOUND'); ?>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>


