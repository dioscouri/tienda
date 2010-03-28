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

JLoader::import( 'com_tienda.models._base', JPATH_ADMINISTRATOR.DS.'components' );

class TiendaModelEmails extends TiendaModelBase 
{
	function getTable()
	{
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$table = JTable::getInstance( 'Config', 'TiendaTable' );
		return $table;
	}
	
	public function getList(){
		
		jimport('joomla.language.helper');
		
		$list = JLanguageHelper::createLanguageList(JLanguageHelper::detectLanguage());
		
		foreach($list as $l){
			$l['link'] = "index.php?option=com_tienda&view=emails&task=edit&id=".$l['value'];
			$item = new JObject();
			
			foreach($l as $k => $v){
				$item->$k = $v; 
			}
			$result[] = &$item;			
		}
		
		return $result;
	}
	
	public function getItem( $id = 'en-GB') {
		$lang = JLanguage::getInstance($id);
		// Load admin & site language
		$lang->load('com_tienda', JPATH_ADMINISTRATOR, $id, true);
		$lang->load('com_tienda', JPATH_SITE, $id, true);
		
		$lang->strings = array();
		
		$strings = $lang->_strings;
		foreach($strings as $k =>$v){
			if(stripos( $k, 'EMAIL_'))
				$lang->strings[$k] = $v;
		}
		
		return $lang;
		
	}
}
