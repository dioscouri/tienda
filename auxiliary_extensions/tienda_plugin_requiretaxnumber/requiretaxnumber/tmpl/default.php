<?php
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

$state = @$vars->state;
$prefix = @$vars->prefix;
?>
    <tr>
        <th style="width: 100px; text-align: right;" class="key"> 
          <?php echo JText::_( 'Tax Number' ); ?>
        </th>
        <td>
            <input type="text" name="<?php echo $prefix; ?>tax_number" id="<?php $prefix; ?>tax_number"
            size="48" maxlength="250" value="<?php echo @$state->tax_number ?>" />
        </td>
    </tr>
    <tr>
        <th style="width: 100px; text-align: right;" class="key"> 
          <?php echo JText::_( 'Personal Identification Number' ); ?>
        </th>
        <td>
            <input type="text" name="<?php echo $prefix; ?>personal_id_number" id="<?php echo $prefix; ?>personal_id_number"
            size="48" maxlength="250" value="<?php echo @$state->personal_id_number ?>" />
        </td>
    </tr>