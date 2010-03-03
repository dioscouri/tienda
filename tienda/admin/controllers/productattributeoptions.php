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

class TiendaControllerProductAttributeOptions extends TiendaController 
{
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
		$this->set('suffix', 'productattributeoptions');
	}
	
	/**
	 * delete the object and updates the product quantities
	 */
	function delete(){
		
		$this->message = '';
		$this->messagetype = '';
		$error = false;
		
		$cids = JRequest::getVar('cid', array (0), 'request', 'array');
				
		// Get the ProductQuantities model
		$qmodel = JModel::getInstance('ProductQuantities', 'TiendaModel');
		// Filter the quantities
		$qmodel->setState('filter_attributes', implode(',', $cids));
		$quantities = $qmodel->getList();
		$qtable = $qmodel->getTable();
		
		// Delete the product quantities
		foreach(@$quantities as $q){
			if (!$qtable->delete($q->productquantity_id)){
				$this->message .= $qtable->getError();
				$this->messagetype = 'notice';
				$error = true;
			}
		}
		
		if ($error)
		{
			$this->message = JText::_('Error') . " - " . $this->message;
		}
			else
		{
			$this->message = JText::_('Items Deleted');
		}
		
		// delete the option itself
		parent::delete();
	}
}

?>