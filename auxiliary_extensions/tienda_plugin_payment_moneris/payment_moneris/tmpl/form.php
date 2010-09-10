<?php defined('_JEXEC') or die('Restricted access'); ?>

<p><?php echo JText::_( "Tienda Moneris Standard Form Message" ); ?></p>
<?php defined('_JEXEC') or die('Restricted access'); ?>

<table class="userlist">
    <tbody>
    <tr>
        <td>
            <table>
               <tr>
                    <td colspan='2'><hr/></td>
                </tr>               
                <tr>
                    <td>
                        <?php echo JText::_( "Card Type" ); ?>
                    </td>
                    <td>
                        <?php echo @$vars->selectCardType; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo JText::_( "Card Number" ); ?>
                    </td>
                    <td>
                        <input name='card_number' value='<?php echo @$vars->card_number; ?>' type='text' size='35' />
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo JText::_( "Expiration Month" ); ?>
                    </td>
                    <td>
                        <input name='expiration_month' value='<?php echo @$vars->expiration_month; ?>' type='text' size='10' />
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo JText::_( "Expiration Year" ); ?>
                    </td>
                    <td>
                        <input name='expiration_year' value='<?php echo @$vars->expiration_year; ?>' type='text' size='10' />
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo JText::_( "CVV Number" ); ?>
                    </td>
                    <td>
                        <input name='cvv_number' value='<?php echo @$vars->cvv_number; ?>' type='password' size='10' />
                    </td>
                </tr>
            </table>
            <!--  
            <input type='hidden' name='item_number' value='<?php //echo $vars->item->id; ?>'>
            <input type='submit' name='submit' value='<?php echo JText::_( "Submit" ); ?>'>
            -->
            <?php echo JHTML::_( 'form.token' ); ?>
           
        </td>
    </tr>
    </tbody>
</table>
