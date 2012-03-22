<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Valérie Isaksen
 * @link 	http://www.alatak.net
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
?>

<?php defined('_JEXEC') or die('Restricted access'); ?>
<div class="note">
<?php
$checkout_link="index.php?option=com_tienda&view=checkout";
echo $vars->message; ?>
<a href="<?php echo JRoute::_($checkout_link); ?>">
        <?php echo JText::_('TIENDA_SIPS_RESPONSE_TRY_AGAIN'); ?>
	</a>
</div>
