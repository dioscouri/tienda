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

Tienda::load( 'TiendaTable', 'tables._base' );

class TiendaTableGroups extends TiendaTable 
{
	function TiendaTableGroups ( &$db ) 
	{
		
		$tbl_key 	= 'group_id';
		$tbl_suffix = 'groups';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'tienda';
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );	
	}
	
	function check()
	{
		$db			= $this->getDBO();
		$nullDate	= $db->getNullDate();
		if (empty($this->created_date) || $this->created_date == $nullDate)
		{
			$date = JFactory::getDate();
			$this->created_date = $date->toMysql();
		}
		if (empty($this->modified_date) || $this->modified_date == $nullDate)
		{
			$date = JFactory::getDate();
			$this->modified_date = $date->toMysql();
		}
		$this->filterHTML( 'group_name' );
		if (empty($this->group_name))
		{
			$this->setError( JText::_('COM_TIENDA_NAME_REQUIRED') );
			return false;
		}
		$this->filterHTML( 'group_description' );
		return true;
	}
	
	/**
	 * Stores the object
	 * @param object
	 * @return boolean
	 */
	function store($updateNulls=false) 
	{
		$date = JFactory::getDate();
		$this->modified_date = $date->toMysql();
		$store = parent::store($updateNulls);		
		return $store;		
	}
	
	/**
	 * Delete also the prices linked to this group
	 */
	function delete($oid=null)
	{
		$k = $this->_tbl_key;
		$default_user_group = Tienda::getInstance()->get('default_user_group', '1');
		
		if($oid)
		{
			$key = $oid;
		}
		else
		{
			$key = $this->$k;
		}
		
		
		if( $key != $default_user_group )
		{
			$return = parent::delete($oid);
			
			if($return)
			{
				
				// Delete user group relationships
				$model = JModel::getInstance('UserGroups', 'TiendaModel');
				$model->setState('filter_group', $this->$k);
				$links = $model->getList();
				
				if($links)
				{
					$table = JTable::getInstance('UserGroups', 'TiendaTable');
					foreach($links as $link)
					{
						$table->delete($link->user_id);
					}
				}
				
				// Delete prices
				$model = JModel::getInstance('ProductPrices', 'TiendaModel');
				$model->setState('filter_user_group', $this->$k);
				$prices = $model->getList();
				
				if($prices)
				{
					$table = JTable::getInstance('ProductPrices', 'TiendaTable');
					foreach($prices as $price)
					{
						$table->delete($price->user_id);
					}
				}
			}
		}
		else
		{
			$this->setError(JText::_('COM_TIENDA_COM_TIENDA_YOU_CANT_DELETE_THE_DEFAULT_USER_GROUP'));
			return false;
		}
		
		return $return;
			
	}
}
