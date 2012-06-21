<?php 
defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'menu.css', 'media/com_tienda/css/');
?>

<div id="<?php echo $this->name; ?>" class="submenu">


<ul>
<?php 
foreach ($this->items as $item) {
    echo '<li>';
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
	
	$names = explode( ' ', $item[0] );
    $name = strtolower( $names[0] );
    //$submenu = new TiendaMenu( 'submenu_' . $name, '1' );
	 $submenu = TiendaMenu::getInstance( 'submenu_' . $name, '1' );
	
   $submenu_items = $submenu->_menu->getItems();
	?>
	<ul class="submenu_dropdown">
        <?php
       
       
        foreach ($submenu_items as $submenu_item)
        {
            ?>
            <li>
                <?php
                if ($submenu_item[2] == 1) {
                ?> <a class="active" href="<?php echo $submenu_item[1]; ?>"><?php echo $submenu_item[0]; ?></a> <?php
                } else {
                ?> <a href="<?php echo $submenu_item[1]; ?>"><?php echo $submenu_item[0]; ?></a> <?php   
                }                    
                ?>
            </li>
            <?php
        }
        ?>
        </ul>
        <?php
	
	
	
    echo '</li>';
}
?>
</ul>
</div>