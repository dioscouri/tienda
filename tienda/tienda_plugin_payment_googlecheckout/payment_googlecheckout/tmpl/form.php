<?php defined('_JEXEC') or die('Restricted access'); ?>

<table class="userlist">
	<tbody>
	<tr>
		<td class="title">		
			<form action="<?php echo $vars->action_url ?>" method="post">			
				<input type="hidden" name="type_id" value="<?php echo plg_tienda_escape($vars->type_id) ?>" />
				<input type="hidden" name="r" value="<?php echo plg_tienda_escape($vars->r) ?>" />
				<input type="image" src="<?php echo plg_tienda_escape($vars->button_url) ?>/buttons/checkout.gif?merchant_id=<?php echo plg_tienda_escape($vars->merchant_id) ?>&w=160&h=43&style=trans&variant=text&loc=en_US" border="0" name="submit" alt="Pay with Google Checkout" />
			</form>
		</td>
        <td class="input">
			<?php echo plg_tienda_escape($vars->note); ?>
		</td>
	</tr>
	</tbody>
</table>