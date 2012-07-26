<?php 
defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'leftmenu_admin.css', 'media/com_calendar/css/');
?>

<div id="<?php echo $this->name; ?>" >
    <ul class="nav nav-pills nav-stacked">
    <?php 
    foreach ($this->items as $item) {
        ?>
        <li <?php  if ($item[2] == 1) {echo 'class="active"'; } ?> >
        <?php 
		
        if ($this->hide) {
            
            if ($item[2] == 1) {
            ?>  <span class="nolink active"><?php echo $item[0]; ?></span> <?php
            } else {
            ?>  <span class="nolink"><?php echo $item[0]; ?></span> <?php    
            }
            
        } else {
            
            if ($item[2] == 1) {
            ?> <a class="active" href="<?php echo $item[1]; ?>"><?php echo $item[0]; ?></a> <?php
            } else {
            ?> <a href="<?php echo $item[1]; ?>"><?php echo $item[0]; ?></a> <?php   
            }        
        }
		?>
		</li>
		<?php
        
    }
    ?>
    </div>
</div>