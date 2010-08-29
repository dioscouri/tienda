<?php defined('_JEXEC') or die('Restricted access'); ?>
<div class="note">
<?php
$checkout_link="index.php?option=com_tienda&view=checkout";
echo $vars->message; ?>
<a href="<?php echo JRoute::_($checkout_link); ?>">
        <?php echo JText::_( "TIENDA_SIPS_RESPONSE_TRY_AGAIN" ); ?>
	</a>
</div>
