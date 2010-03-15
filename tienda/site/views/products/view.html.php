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

JLoader::import( 'com_tienda.views._base', JPATH_SITE.DS.'components' );
JLoader::import( 'com_tienda.helpers.product', JPATH_ADMINISTRATOR.DS.'components' );
JLoader::import( 'com_tienda.helpers.category', JPATH_ADMINISTRATOR.DS.'components' );
JLoader::import( 'com_tienda.library.url', JPATH_ADMINISTRATOR.DS.'components' );

class TiendaViewProducts extends TiendaViewBase  
{
	/**
	 * 
	 * @param $tpl
	 * @return unknown_type
	 */
	function display($tpl=null) 
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
				$this->_default($tpl);
			  break;
		}
		parent::display($tpl);
	}
	
	function _default() 
	{
	    parent::_default();

	    // TODO Move all of this to the controller
	    $title = (isset($this->state->category_name))
            ? $this->state->category_name
            : JText::_( "All Categories" );
    
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $cmodel = JModel::getInstance( 'Categories', 'TiendaModel' );
        $level = (!empty($this->state->filter_category)) ? $this->state->filter_category : '1';
        $cmodel->setState('filter_level', $level);
        $cmodel->setState('filter_enabled', '1');
        $cmodel->setState('order', 'tbl.lft');
        $cmodel->setState('direction', 'ASC');
        $citems = $cmodel->getList();
        if ($level > 1) {
            $tcmodel = JModel::getInstance( 'Categories', 'TiendaModel' );
            $tcmodel->setId($level);
            $cat = $tcmodel->getItem();
        } else {
            $cat = new JObject(); 
            $cat->category_id = ""; 
            $cat->category_description = "";
        }
        
        $this->assign( 'level', $level);
        $this->assign( 'title', $title );
	    $this->assign( 'cat', $cat );
        $this->assign( 'citems', $citems );	    
	}

}