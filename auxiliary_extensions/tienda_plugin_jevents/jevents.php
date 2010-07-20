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
        
        // include custom tables
        JTable::addIncludePath( JPATH_SITE.DS.'plugins'.DS.'tienda'.DS.'jevents'.DS.'tables' );
    }

    /**
     * will return the HTML of the event maping 
     * @param  object
     * @return string
     */
    function onAfterDisplayProductFormRightColumn( $product )
    {
        if (empty($product->product_id))
        {
            // this is a new product
        }
        
        // events
        $this->includeCustomModel('ElementEvent');
        $elementEventModel = JModel::getInstance( 'ElementEvent', 'TiendaModel' );
        $elementEvent_terms = $elementEventModel->_fetchElement( 'jevent', $product->product_id );
        $resetEvent_terms = $elementEventModel->_clearElement( 'jevent', '0' );
        
        $vars->product = $product;
        $vars->elementEvent_terms = $elementEvent_terms;
        $vars->resetEvent_terms = $resetEvent_terms;
        echo $this->_getLayout( 'product_form', $vars );
        return null;
    }

	/*
	 * will update or insert the mapping table on the saving of the product
	 *
	 * @return unknown_type
	 */
	function onAfterSaveProducts( $product )
	{
        jimport('joomla.application.component.helper');
        // check jevent is installed
        $isInstalled = JComponentHelper::isEnabled('com_jevents', false);

		// if JEvent is installed
		if($isInstalled)
		{
			$post_data =JRequest::get('POST');
			$event = $post_data['jevent'];

			$this->includeCustomModel('JEventsEventsProducts');
			$model = JModel::getInstance('JEventsEventsProducts', 'TiendaModel');

			$this->includeCustomTables('JEventsEventsProducts');
			$row = JTable::getInstance('JEventsEventsProducts', 'TiendaTable');
			
			$row->load(array('product_id'=>$post_data['id']));
          
			// creating an array for the binding
			$productEnvent= array();
			$productEnvent['product_id']=$post_data['id'];
			$productEnvent['event_id']=$post_data['jevent'];
			$row->bind( $productEnvent );
			if(!$row->save())
			{
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
        Tienda::load( 'TiendaUrl', 'library.url' );
        Tienda::load( 'TiendaSelect', 'library.select' );
        Tienda::load( 'TiendaGrid', 'library.grid' );
	    
		$this->includeCustomModel('JEventsEvents');
       	$model = JModel::getInstance( 'JEventsEvents', 'TiendaModel' );
       	$items = $model->getList();
       	// here you could loop thru the items if you wanted, to add an ->link to each one, for example
       	
       	$vars->state = $model->getState();
        $vars->items = $items;
        echo $this->_getLayout( 'list', $vars );
	}
}
