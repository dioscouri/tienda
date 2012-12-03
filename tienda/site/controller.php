<?php
/**
 * @version	0.1
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class TiendaController extends DSCControllerSite
{
    public $default_view = 'products';
    public $router = null;
    public $defines = null;
    
    function __construct( $config=array() )
    {
        parent::__construct( $config );
        
        $this->defines = Tienda::getInstance();
        
        Tienda::load( "TiendaHelperRoute", 'helpers.route' );
        $this->router = new TiendaHelperRoute();
        
        $this->user = JFactory::getUser();
    }
}
