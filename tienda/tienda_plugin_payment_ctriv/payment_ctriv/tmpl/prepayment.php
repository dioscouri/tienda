<?php 
defined('_JEXEC') or die('Restricted access'); 
?>


<?php 
if($vars->error)
{
	echo '<div class="note">'.JText::_('CTRIV_ERROR').'<br />'.$vars->message.'</div>';
}
else
{
?>
<form action="<?php echo @$vars->redirect; ?>" method="post" name="adminForm" enctype="application/www-form-urlencoded">

    <div class="note">
        <?php echo JText::_( "Consorzio Triveneto Payment" ); ?>
    
        <p>
            <strong><?php echo JText::_( "Consorzio Triveneto Payment");?>:</strong> 
        </p>
    </div>
    
    <input type="submit" class="button" value="<?php echo JText::_('Click Here to Pay using Consorzio Triveneto'); ?>" />
    
   
</form>
<?php 
}
?>