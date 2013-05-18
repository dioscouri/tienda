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

Tienda::load( 'TiendaViewBase', 'views._base', array( 'site'=>'site', 'type'=>'components', 'ext'=>'com_tienda' ) );
Tienda::load( "TiendaHelperProduct", 'helpers.product' );
Tienda::load( 'TiendaHelperCategory', 'helpers.category' );
Tienda::load( 'TiendaUrl', 'library.url' );

class TiendaViewProducts extends TiendaViewBase
{
    function __construct( $config=array() )
    {
        parent::__construct( $config );
    
        if (empty($this->helpers)) {
            $this->helpers = array();
        }
    
        Tienda::load( "TiendaHelperProduct", 'helpers.product' );
        $this->helpers['product'] = new TiendaHelperProduct();
    }
    
	/**
	 *
	 * @param $tpl
	 * @return unknown_type
	 */
	function display($tpl=null, $perform = true )
	{
		$model = $this->getModel();
		$task = $model->getState( 'task' );
		switch(strtolower($task))
		{
			case "view":
				$this->_form( $tpl );
				break;
			case "display":
				$this->_default( $tpl );
				break;
			case "product_comments":
				$this->_default( $tpl, true );
				break;
			default:
				$this->_default_light( $tpl );
				break;
		}
		parent::display($tpl, false );
	}

	/**
	 * Basic methods for a form
	 * @param $tpl
	 * @return unknown_type
	 */
	function _form($tpl='')
	{
		Tienda::load( 'TiendaSelect', 'library.select' );
		$model = $this->getModel();

		// get the data
		$row = $model->getItem( true, false );
		JFilterOutput::objectHTMLSafe( $row );
		$this->assign('row', $row );

		// form
		$form = array();
		$controller = strtolower( $this->get( '_controller', JRequest::getVar('controller', JRequest::getVar('view') ) ) );
		$view = strtolower( $this->get( '_view', JRequest::getVar('view') ) );
		$task = strtolower( $this->get( '_task', 'edit' ) );
		$form['action'] = $this->get( '_action', "index.php?option=com_tienda&controller={$controller}&view={$view}&task={$task}&id=".$model->getId() );
		$form['validation'] = $this->get( '_validation', "index.php?option=com_tienda&controller={$controller}&view={$view}&task=validate&format=raw" );
		$form['validate'] = "<input type='hidden' name='".JUtility::getToken()."' value='1' />";
		$form['id'] = $model->getId();
		$this->assign( 'form', $form );
	}
}