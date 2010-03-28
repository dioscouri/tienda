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
		$values = JRequest::get('post');

		unset($values['task']);
		unset($values['id']);
		unset($values['option']);
		
		$model = $this->getModel('Emails', 'TiendaModel');
		$lang = $model->getItem();
		$paths = $lang->getPaths('com_tienda');
		
		$msg = JText::_('Saved');
		
		jimport('joomla.filesystem.file');
		foreach($paths as $p => $x){		
			
			if (JFile::exists($p))
			{
				$original = new JRegistry();
				$original->loadFile($p);
				
				echo Tienda::dump($original);
				
				$registry = new JRegistry();
				$registry->loadArray($values);
				
				echo Tienda::dump($registry);
				
				$registry->merge($original);
				
				echo Tienda::dump($registry);
				
				$txt = $registry->toString();
				
				$success = JFile::write($p, $txt);
	
				if(!$success)
					$msg = JText::_('Error Saving the new Language File');
					
			}
			
		}

		$this->setRedirect( 'index.php?option=com_tienda&view=emails', $msg, 'message' );
	}

    
}

?>
