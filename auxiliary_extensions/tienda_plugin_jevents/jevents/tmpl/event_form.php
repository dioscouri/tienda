<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $message = @$vars->message; ?>
<?php $cat = @$vars->cat; ?>
<fieldset>
            <legend>  <?php echo JText::_( "JEvent  Settings" ); ?> </legend>
            <table class="admintable" style="width: 100%;">
                <tr style="width: 100px; text-align: right;" class="key">
                            <th style="width: 25%;">
                               <?php echo JText::_( 'Select Event' ) ?> 
                            </th>
                            <td style="width: 280px;">
                              <?php echo @$vars->elementEvent_terms ?>
                               <?php echo @$vars->resetEvent_terms ?>               
                           </td>
                </tr>
                
            </table>
            </fieldset>




        