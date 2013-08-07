<?php
/**
 * @package    Tienda
 * @author     Dioscouri Design
 * @link     http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
?>

<?php if (!empty($items)) { ?>
    <ul>
    <?php foreach ($items as $item) {
        $itemid_string = null;
        if ($itemid = $helper->model->getItemid($item->wishlist_id) ) {
            $itemid_string = "&Itemid=" . $itemid;
        }        
        ?>
        <li>
            <a href="<?php echo JRoute::_( "index.php?option=com_tienda&view=wishlists&task=view&id=" . $item->wishlist_id . $itemid_string ); ?>">
                <span class="wishlist-name wishlist-<?php echo $item->wishlist_id; ?>">
                    <?php echo $item->wishlist_name; ?>
                </span>
            </a>
        </li>
    <?php } ?>
    </ul>
<?php } ?>