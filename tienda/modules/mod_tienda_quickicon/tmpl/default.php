<?php
/**
 * @version    1.5
 * @package    Tienda
 * @author     Dioscouri
 * @link     http://www.dioscouri.com
 * @copyright Copyright (C) 2010 Dioscouri. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

$document->addStyleSheet(Tienda::getURL()."/css/tienda_admin.css");
?>
<div class="tcpanel">
<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
<div class="icon"><a href="index.php?option=com_tienda">
<img src="<?php echo $img ?>" style="width: 96px;" />
<span><?php echo $text; ?></span> </a></div>
</div>
</div>