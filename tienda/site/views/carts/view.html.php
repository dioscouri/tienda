<?php
/**
 * @version    1.5
 * @package    Tienda
 * @author     Dioscouri Design
 * @link     http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

JLoader::import( 'com_tienda.views._base', JPATH_SITE.DS.'components' );

class TiendaViewCarts extends TiendaViewBase  
{
    /**
     * 
     * @param $tpl
     * @return unknown_type
     */
    function getLayoutVars($tpl=null) 
    {  
        $layout = $this->getLayout();
        switch(strtolower($layout))
        {
            case "view":
                $this->_form($tpl);
              break;
            case "form":
                $this->_form($tpl);
              break;
            case "default":
            default:
                Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
                $suffix = strtolower( TiendaHelperCarts::getSuffix() );

                switch($suffix) 
                {
                    case 'Sessioncarts':
                    case 'sessioncarts':
                        JModel::addIncludePath( JPATH_SITE.DS.'components'.DS.'com_tienda'.DS.'models' );
                        $model = JModel::getInstance($suffix, 'TiendaModel');
                        $list = $model->getList();
                        $this->assign('items', $list);
                        break;
                    default:
                        $this->_default($tpl);
                        break;
                }
              break;
        }
    }
}