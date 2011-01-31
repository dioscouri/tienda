<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
Tienda::load( 'TiendaGrid', 'library.grid' );
$items = @$this->cartobj->items;
$subtotal = @$this->cartobj->subtotal;
$state = @$this->state;
Tienda::load( 'TiendaHelperBase', 'helpers._base' );
?>

<div class='componentheading'>
    <span><?php echo JText::_( "Shopping Cart Summary" ); ?></span>
</div>
    
<div class="cartitems">
    <form action="<?php echo JRoute::_('index.php?option=com_tienda&view=carts&task=update'); ?>" method="post" name="adminForm" enctype="multipart/form-data">

        <div style="float: right;">
            <input onclick="window.parent.document.getElementById('sbox-window').close(); window.parent.location = '<?php echo JRoute::_('index.php?option=com_tienda&view=checkout'); ?>';" value="<?php echo JText::_('Begin Checkout'); ?>" name="begincheckout" type="submit" class="button" />
        </div>
        <div style="float: left;">
            <input onclick="window.parent.document.getElementById('sbox-window').close(); window.parent.location = '<?php echo JRoute::_('index.php?option=com_tienda&view=carts'); ?>';" value="<?php echo JText::_('View Shopping Cart'); ?>" name="begincheckout" type="submit" class="button" />
        </div>
                                
        <table class="adminlist" style="clear: both;">
            <thead>
                <tr>
                    <th style="text-align: left;"><?php echo JText::_( "Product" ); ?></th>
                    <th style="width: 50px;"><?php echo JText::_( "Quantity" ); ?></th>
                    <th style="width: 50px;"><?php echo JText::_( "Total" ); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php $i=0; $k=0; $subtotal = 0; ?> 
            <?php foreach ($items as $item) : ?>
                <tr class="row<?php echo $k; ?>">
                    <td>
                        <?php echo $item->product_name; ?>
                        <br/>
                        
                        <?php if (!empty($item->attributes_names)) : ?>
                            <?php echo $item->attributes_names; ?>
                            <br/>
                        <?php endif; ?>
                        
                        <?php echo JText::_( "Price" ); ?>: <?php echo TiendaHelperBase::currency($item->product_price); ?> 
                    </td>
                    <td style="width: 50px; text-align: center;">
                        <?php echo $item->product_qty; ?>
                    </td>
                    <td style="text-align: right;">                      
                        <?php $subtotal = $subtotal + $item->subtotal; ?>
                        <?php echo TiendaHelperBase::currency($item->subtotal); ?>
                    </td>
                </tr>
            <?php ++$i; $k = (1 - $k); ?>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="font-weight: bold;">
                        <?php echo JText::_( "Subtotal" ); ?>
                    </td>
                    <td style="text-align: right;">
                        <?php echo TiendaHelperBase::currency($subtotal); ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="20">
                        <div style="float: right;">
                        <input onclick="window.parent.document.getElementById('sbox-window').close(); window.parent.location = '<?php echo JRoute::_('index.php?option=com_tienda&view=checkout'); ?>';" value="<?php echo JText::_('Begin Checkout'); ?>" name="begincheckout" type="submit" class="button" />
                        </div>
                        
                        <div style="float: left;">
                        <input onclick="window.parent.document.getElementById('sbox-window').close(); window.parent.location = '<?php echo JRoute::_('index.php?option=com_tienda&view=carts'); ?>';" value="<?php echo JText::_('View Shopping Cart'); ?>" name="begincheckout" type="submit" class="button" />
                        </div>                        
                    </td>
                </tr>
            </tfoot>
        </table>
    </form>

</div>