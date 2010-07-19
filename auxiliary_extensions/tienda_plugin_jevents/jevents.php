<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaPluginBase', 'library.plugins._base' );
class plgTiendaJEvents extends TiendaPluginBase
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'jevents';
    
	function plgTiendaJEvents(& $subject, $config) 
{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}

	/**
	 * will return the HTML of the event maping 
	 * @param  object
	 * @return string
	 */
	function onAfterDisplayProductFormRightColumn( $model )
	{
		$productid = 1;
//       	if($model->_item !=null && !empty($model->_item)) {
//       		$productid=$model->_item->product_id;
//       	}
//        
       	// events
//       	//$elementEventModel 	= JModel::getInstance( 'ElementEvent', 'TiendaModel' );
//       	$this->includeTiendaTables();
		$this->includeCustomModel('ElementEvent');
       	$elementEventModel 	=JModel::getInstance( 'ElementEvent', 'TiendaModel' );
       	$elementEvent_terms 		= $elementEventModel->_fetchElement( 'jevent', $productid );
		$resetEvent_terms			= $elementEventModel->_clearElement( 'jevent', '0' );
		
		$vars->elementEvent_terms = $elementEvent_terms;
		$vars->resetEvent_terms =$resetEvent_terms;
		 echo $this->_getLayout( 'event_form', $vars );
        return null;
  // return $html;                            
   
	}

	/*
	 * will update or insert the mapping table on the saving of the product
	 *
	 * @return unknown_type
	 */
	function onAfterSaveProducts( $product )
	{
		// check jevent is installed
		$isInstalled=JComponentHelper::isEnabled('com_jevents', false);
		jimport('joomla.application.component.helper');

		// if JEvent is installed

		if($isInstalled)
		{
			$post_data=JRequest::get('POST');
			$event= $post_data['jevent'];

			$this->includeTiendaTables();
			$this->includeCustomModel('ProductJEvent');
			$model = JModel::getInstance('ProductJEvent', 'TiendaModel');
				
			$row = $model->getTable();
			$row->load(array('product_id'=>$post_data['id']));

			// creating an array for the binding
			$productEnvent= array();
			$productEnvent['product_id']=$post_data['id'];
			$productEnvent['event_id']=$post_data['jevent'];
			$row->bind( $productEnvent );
				
			if(!$row->save()){

				// TODO : If data does not save properly

				$this->messagetype  = 'notice';
				$this->message      = JText::_( 'Save Failed' )." - ".$row->getError();
			}
		}
			
	}
	
	/*
	 * to show the list of the events s
	 */
	
	
function showEvents()
	{
		echo "I am here ";
	//	$this->includeCustomModel('ElementEvent');
//       	$elementEventModel 	=JModel::getInstance( 'ElementEvent', 'TiendaModel' );
//       	$rows=$elementEventModel->getList();
//       	
        $this->includeCustomView('ElementEvent');
//      	$elementViewModel 	=JModel::getInstance( 'ElementEvent', 'TiendaView' );
//        $elementViewModel->assign("List",$rows);
//        $elementViewModel->assign("pagination",$elementEventModel->getPagination());
//        $elementViewModel->display();

		$model = JModel::getInstance( 'ElementEvent', 'TiendaModel' );
		$view = new  JView ( 'ElementEvent', 'TiendaView' );
		$view->assign( "items",$model->getList() );
		$view->assign( "pagination", $model->getPagination() );
		$view->display();
        
		//return $text;
       	//die();		
		
	}
}
