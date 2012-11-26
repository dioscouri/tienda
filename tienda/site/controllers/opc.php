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
        $method = JRequest::getVar('method');
        $session = JFactory::getSession();
        $session->set('tienda.opc.method', $method);
        $response = $this->getResponseObject();
        
        switch(strtolower($method)) {
            case "guest":
                $response->summary->html = JText::_("COM_TIENDA_GUEST_CHECKOUT");
                break;
            case "register":
            default:
                $response->summary->html = JText::_("COM_TIENDA_GUEST_REGISTERING_AS_NEW_USER");
                break;
        }
        
        echo json_encode($response);
    }
    
    public function setBilling()
    {
        $this->setFormat();

        $data = new stdClass();
        
        $session = JFactory::getSession();
        $session->set('tienda.opc.billing', $data);
        $response = $this->getResponseObject();
    
        $post = JRequest::get('post');
        
        $response->summary->html = DSC::dump($post);
    
        echo json_encode($response);
    }
    
    private function setFormat( $set='raw' )
    {
        $format = JRequest::getVar('format');
        if ($format != $set) {
            JRequest::setVar('format', $set);
        }
    }
    
    private function getResponseObject()
    {
        $response = new stdClass();
        $response->summary = new stdClass();
        $response->summary->html = ''; // the content to be inserted into the summary element
        
        return $response;
    }
}