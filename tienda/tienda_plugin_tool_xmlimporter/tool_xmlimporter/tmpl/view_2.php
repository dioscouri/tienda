<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $preview = @$vars->preview; ?>
<?php $header = array_keys((array)$preview[0]); ?>

<div class="note_green">
	<table>
		<thead>
		<tr>
			 <?php foreach($header as $h): ?>
			 	<th>
			 		<?php echo $h; ?>
			 	</th>
			 <?php endforeach;?>
		</tr>
		</thead>
    <?php foreach($preview as $row): ?>
    	
    	<tr>
    		<?php foreach($row as $field): ?>
    			<td>
    				<?php
    					if(count($field))
    					{
    						echo JText::_('COM_TIENDA_TOTAL_NUMBER'. count($field));
    					} 
    					else
    					{
    						echo $field; 
    					}
    					?>
    			</td>
    		<?php endforeach;?>
    	</tr>
    <?php endforeach;?>
    </table>
</div>