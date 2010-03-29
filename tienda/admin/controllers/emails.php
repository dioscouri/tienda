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
defined( '_JEXEC' ) or die( 'Restricted access' );

class TiendaControllerEmails extends TiendaController 
{
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
		
		$this->set('suffix', 'emails');
				
	}
	
	function save(){
		
		$id = JRequest::getVar('id', 'en-GB');
		$temp_values = JRequest::get('post');
		
		$model = $this->getModel('Emails', 'TiendaModel');
		
		// Filter values
		$prefix = $model->email_prefix;
		$values = array();
		foreach($temp_values as $k =>$v){
			if(stripos($k, $prefix) === 0)
				$values[$k] = $v;
		}
		
		
		$lang = $model->getItem();
		$path = $lang->path;
		
		$msg = JText::_('Saved');
		
		jimport('joomla.filesystem.file');

		if (JFile::exists($path))
		{
			$original = new JRegistry();
			$original->loadFile($path);
			
			$registry = new JRegistry();
			$registry->loadArray($values);
			
			// Store the modified data
			foreach($registry->_registry['default']['data'] as $k => $v){
				$original->_registry['default']['data']->$k = $v;
			}
			
			$txt = $original->toString();
			
			$success = JFile::write($path, $txt);

			if(!$success)
				$msg = JText::_('Error Saving the new Language File');
				
		}
			
		$this->setRedirect( 'index.php?option=com_tienda&view=emails', $msg, 'message' );
	}

    
}

?>
