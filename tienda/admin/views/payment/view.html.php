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

Tienda::load( 'TiendaViewBase', 'views._base' );

class TiendaViewPayment extends TiendaViewBase 

{
	function getLayoutVars($tpl=null) 
	{
		$layout = $this->getLayout();
		switch(strtolower($layout))
		{
			case "view":
			    $this->set( 'leftMenu', 'leftmenu_configuration' );
				$this->_form($tpl);
			  break;
			case "form":
				JRequest::setVar('hidemainmenu', '1');
				$this->_form($tpl);
			  break;
			case "default":
			default:
				$this->set( 'leftMenu', 'leftmenu_configuration' );
				$this->_default($tpl);
			  break;
		}
	}
	
	function _form($tpl=null)
	{
        parent::_form($tpl);
        
        // load the plugin
		$row = $this->getModel()->getItem();
		$params = new JParameter( $row->params, JApplicationHelper::getPath( 'plg_xml', $row->folder.DS.$row->element ), 'plugin' );
		$this->assignRef('params',$params);
	}
	
	function _defaultToolbar()
	{
	}
	
    function _viewToolbar()
    {
    	JToolBarHelper::custom( 'view', 'forward', 'forward', 'COM_TIENDA_SUBMIT', false );
    	JToolBarHelper::cancel( 'close', 'COM_TIENDA_CLOSE' );
    }
    
    /**
     * The default toolbar for editing an item
     * @param $isNew
     * @return unknown_type
     */
    function _formToolbar( $isNew=null )
    {
        $divider = false;
        $surrounding = (!empty($this->surrounding)) ? $this->surrounding : array();
        if (!empty($surrounding['prev']))
        {
            $divider = true;
            JToolBarHelper::custom('saveprev', "saveprev", "saveprev", 'COM_TIENDA_SAVE_PLUS_PREV', false);
        }
        if (!empty($surrounding['next']))
        {
            $divider = true;
            JToolBarHelper::custom('savenext', "savenext", "savenext", 'COM_TIENDA_SAVE_PLUS_NEXT', false);
        }
        if ($divider)
        {
            JToolBarHelper::divider();
        }

        JToolBarHelper::save('save');
        JToolBarHelper::apply('apply');

        if ($isNew)
        {
            JToolBarHelper::cancel();
        }
            else
        {
            JToolBarHelper::cancel( 'close', 'COM_TIENDA_CLOSE' );
        }
    }
}
