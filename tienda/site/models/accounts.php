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

Tienda::load( 'TiendaModelBase', 'models._base' );

class TiendaModelAccounts extends TiendaModelBase 
{
	function getTable($name='', $prefix=null, $options = array())
	{
		DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
		$table = DSCTable::getInstance( 'UserInfo', 'TiendaTable' );
		return $table;
	}
	
    protected function _buildQueryWhere(&$query)
    {
        $filter_userid      = $this->getState('filter_userid');

        if ($filter_userid)
        {
            $query->where('tbl.user_id = '.$filter_userid);
        }
    }
}
