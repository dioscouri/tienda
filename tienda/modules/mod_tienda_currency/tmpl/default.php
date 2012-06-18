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

Tienda::load('TiendaHelperBase', 'helpers._base');
Tienda::load('TiendaSelect', 'library.select');
JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
$helper = TiendaHelperBase::getInstance();

$url = JRoute::_( 'index.php?option=com_tienda&view=products&task=setCurrency&return='.base64_encode( JURI::getInstance()->toString() ) , false);
// Check the currently selected currency
$selected = TiendaHelperBase::getSessionVariable('currency_id', Tienda::getInstance()->get( 'default_currencyid', 1 ) );
?>

<div id="currency">
    <form action="<?php echo $url; ?>" method="post" name="currencySwitch">
        <?php echo JText::_('COM_TIENDA_SELECT_CURRENCY').': '; ?>
        <?php $attribs = array( 'onChange' => 'document.currencySwitch.submit(); '); ?>
        <?php echo TiendaSelect::currency($selected, 'currency_id', $attribs); ?> 
    </form>
</div>
  