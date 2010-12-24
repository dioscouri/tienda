<?php
/**
 * @package    Tienda
 * @author     Dioscouri
 * @link     http://www.dioscouri.com
 * @copyright Copyright (C) 2010 Dioscouri. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

$document->addStyleSheet( JURI::root(true).'/administrator/modules/mod_tienda_search_admin/tmpl/stylesheet.css');
?>

<form action="index.php?option=com_tienda&view=dashboard&task=search" method="post">
    <div class="mod_tienda_search_admin<?php echo $class_suffix; ?>">
        <input type="text" name="tienda_search_admin_keyword" class="tienda_search_admin_keyword<?php echo $class_suffix; ?>" value="" />
        <?php echo TiendaSelect::view( "", "tienda_search_admin_view" ); ?>
        <input type="submit" class="button" value="<?php echo JText::_( "Quick Search" ); ?>" name="tienda_search_admin_submit" />
        <?php if (empty($display_outside)) : ?>
            <input type="hidden" name="task" value="search" />
        <?php endif; ?>
    </div>
</form>
