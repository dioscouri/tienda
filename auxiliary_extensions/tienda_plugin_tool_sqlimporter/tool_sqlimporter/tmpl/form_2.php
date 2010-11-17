<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php echo @$vars->token; ?>

    <p><?php echo JText::_( "THIS TOOL IMPORTS DATA FROM A SQL FILE TO TIENDA" ); ?></p>

    <div class="note">
        <span style="float: right; font-size: large; font-weight: bold;"><?php echo JText::_( "STEP TWO OF THREE" ); ?></span>
        <p><?php echo JText::_( "PLEASE REVIEW THE FOLLOWING INFORMATION" ); ?></p>
    </div>
    
    <fieldset>
        <legend><?php echo JText::_('SQL INFORMATION'); ?></legend>
            <table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'File' ); ?>: *
                    </td>
                    <td>
                    	<?php echo @$state->uploaded_file; ?>
                        <input type="hidden" name="uploaded_file" id="uploaded_file" size="48" value="<?php echo @$state->uploaded_file; ?>" />
                    </td>
                </tr>               
            </table>    
    <br />
    
    </fieldset>
        