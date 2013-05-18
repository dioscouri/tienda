<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'menu.css', 'media/com_tienda/css/');
$steps = @$this->steps;
$current_step = @$this->current_step;
?>

<div class="progressbar">
	<?php 
		$i = 0;
		foreach ($steps as $step)
		{
            ?>
    		<span class="step <?php if($i == $current_step) echo 'current-step'; ?>">
                <?php echo ($i+1).". ".JText::_( $step ); ?>
    		</span>
            <?php
    		$i++;
		}
	?>
</div>
