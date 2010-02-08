<?php defined('_JEXEC') or die('Restricted access'); ?>

                    <table class="adminlist" style="clear: both; width: 100%;">
                    <thead>
                        <tr>
                            <th colspan="2" style="text-align: left;"><?php echo JText::_( 'Billing Address' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo JText::_( 'Select from saved addresses:' ); ?></td>
                        </tr>               
                        <tr>
                            <td>
                                <?php 
                                $billattribs = 
                                    array(
                                        'class' => 'inputbox',
                                        'size' => '1',
                                        'onchange' => 'tiendaSetAddressToDiv(\'billing\'); tiendaGetOrderTotals();'
                                );
                
                                  echo TiendaSelect::address($row->user_id, '', 'billing_address_id', 1, $billattribs, 'billing_address_id', true );
                                ?>
                            </td>
                        </tr>
                        <tr>
                           <td colspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                            <td>
                                <div id="billing_save_to_address_book_div">
                                    <input id="billing_save_to_address_book" name="billing_save_to_address_book" 
                                    onclick="tiendaShowAddressNameForSaveToAddressBook(this, 'billing_address_name_row', 'billing_input_address_name')" type="checkbox" />&nbsp;
                                    <?php echo JText::_( 'Save To Address Book' ); ?>:
                                </div>
                            </td>
                        </tr>                   
                    </tbody>
                    </table>
                    
                    <table id="billingAddressInputFormTable"
                    class="adminlist"
                    style="clear: both; width: 100%;">
                    <tr id="billing_address_name_row" style="display:none">
                        <th width="100" align="right" class="key">
                           <?php echo JText::_( 'Address Name' ); ?>:
                        </th>
                        <td>
                           <input type="text" name="billing_input_address_name" id="billing_input_address_name"
                            size="48" maxlength="250"
                            value="" />
                        </td>
                    </tr>               
                    <tr>
                        <th width="100" align="right" class="key">
                           <?php echo JText::_( 'Title' ); ?>:
                        </th>
                        <td>
                           <input type="text" name="billing_input_title" id="billing_input_title"
                            size="48" maxlength="250"
                            value="<?php echo @$row->userinfo->title; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                           <?php echo JText::_( 'First name' ); ?>:
                        </th>
                        <td>
                           <input type="text" name="billing_input_first_name"
                            id="billing_input_first_name" size="48" maxlength="250"
                            value="<?php echo @$row->userinfo->first_name; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                           <?php echo JText::_( 'Middle name' ); ?>:
                        </th>
                        <td>
                           <input type="text" name="billing_input_middle_name"
                            id="billing_input_middle_name" size="48" maxlength="250"
                            value="<?php echo @$row->userinfo->middle_name; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                           <?php echo JText::_( 'Last name' ); ?>:
                        </th>
                        <td>
                           <input type="text" name="billing_input_last_name"
                            id="billing_input_last_name" size="48" maxlength="250"
                            value="<?php echo @$row->userinfo->last_name; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key"> 
                        <?php echo JText::_( 'Company' ); ?>:
                        </th>
                        <td><input type="text" name="billing_input_company" id="billing_input_company"
                            size="48" maxlength="250"
                            value="<?php echo @$row->userinfo->company; ?>" /></td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                            <?php echo JText::_( 'Address1' ); ?>:
                        </th>
                        <td>
                            <input type="text" name="billing_input_address_1"
                            id="billing_input_address_1" size="48" maxlength="250" value="" />
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                            <?php echo JText::_( 'Address2' ); ?>:
                        </th>
                        <td>
                            <input type="text" name="billing_input_address_2"
                            id="billing_input_address_2" size="48" maxlength="250" value="" />
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                            <?php echo JText::_( 'City' ); ?>:
                        </th>
                        <td>
                            <input type="text" name="billing_input_city" id="billing_input_city"
                            size="48" maxlength="250" value="" />
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                            <?php echo JText::_( 'Country' ); ?>:
                        </th>
                        <td><?php 
                        $url = "index.php?option=com_tienda&format=raw&controller=zones&task=filterzones&hookgeozone=false&idprefix=billing_input_&countryid=";
                        $attribs = array(
                                                                'class' => 'inputbox',
                                                                'size' => '1',
                                                                'onchange' => 'tiendaDoTask( \''.$url.'\'+document.getElementById(\'billing_input_country_id\').value, \'billing_zones_wrapper\', \'\');' );
                            
                        echo TiendaSelect::country(0, 'billing_input_country_id', $attribs, 'billing_input_country_id', true ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                            <?php echo JText::_( 'Zone' ); ?>:
                        </th>
                        <td>
                        <div id="billing_zones_wrapper"></div>
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                            <?php echo JText::_( 'Postal code' ); ?>:
                        </th>
                        <td>
                            <input type="text" name="billing_input_postal_code"
                            id="billing_input_postal_code" size="48" maxlength="250" value="" />
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                            <?php echo JText::_( 'Phone' ); ?>:
                        </th>
                        <td>
                            <input type="text" name="billing_input_phone_1" id="billing_input_phone_1"
                            size="48" maxlength="250" value="<?php echo @$row->userinfo->phone_1; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                            <?php echo JText::_( 'Cell' ); ?>:
                        </th>
                        <td>
                            <input type="text" name="billing_input_phone_2" id="billing_input_phone_2"
                            size="48" maxlength="250"
                            value="<?php echo @$row->userinfo->phone_2; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th width="100" align="right" class="key">
                            <?php echo JText::_( 'Fax' ); ?>:
                        </th>
                        <td>
                            <input type="text" name="billing_input_fax" id="billing_input_fax" size="48"
                            maxlength="250" value="<?php echo @$row->userinfo->fax; ?>" />
                        </td>
                    </tr>
                    </table>