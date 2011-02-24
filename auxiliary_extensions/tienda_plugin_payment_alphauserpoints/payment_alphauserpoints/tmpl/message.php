<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php
	if( empty( $vars->errors ) )
	{
		$color = 'green';
	}
	else 
	{
		$color = 'pink';
	}
?>

<div class="note_<?php echo @$color; ?>">
	<?php echo @$vars->message; ?>
</div>
