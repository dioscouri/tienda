<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

JLoader::import( 'com_tienda.views._base', JPATH_ADMINISTRATOR.DS.'components' );

class TiendaViewOrders extends TiendaViewBase 
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
        	case "print":
            case "view":
                $this->_form($tpl);
              break;
            case "form":
                JRequest::setVar('hidemainmenu', '1');
                $this->_form($tpl);
              break;
            case "default":
            default:
                $this->_default($tpl);
              break;
        }
    }
	    
	/**
	 * Method for collecting custom information for displaying an order 
	 * @return void
	 **/
	function _form($tpl = null) 
	{
		parent::_form($tpl);
		JLoader::import( 'com_tienda.library.url', JPATH_ADMINISTRATOR.DS.'components' );

		$model = $this->getModel();
		$row = $model->getItem();	

		// Get the shop country name
		$countryModel = JModel::getInstance('Countries', 'TiendaModel');
		$countryModel->setId(TiendaConfig::getInstance()->get('shop_country'));
		$countryItem = $countryModel->getItem();
		if($countryItem){
			$row->shop_country_name = $countryItem->country_name;
		}
		
		// Get the shop zone name
		$zoneModel = JModel::getInstance('Zones', 'TiendaModel');
		$zoneModel->setId(TiendaConfig::getInstance()->get('shop_zone'));
		$zoneItem = $zoneModel->getItem();
		if($zoneItem){
			$row->shop_zone_name = $zoneItem->zone_name;
		}

		//retrieve user information and make available to page
		$user_id = JRequest::getVar('userid', 0, 'get', 'int');
		$row->user_id = $user_id;
		if ($user_id> 0)
		{
			//get the user information from jos_users and jos_tienda_userinfo
			$userModel 	= JModel::getInstance( 'Users', 'TiendaModel' );
			$userModel->setId($user_id);
			$userItem = $userModel->getItem();
			if ($userItem)
			{
				$row->userinfo = $userItem;
			} 		
		}
		$this->assign('row', $row );
    }
    
    /**
     * The default toolbar for a list
     * @return unknown_type
     */
    function _defaultToolbar()
    {
        // JToolBarHelper::editList();
        //JToolBarHelper::deleteList( JText::_( 'VALIDDELETEITEMS' ) );
        //JToolBarHelper::addnew();
    }
    
    /**
     * (non-PHPdoc)
     * @see tienda/admin/views/TiendaViewBase#_viewToolbar($isNew)
     */
    function _viewToolbar( $isNew=null )
    {
        $divider = false;
        $model = $this->getModel();
        JLoader::import( 'com_tienda.helpers.order', JPATH_ADMINISTRATOR.DS.'components' );
        $surrounding = TiendaHelperOrder::getSurrounding( $model->getId() );
        if (!empty($surrounding['prev']))
        {
            $divider = true;
            JToolBarHelper::custom('prev', "prev", "prev", JText::_( 'Prev' ), false);
        }
        if (!empty($surrounding['next']))
        {
            $divider = true;
            JToolBarHelper::custom('next', "next", "next", JText::_( 'Next' ), false);  
        }
        if ($divider)
        {
            JToolBarHelper::divider();
        }
        $this->assign('surrounding', $surrounding);
        parent::_viewToolbar($isNew);
    }
}
