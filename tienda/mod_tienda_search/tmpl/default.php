<?php
/**
 * @version    1.5
 * @package    Tienda
 * @author     Dioscouri Design
 * @link     http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaSelect', 'library.select' );
$url = JRoute::_( 'index.php?option=com_tienda&controller=products&task=search' , false);
?>

<div id="productSearch">
    <form action="<?php echo $url; ?>" method="post" name="productSearch" onSubmit="if(this.elements['filter'].value == '<?php echo JText::_( 'SKU, Model # or Keyword' ); ?>') this.elements['filter'].value = '';">
        <?php echo JText::_('Search').': '; ?>
        <?php echo TiendaSelect::category('', 'filter_category', '', '', true, '', 'All Categories', '', '1'); ?>
        <input type="text" name="filter" value="<?php echo JText::_( 'SKU, Model # or Keyword' ); ?>" onclick="this.value='';"/> 
        <input type="submit" value="Submit" />
    </form>
</div>
  