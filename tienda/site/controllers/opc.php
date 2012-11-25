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

Tienda::load( 'TiendaControllerCheckout', 'controllers.checkout', array( 'site'=>'site', 'type'=>'components', 'ext'=>'com_tienda' ) );

class TiendaControllerOpc extends TiendaControllerCheckout
{
    public function __construct()
    {
        parent::__construct();
        
        $this->set('suffix', 'opc');
    }
    
    public function setMethod()
    {
        $this->setFormat();
    }
    
    private function setFormat( $set='raw' )
    {
        $format = JRequest::getVar('format');
        if ($format != $set) {
            JRequest::setVar('format', $set);
        }
    }
}