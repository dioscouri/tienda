<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $message = @$vars->message; ?>
<?php $product = $vars->product; ?>

<fieldset>
<legend><?php echo JText::_( "Lightspeed Integration" ); ?></legend>
<table class="admintable" style="width: 100%;">
    <tr>
        <td style="vertical-align: top; width: 100px; text-align: right;" class="key">
            <?php echo JText::_( 'This Product Corresponds With' ); ?>:
        </td>
        <td>
            <?php if (empty($product->rowid)) { ?>
                <?php echo $message; ?>
            <?php } else { ?>
                <table>
                <tr>
                    <th><?php echo JText::_( "ID" ); ?></th>
                    <td><?php echo $product->rowid; ?></td>
                </tr>
                <tr>
                    <th><?php echo JText::_( "Name" ); ?></th>
                    <td><?php echo $product->name; ?></td>
                </tr>
                </table>
            <?php } ?>
        </td>
    </tr>
</table>
</fieldset>




        