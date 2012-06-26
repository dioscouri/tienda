<?php
/**
 * @version 1.5
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

/** Import library dependencies */
jimport('joomla.plugin.plugin');

class plgQuickiconTiendaIcon extends JPlugin 
{
    
    function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
    }
    
    public function onGetIcons($context)
    {
        if (
            $context == $this->params->get('context', 'mod_quickicon')
            && JFactory::getUser()->authorise('core.manage', 'com_content')
        ){
            return array(array(
                'link' => 'index.php?option=com_tienda',
                'image' => JURI::root().'/media/com_tienda/images/tienda.png',
                'text' => 'Tienda Dashboard',
                'id' => 'plg_quickicon_tiendaicon'
            ));
        } else return;
    }
}
