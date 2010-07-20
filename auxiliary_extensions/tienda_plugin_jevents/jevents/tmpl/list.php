<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $state = $vars->state; ?>
<?php $items = $vars->items; ?>
<?php
JHTML::_('behavior.tooltip');
$object = JRequest::getVar( 'object' );
$link = 'index.php?option=com_tienda&task=doTask&element=jevents&elementTask=showEvents&object='.$object;
?>

<form action="<?php echo $link; ?>" method="post" name="adminForm">

    <table>
        <tr>
            <td align="left" width="100%">
            </td>
            <td nowrap="nowrap" style="text-align: right;">
                <input name="filter" value="<?php echo @$state->filter; ?>" />
                <button onclick="this.form.submit();"><?php echo JText::_('Search'); ?></button>
                <button onclick="tiendaFormReset(this.form);"><?php echo JText::_('Reset'); ?></button>
            </td>
        </tr>
    </table>

    <table class="adminlist" cellspacing="1">
    <thead>
        <tr>
            <th style="width: 50px;">
                <?php echo TiendaGrid::sort( 'ID', "tbl.ev_id", @$state->direction, @$state->order ); ?>
            </th>
            <th style="text-align: left;">
                <?php echo TiendaGrid::sort( 'Summary', "eventdetails.summary", @$state->direction, @$state->order ); ?>
            </th>
            <th style="width: 100px;">
                <?php echo TiendaGrid::sort( 'Start', "eventdetails.dtstart", @$state->direction, @$state->order ); ?>
            </th>
            <th style="width: 100px;">
                <?php echo TiendaGrid::sort( 'End', "eventdetails.dtend", @$state->direction, @$state->order ); ?>
            </th>
        </tr>
    </thead>
    <tfoot>
    <tr>
        <td colspan="15">
            <?php //echo $page->getListFooter(); ?>
        </td>
    </tr>
    </tfoot>
    <tbody>
    <?php
    $k = 0;
    foreach ($items as $item)
    {
        ?>
        <tr class="<?php echo "row$k"; ?>">
            <td>
                <?php echo $item->ev_id; ?>
            </td>
            <td style="text-align: left;">
              <a style="cursor: pointer;" onclick="window.parent.jSelectEvent('<?php echo $item->evdet_id; ?>', '<?php echo str_replace(array("'", "\""), array("\\'", ""),$item->summary); ?>', '<?php echo JRequest::getVar('object'); ?>');">
							<?php echo htmlspecialchars($item->summary, ENT_QUOTES, 'UTF-8'); ?></a>
              </td>
            <td>
                <?php  echo JHTML::date( $item->dtstart); ?>
            </td>
            <td>
                <?php echo JHTML::date( $item->dtend); ?>
            </td>
        </tr>
        <?php
        $k = 1 - $k;
    }
    ?>
    </tbody>
    </table>

<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php //echo $lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php // echo $lists['order_Dir']; ?>" />
</form>