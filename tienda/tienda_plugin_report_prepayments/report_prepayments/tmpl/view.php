<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');?>
<?php $state = @$vars->state; ?>
<?php $items = @$vars->items; ?>
    <table class="adminlist" style="clear: both;">
        <thead>
            <tr>
                <th style="width: 5px;">
                    <?php echo JText::_("Num"); ?>
                </th>
                <th style="width: 50px;">
                    <?php echo JText::_("ID"); ?>
                </th>
                <th style=" width: 200px;">
                    <?php echo JText::_("Date of Order"); ?>
                </th>
                <th style="text-align: left;">
                    <?php echo JText::_("Customer"); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo JText::_("Total"); ?>
                </th>
                <th style="width: 150px;">
                    <?php echo JText::_("State"); ?>
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
				<td style="text-align: center;">
					<a href="<?php echo $item->link; ?>">
						<?php echo $item->order_id; ?>
					</a>
				</td>
               <td style="text-align: center;">
                    <a href="<?php echo $item->link; ?>">
                        <?php echo JHTML::_('date', $item->created_date, TiendaConfig::getInstance()->get('date_format')); ?>
                    </a>
                </td>
                <td style="text-align: left;">
					<?php echo $item->user_name .' [ '.$item->user_id.' ]'; ?>
					&nbsp;&nbsp;&bull;&nbsp;&nbsp;<?php echo $item->email .' [ '.$item->user_username.' ]'; ?>
					<br/>
					<b><?php echo JText::_( "Ship to" ); ?></b>:
					<?php 
					if (empty($item->shipping_address_1)) 
					{
					   echo JText::_( "Undefined Shipping Address" ); 
					}
					   else
					{
	                    echo $item->shipping_address_1.", ";
	                    echo $item->shipping_address_2 ? $item->shipping_address_2.", " : "";
	                    echo $item->shipping_city.", ";
	                    echo $item->shipping_zone_name." ";
	                    echo $item->shipping_postal_code." ";
	                    echo $item->shipping_country_name;
					}
					?>
                    <?php 
                    if (!empty($item->order_number))
                    {
                        echo "<br/><b>".JText::_( "Order Number" )."</b>: ".$item->order_number;
                    }
                    ?>
				</td>
                <td style="text-align: center;">
					<?php echo TiendaHelperBase::currency( $item->order_total, $item->currency ); ?>
                    <?php if (!empty($item->commissions)) { ?>
                        <br/>
                        <?php JHTML::_('behavior.tooltip'); ?>
                        <a href="index.php?option=com_amigos&view=commissions&filter_orderid=<?php echo $item->order_id; ?>" target="_blank">
                            <img src='<?php echo JURI::root(true); ?>/media/com_amigos/images/amigos_16.png' title="<?php echo JText::_( "Order Has a Commission" ); ?>::<?php echo JText::_( "View Commission Records" ); ?>" class="hasTip" />
                        </a>
                    <?php } ?>
				</td>
                <td style="text-align: center;">
					<?php echo $item->order_state_name; ?>
				</td>
            </tr>
            <?php ++$i; $k = (1 - $k); ?>
            <?php endforeach; ?>

            <?php if (!count(@$items)) : ?>
            <tr>
                <td colspan="10" align="center">
                    <?php echo JText::_('No items found'); ?>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
