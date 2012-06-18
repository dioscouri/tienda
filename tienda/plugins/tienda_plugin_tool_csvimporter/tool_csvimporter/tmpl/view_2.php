<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $preview = @$vars->preview; ?>
<?php $header = array_shift($preview); ?>

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
    					if(is_array($field))
    					{
    						foreach($field as $key => $subfield)
    						{
    							if(is_array($subfield))
    							{
    								echo $key;
    								echo '<ul>';
    								foreach($subfield as $detail)
    								{
    									echo '<li>'.$detail.'</li>';
    								}	
    								echo '</ul>';
    							}
    							else
    							{
    								echo $subfield;
    							}
    						}	
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