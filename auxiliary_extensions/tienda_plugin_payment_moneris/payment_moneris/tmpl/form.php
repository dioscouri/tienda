<?php defined('_JEXEC') or die('Restricted access'); ?>

<p><?php echo JText::_( "Tienda Moneris Standard Form Message" ); ?></p>
<?php defined('_JEXEC') or die('Restricted access'); ?>

<table class="userlist">
    <tbody>
    <tr>
        <td>
            <table>
                <tr>
                    <td>
                        <?php echo JText::_( "First Name" ); ?>
                    </td>
                    <td>
                        <input name='first_name' value='<?php echo @$vars->first_name; ?>' type='text' size='35' />
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo JText::_( "Last Name" ); ?>
                    </td>
                    <td>
                        <input name='last_name' value='<?php echo @$vars->last_name; ?>' type='text' size='35' />
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo JText::_( "Address 1" ); ?>
                    </td>
                    <td>
                        <input name='address_line_1' value='<?php echo @$vars->address_line_1; ?>' type='text' size='35' />
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo JText::_( "Address 2" ); ?>
                    </td>
                    <td>
                        <input name='address_line_2' value='<?php echo @$vars->address_line_2; ?>' type='text' size='35' />
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo JText::_( "City" ); ?>
                    </td>
                    <td>
                        <input name='city' value='<?php echo @$vars->city; ?>' type='text' size='35' />
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo JText::_( "State" ); ?>
                    </td>
                    <td>
                        <input name='state' value='<?php echo @$vars->state; ?>' type='text' size='10' />
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo JText::_( "Postal Code" ); ?>
                    </td>
                    <td>
                        <input name='postal_code' value='<?php echo @$vars->postal_code; ?>' type='text' size='10' />
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo JText::_( "Country" ); ?>
                    </td>
                    <td>
                        <input name='country' value='<?php echo @$vars->country; ?>' type='text' size='35' />
                    </td>
                </tr>
                <?php // if ( ! $vars->user->get('id')): ?>				
					<tr>
						<td><?php echo JText::_( 'Email Address' ) ?></td>
						<td><input type="text" name="email" size="35" value="<?php echo @$vars->email; ?>" /></td>
					</tr>					
				<?php //endif; ?>	
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
