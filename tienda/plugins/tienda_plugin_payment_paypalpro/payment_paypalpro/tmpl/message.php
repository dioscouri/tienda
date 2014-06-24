<?php defined('_JEXEC') or die('Restricted access'); ?>

<table class="userlist">
	<tbody>
		<tr>
			<td class="input">
	  			<?php 
	  				if( is_array($vars->errors ) ) {
	  					for( $i = 0, $c = count( $vars->errors  ); $i < $c; $i++ ) {
	  						echo '<p>'.plg_tienda_escape($vars->errors[$i]).'</p>';
	  					}
	  				} else {
	  					echo '<p>'.$vars->errors.'</p>';
	  				}
	  			?>
			</td>
		</tr>
	</tbody>
</table>

