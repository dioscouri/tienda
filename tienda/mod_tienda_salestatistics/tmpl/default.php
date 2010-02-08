<?php
/**
 * @version    1.5
 * @package    Tienda
 * @author     Dioscouri Design
 * @link     http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
?>

<?php $options = array('num_decimals'=>'0'); ?>
    
<table class="adminlist" style="margin-bottom: 5px;">
<thead>
<tr>
    <th colspan="3"><?php echo JText::_( "Summary Statistics" ); ?></th>
</tr>
</thead>
<tbody>
<tr>
    <th><a href="<?php echo $stats->link; ?>"><?php echo JText::_( "Today" ); ?></a></th>
    <td style="text-align: right;"><?php echo TiendaHelperBase::number( $stats->today->num, $options ); ?></td>
    <td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->today->amount, $options ); ?></td>
</tr>
<tr>
    <th><a href="<?php echo $stats->link; ?>"><?php echo JText::_( "Yesterday" ); ?></a></th>
    <td style="text-align: right;"><?php echo TiendaHelperBase::number( $stats->yesterday->num, $options ); ?></td>
    <td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->yesterday->amount, $options ); ?></td>
</tr>
<tr>
    <th><a href="<?php echo $stats->link; ?>"><?php echo JText::_( "Last Seven Days" ); ?></a></th>
    <td style="text-align: right;"><?php echo TiendaHelperBase::number( $stats->lastseven->num, $options ); ?></td>
    <td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->lastseven->amount, $options ); ?></td>
</tr>
<tr>
    <th><a href="<?php echo $stats->link; ?>"><?php echo JText::_( "Last Month" ); ?></a></th>
    <td style="text-align: right;"><?php echo TiendaHelperBase::number( $stats->lastmonth->num, $options ); ?></td>
    <td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->lastmonth->amount, $options ); ?></td>
</tr>
<tr>
    <th><a href="<?php echo $stats->link; ?>"><?php echo JText::_( "This Month" ); ?></a></th>
    <td style="text-align: right;"><?php echo TiendaHelperBase::number( $stats->thismonth->num, $options ); ?></td>
    <td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->thismonth->amount, $options ); ?></td>
</tr>
<tr>
    <th><a href="<?php echo $stats->link; ?>"><?php echo JText::_( "Last Year" ); ?></a></th>
    <td style="text-align: right;"><?php echo TiendaHelperBase::number( $stats->lastyear->num, $options ); ?></td>
    <td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->lastyear->amount, $options ); ?></td>
</tr>
<tr>
    <th><a href="<?php echo $stats->link; ?>"><?php echo JText::_( "This Year" ); ?></a></th>
    <td style="text-align: right;"><?php echo TiendaHelperBase::number( $stats->thisyear->num, $options ); ?></td>
    <td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->thisyear->amount, $options ); ?></td>
</tr>
<tr>
    <th><a href="<?php echo $stats->link; ?>"><?php echo JText::_( "Lifetime Sales" ); ?></a></th>
    <td style="text-align: right;">
        <?php echo TiendaHelperBase::number( $stats->lifetime->num, $options )." ".JText::_("Total"); ?>
    </td>
    <td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->lifetime->amount, $options ); ?></td>
</tr>
<tr>
    <th><a href="<?php echo $stats->link; ?>"><?php echo JText::_( "Average Sale" ); ?></a></th>
    <td style="text-align: right;"><?php echo TiendaHelperBase::number( $stats->lifetime->average_daily, $options )." ".JText::_("per day"); ?></td>
    <td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->lifetime->average, $options ); ?></td>
</tr>
</tbody>
</table>