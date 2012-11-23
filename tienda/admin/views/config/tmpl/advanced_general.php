<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $row = @$this -> row; ?>

<table class="table table-striped table-bordered">
    <tbody>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_NO_ZONES_COUNTRIES'); ?>
            </th>
            <td style="width: 280px;"><input type="text" name="ignored_countries" value="<?php echo $this -> row -> get('ignored_countries', ''); ?>" class="inputbox" />
            </td>
            <td><?php echo JText::_('COM_TIENDA_COUNTRIES_THAT_WILL_BE_IGNORED_WHEN_VALIDATING_THE_ZONES_DURING_CHECKOUT_DESC'); ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ENABLE_AUTOMATIC_TABLE_REORDERING'); ?>
            </th>
            <td style="width: 150px;"><?php  echo TiendaSelect::btbooleanlist('enable_reorder_table', 'class="inputbox"', $this -> row -> get('enable_reorder_table', '1')); ?>
            </td>
            <td><?php echo JText::_('COM_TIENDA_ENABLE_AUTOMATIC_TABLE_REORDERING_DESC'); ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DEFAULT_USER_GROUP'); ?>
            </th>
            <td style="width: 150px;"><?php echo TiendaSelect::groups($this -> row -> get('default_user_group', '1'), 'default_user_group'); ?>
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_LOAD_CUSTOM_LANGUAGE_FILE'); ?>
            </th>
            <td style="width: 150px;"><?php  echo TiendaSelect::btbooleanlist('custom_language_file', 'class="inputbox"', $this -> row -> get('custom_language_file', '0')); ?>
            </td>
            <td><?php echo JText::_('COM_TIENDA_TIENDA_CUSTOM_LANGUAGE_FILE_DESC'); ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_USE_SHA1_TO_STORE_THE_IMAGES'); ?>
            </th>
            <td style="width: 150px;"><?php  echo TiendaSelect::btbooleanlist('sha1_images', 'class="inputbox"', $this -> row -> get('sha1_images', '0')); ?>
            </td>
            <td><?php echo JText::_('COM_TIENDA_TIENDA_SHA1_IMAGE_DESC'); ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_MAX_FILESIZE_FOR_IMAGES_IMAGE_ARCHIVES'); ?>
            </th>
            <td style="width: 150px;"><input type="text" name="files_maxsize" value="<?php echo $this -> row -> get('files_maxsize', '3000'); ?>" /> Kb</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_CHOOSE_MULTI_UPLOAD_SCRIPT'); ?>
            </th>
            <td style="width: 150px;"><?php echo TiendaSelect::multipleuploadscript($this -> row -> get('multiupload_script', '0'), 'multiupload_script'); ?>
            </td>
            <td><?php echo JText::_('COM_TIENDA_CHOOSE_MULTI_UPLOAD_SCRIPT_DESC'); ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_CONFIG_PASSWORD_LENGTH'); ?>
            </th>
            <td style="width: 150px;"><input type="text" name="password_min_length" value="<?php echo $this -> row -> get('password_min_length', '5'); ?>" />
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_CONFIG_PASSWORD_REQUIRE_DIGIT'); ?>
            </th>
            <td style="width: 150px;"><?php  echo TiendaSelect::btbooleanlist('password_req_num', 'class="inputbox"', $this -> row -> get('password_req_num', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_CONFIG_PASSWORD_REQUIRE_ALPHA'); ?>
            </th>
            <td style="width: 150px;"><?php  echo TiendaSelect::btbooleanlist('password_req_alpha', 'class="inputbox"', $this -> row -> get('password_req_alpha', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_CONFIG_PASSWORD_REQUIRE_SPECIAL'); ?>
            </th>
            <td style="width: 150px;"><?php  echo TiendaSelect::btbooleanlist('password_req_spec', 'class="inputbox"', $this -> row -> get('password_req_spec', '1')); ?>
            </td>
            <td><?php echo JText::_('COM_TIENDA_CONFIG_PASSWORD_REQUIRE_SPECIAL_DESC'); ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_CONFIG_PASSWORD_VALDATE_PHP'); ?>
            </th>
            <td style="width: 150px;"><?php  echo TiendaSelect::btbooleanlist('password_php_validate', 'class="inputbox"', $this -> row -> get('password_php_validate', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_CONFIG_LOWER_FILENAME'); ?>
            </th>
            <td style="width: 150px;"><?php  echo TiendaSelect::btbooleanlist('lower_filename', 'class="inputbox"', $this -> row -> get('lower_filename', '1')); ?>
            </td>
            <td><?php echo JText::_('COM_TIENDA_CONFIG_LOWER_FILENAME_DESC'); ?>
            </td>
        </tr>
    </tbody>
</table>
