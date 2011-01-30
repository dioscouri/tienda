<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; ?>
<?php $order = @$this->order; ?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >

    <h3><?php echo JText::_('Edit Addresses for Order') . " " . $row->order_id; ?></h3>
    
    <fieldset style="width: 48%; float: left;">
        <legend><?php echo JText::_( "Billing Address" ); ?></legend>
        <table class="admintable">
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'First Name' ); ?>:
                </td>
                <td>
                    <input type="text" name="billing_first_name" value="<?php echo @$row->billing_first_name; ?>" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Last Name' ); ?>:
                </td>
                <td>
                    <input type="text" name="billing_last_name" value="<?php echo @$row->billing_last_name; ?>" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Company' ); ?>:
                </td>
                <td>
                    <input type="text" name="billing_company" value="<?php echo @$row->billing_company; ?>" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Line 1' ); ?>:
                </td>
                <td>
                    <input type="text" name="billing_address_1" value="<?php echo @$row->billing_address_1; ?>" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Line 1' ); ?>:
                </td>
                <td>
                    <input type="text" name="billing_address_2" value="<?php echo @$row->billing_address_2; ?>" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'City' ); ?>:
                </td>
                <td>
                    <input type="text" name="billing_city" value="<?php echo @$row->billing_city; ?>" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Postal Code' ); ?>:
                </td>
                <td>
                    <input type="text" name="billing_postal_code" value="<?php echo @$row->billing_postal_code; ?>" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Country' ); ?>:
                </td>
                <td>
                    <?php
                    $url = "index.php?option=com_tienda&format=raw&controller=addresses&task=getzones&name=shop_zone&country_id=";
                    $attribs = array('onchange' => 'tiendaDoTask( \''.$url.'\'+document.getElementById(\'billing_country_id\').value, \'billing_zones_wrapper\', \'\');' );
                    echo TiendaSelect::country( @$row->orderinfo->billing_country_id, 'billing_country_id', $attribs, 'billing_country_id', true );
                    ?>
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Zone' ); ?>:
                </td>
                <td>
                    <div id="billing_zones_wrapper">
                        <?php 
                        if (empty($row->orderinfo->billing_zone_id)) 
                        {
                            echo JText::_( "Select Country First" ); 
                        }
                        else
                        {
                            echo TiendaSelect::zone( $row->orderinfo->billing_zone_id, 'billing_zone_id', @$row->orderinfo->billing_country_id );
                        }
                        ?>
                    </div>
                </td>
            </tr>
        </table>
    </fieldset>
    
    <fieldset style="width: 48%; float: left;">
        <legend><?php echo JText::_( "Shipping Address" ); ?></legend>
        <table class="admintable">
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'First Name' ); ?>:
                </td>
                <td>
                    <input type="text" name="shipping_first_name" value="<?php echo @$row->shipping_first_name; ?>" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Last Name' ); ?>:
                </td>
                <td>
                    <input type="text" name="shipping_last_name" value="<?php echo @$row->shipping_last_name; ?>" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Company' ); ?>:
                </td>
                <td>
                    <input type="text" name="shipping_company" value="<?php echo @$row->shipping_company; ?>" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Line 1' ); ?>:
                </td>
                <td>
                    <input type="text" name="shipping_address_1" value="<?php echo @$row->shipping_address_1; ?>" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Line 1' ); ?>:
                </td>
                <td>
                    <input type="text" name="shipping_address_2" value="<?php echo @$row->shipping_address_2; ?>" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'City' ); ?>:
                </td>
                <td>
                    <input type="text" name="shipping_city" value="<?php echo @$row->shipping_city; ?>" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Postal Code' ); ?>:
                </td>
                <td>
                    <input type="text" name="shipping_postal_code" value="<?php echo @$row->shipping_postal_code; ?>" size="48" maxlength="250" />
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Country' ); ?>:
                </td>
                <td>
                    <?php
                    $url = "index.php?option=com_tienda&format=raw&controller=addresses&task=getzones&name=shop_zone&country_id=";
                    $attribs = array('onchange' => 'tiendaDoTask( \''.$url.'\'+document.getElementById(\'shipping_country_id\').value, \'shipping_zones_wrapper\', \'\');' );
                    echo TiendaSelect::country( @$row->orderinfo->shipping_country_id, 'shipping_country_id', $attribs, 'shipping_country_id', true );
                    ?>
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_( 'Zone' ); ?>:
                </td>
                <td>
                    <div id="shipping_zones_wrapper">
                        <?php 
                        if (empty($row->orderinfo->shipping_zone_id)) 
                        {
                            echo JText::_( "Select Country First" ); 
                        }
                        else
                        {
                            echo TiendaSelect::zone( $row->orderinfo->shipping_zone_id, 'shipping_zone_id', @$row->orderinfo->shipping_country_id );
                        }
                        ?>
                    </div>
                </td>
            </tr>
        </table>
    </fieldset>

    <input type="hidden" name="id" value="<?php echo @$row->order_id; ?>" />
    <input type="hidden" name="task" id="task" value="saveAddresses" />
</form>
