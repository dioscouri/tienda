<?php defined('_JEXEC') or die('Restricted access'); ?>

<table class="userlist">
	<thead>
	   <tr>
            <th colspan="2"><?php echo plg_ambrasubs_escape($vars->title); ?></th>
        </tr>
	</thead>
	<tbody>
		<tr>
			<td class="title"><?php echo plg_ambrasubs_escape($vars->id_title); ?>:</td>
				<td>
					<center>
						<?php echo plg_ambrasubs_escape($vars->row_id); ?>
					</center>
				</td>
		</tr>
		<tr>
			<td class="title"><?php echo plg_ambrasubs_escape($vars->date_title); ?>:</td>
			<td>
				<center>
					<?php echo plg_ambrasubs_escape($vars->payment_datetime); ?>
				</center>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo plg_ambrasubs_escape($vars->transaction_id_title); ?>:</td>
			<td>
				<center>
					<?php echo plg_ambrasubs_escape($vars->transaction_id); ?>
				</center>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo plg_ambrasubs_escape($vars->amount_title); ?>:</td>
			<td>
				<center>
					<?php echo plg_ambrasubs_escape($vars->payment_amount); ?>
				</center>
			</td>
		</tr>
	</tbody>
</table>