<?php defined('_JEXEC') or die('Restricted access'); ?>

                    <table class="table table-striped table-bordered" style="clear: both; width: 100%;">
                    <thead>
                        <tr>
                            <th colspan="2" style="text-align: left;"><?php echo JText::_('COM_TIENDA_SHIPPING_ADDRESS'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo JText::_('COM_TIENDA_SELECT_FROM_SAVED_ADDRESSES').":"; ?></td>
                        </tr>
                        <tr>
                            <td><?php 
                            $shipattribs = array('class' => 'inputbox', 'size' => '1',
                                                    'onchange' => 'tiendaSetAddressToDiv(\'shipping\');tiendaGetOrderTotals();');
                                
                            echo TiendaSelect::address($row->user_id, '', 'shipping_address_id', 2, $shipattribs, 'shipping_address_id', true ); ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div>
                                    <input id="sameasbilling" name="sameasbilling" type="checkbox" onclick="tiendaDisableShippingAddressControls(this);" />&nbsp;
                                    <?php echo JText::_('COM_TIENDA_SAME_AS_BILLING_ADDRESS'); ?>:
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div id="shipping_save_to_address_book_div">
                                    <input id="shipping_save_to_address_book" name="shipping_save_to_address_book" 
                                    onclick="tiendaShowAddressNameForSaveToAddressBook(this, 'shipping_address_name_row', 'shipping_address_name')" type="checkbox" />&nbsp;
                                    <?php echo JText::_('COM_TIENDA_SAVE_TO_ADDRESS_BOOK'); ?>:
                                </div>
                            </td>
                        </tr>                   
                    </tbody>
                    </table>
                    
                    <table id="shippingAddressInputFormTable"
                    class="table table-striped table-bordered"
                    style="clear: both; width: 100%;">
                    <tr id="shipping_address_name_row" style="display:none">
                        <th width="100" align="right" class="key">
                           <?php echo JText::_('COM_TIENDA_ADDRESS_TITLE'); ?>:
                        </th>
                        <td>
                           <input type="text" name="shipping_input_address_name" id="shipping_input_address_name"
                            size="48" maxlength="250"
                            value="" />
                        </td>
                    </tr>               
                    <tr>
                        <th width="100" align="right" class="key">
                            <?php echo JText::_('COM_TIENDA_TITLE'); ?>:
                        </th>
                        <td>
                            <input type="text" name="shipping_input_title" id="shipping_input_title"
                            size="48" maxlength="250" value="<?php echo @$row->userinfo->title; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                            <?php echo JText::_('COM_TIENDA_FIRST_NAME'); ?>:
                        </th>
                        <td>
                            <input type="text" name="shipping_input_first_name"
                            id="shipping_input_first_name" size="48" maxlength="250"
                            value="<?php echo @$row->userinfo->first_name; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                            <?php echo JText::_('COM_TIENDA_MIDDLE_NAME'); ?>:
                        </th>
                        <td>
                            <input type="text" name="shipping_input_middle_name"
                            id="shipping_input_middle_name" size="48" maxlength="250"
                            value="<?php echo @$row->userinfo->middle_name; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                            <?php echo JText::_('COM_TIENDA_LAST_NAME'); ?>:
                        </th>
                        <td>
                            <input type="text" name="shipping_input_last_name"
                            id="shipping_input_last_name" size="48" maxlength="250"
                            value="<?php echo @$row->userinfo->last_name; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                            <?php echo JText::_('COM_TIENDA_COMPANY'); ?>:
                        </th>
                        <td>
                            <input type="text" name="shipping_input_company" id="shipping_input_company"
                            size="48" maxlength="250"
                            value="<?php echo @$row->userinfo->company; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                            <?php echo JText::_('COM_TIENDA_ADDRESS_LINE_1'); ?>:
                        </th>
                        <td>
                            <input type="text" name="shipping_input_address_1"
                            id="shipping_input_address_1" size="48" maxlength="250" value="" />
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                            <?php echo JText::_('COM_TIENDA_ADDRESS_LINE_2'); ?>:
                        </th>
                        <td>
                            <input type="text" name="shipping_input_address_2"
                            id="shipping_input_address_2" size="48" maxlength="250" value="" />
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                            <?php echo JText::_('COM_TIENDA_CITY'); ?>:
                        </th>
                        <td>
                            <input type="text" name="shipping_input_city" id="shipping_input_city"
                            size="48" maxlength="250" value="" />
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key"> 
                            <?php echo JText::_('COM_TIENDA_COUNTRY'); ?>:
                        </th>
                        <td>
                            <?php 
                                $url = "index.php?option=com_tienda&format=raw&controller=zones&task=filterzones&hookgeozone=false&idprefix=shipping_input_&countryid=";
                                $attribs = array(
                                                                'class' => 'inputbox',
                                                                'size' => '1',
                                                                'onchange' => 'tiendaDoTask( \''.$url.'\'+document.getElementById(\'shipping_input_country_id\').value, \'shipping_zones_wrapper\', \'\');' );
                            
                                echo TiendaSelect::country(0, 'shipping_input_country_id', $attribs, 'shipping_input_country_id', true ); 
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                            <?php echo JText::_('COM_TIENDA_ZONE'); ?>:
                        </th>
                        <td>
                            <div id="shipping_zones_wrapper"></div>
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                            <?php echo JText::_('COM_TIENDA_POSTAL_CODE'); ?>:
                        </th>
                        <td>
                            <input type="text" name="shipping_input_postal_code" id="shipping_input_postal_code"
                            size="48" maxlength="250" value="" />
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                            <?php echo JText::_('COM_TIENDA_PHONE'); ?>:
                        </th>
                        <td>
                            <input type="text" name="shipping_input_phone_1" id="shipping_input_phone_1"
                            size="48" maxlength="250"
                            value="<?php echo @$row->userinfo->phone_1; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                            <?php echo JText::_('COM_TIENDA_CELL'); ?>:
                        </th>
                        <td>
                            <input type="text" name="shipping_input_phone_2" id="shipping_input_phone_2"
                            size="48" maxlength="250"
                            value="<?php echo @$row->userinfo->phone_2; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                            <?php echo JText::_('COM_TIENDA_FAX'); ?>:
                        </th>
                        <td>
                            <input type="text" name="shipping_input_fax" id="shipping_input_fax"
                            size="48" maxlength="250"
                            value="<?php echo @$row->userinfo->fax; ?>" />
                        </td>
                    </tr>
                    </table>