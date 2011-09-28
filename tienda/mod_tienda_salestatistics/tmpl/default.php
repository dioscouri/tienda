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

<?php $options_decimal = array('num_decimals'=>'2'); ?>
<?php $options_int = array('num_decimals'=>'0'); ?>
    
<table class="adminlist" style="margin-bottom: 5px;">
<thead>
<tr>
    <th colspan="5"><?php echo JText::_( "SUMMARY STATISTICS" ); ?></th>
</tr>
</thead>
<tbody>
<tr>
    <th width="100px"><?php echo JText::_( "RANGE" ); ?></th>
    <th style="text-align: center;"><?php echo JText::_( "TOTAL_ORDERS" ); ?></th>
	<th style="text-align: right;"><?php echo JText::_( "AVERAGE_ORDERS_PER_DAY" ); ?></th>
    <th style="text-align: right;"><?php echo JText::_( "AVERAGE_REVENUE_PER_ORDER" ); ?></th>
	<th style="text-align: right;"><?php echo JText::_( "TOTAL_REVENUE" ); ?></th>
</tr>
<tr>
    <th><a href="<?php echo $stats->link; ?>"><?php echo JText::_( "TODAY" ); ?></a></th>
    <td style="text-align: right;"><?php echo TiendaHelperBase::number( $stats->today->num, $options_int ); ?></td>
	<td style="text-align: right;">&nbsp</td>
	<td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->today->average,'', $options_decimal ); ?></td>
    <td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->today->amount, '', $options_decimal ); ?></td>
</tr>
<tr>
    <th><a href="<?php echo $stats->link; ?>"><?php echo JText::_( "YESTERDAY" ); ?></a></th>
    <td style="text-align: right;"><?php echo TiendaHelperBase::number( $stats->yesterday->num, $options_int ); ?></td>
	<td style="text-align: right;">&nbsp</td>
	<td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->yesterday->average,'', $options_decimal ); ?></td>
    <td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->yesterday->amount, '', $options_decimal ); ?></td>
</tr>
<tr>
    <th><a href="<?php echo $stats->link; ?>"><?php echo JText::_( "LAST SEVEN DAYS" ); ?></a></th>
    <td style="text-align: right;"><?php echo TiendaHelperBase::number( $stats->lastseven->num, $options_int ); ?></td>
	<td style="text-align: right;">&nbsp</td>
	<td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->lastseven->average,'', $options_decimal ); ?></td>
    <td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->lastseven->amount, '', $options_decimal ); ?></td>
</tr>
<tr>
    <th><a href="<?php echo $stats->link; ?>"><?php echo JText::_( "LAST MONTH" ); ?></a></th>
    <td style="text-align: right;"><?php echo TiendaHelperBase::number( $stats->lastmonth->num, $options_int ); ?></td>
	<td style="text-align: right;">&nbsp</td>
	<td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->lastmonth->average,'', $options_decimal ); ?></td>
    <td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->lastmonth->amount, '', $options_decimal ); ?></td>
</tr>
<tr>
    <th><a href="<?php echo $stats->link; ?>"><?php echo JText::_( "THIS MONTH" ); ?></a></th>
    <td style="text-align: right;"><?php echo TiendaHelperBase::number( $stats->thismonth->num, $options_int ); ?></td>
	<td style="text-align: right;">&nbsp</td>
	<td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->thismonth->average,'', $options_decimal ); ?></td>
    <td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->thismonth->amount, '', $options_decimal ); ?></td>
</tr>
<tr>
    <th><a href="<?php echo $stats->link; ?>"><?php echo JText::_( "LAST YEAR" ); ?></a></th>
    <td style="text-align: right;"><?php echo TiendaHelperBase::number( $stats->lastyear->num, $options_int ); ?></td>
	<td style="text-align: right;">&nbsp</td>
	<td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->lastyear->average,'', $options_decimal ); ?></td>
    <td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->lastyear->amount, '', $options_decimal ); ?></td>
</tr>
<tr>
    <th><a href="<?php echo $stats->link; ?>"><?php echo JText::_( "THIS YEAR" ); ?></a></th>
    <td style="text-align: right;"><?php echo TiendaHelperBase::number( $stats->thisyear->num, $options_int ); ?></td>
	<td style="text-align: right;">&nbsp</td>
	<td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->thisyear->average,'', $options_decimal ); ?></td>
    <td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->thisyear->amount, '', $options_decimal ); ?></td>
</tr>
<tr>
    <th><a href="<?php echo $stats->link; ?>"><?php echo JText::_( "LIFETIME SALES" ); ?></a></th>
    <td style="text-align: right;"><?php echo TiendaHelperBase::number( $stats->lifetime->num, $options_int ) ?></td>
	<td style="text-align: right;"><?php echo TiendaHelperBase::number( $stats->lifetime->average_daily, $options_decimal ) ?></td>
	<td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->lifetime->average,'', $options_decimal ); ?></td>
    <td style="text-align: right;"><?php echo TiendaHelperBase::currency( $stats->lifetime->amount, '', $options_decimal ); ?></td>
</tr>

</tbody>
</table>