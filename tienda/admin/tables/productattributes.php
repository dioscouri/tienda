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

class TiendaTableProductAttributes extends TiendaTable 
{
	function TiendaTableProductAttributes ( &$db ) 
	{
		
		$tbl_key 	= 'productattribute_id';
		$tbl_suffix = 'productattributes';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'tienda';
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );
	}
	
	/**
	 * Checks row for data integrity.
	 *  
	 * @return unknown_type
	 */
	function check()
	{
		if (empty($this->product_id))
		{
			$this->setError( JText::_('COM_TIENDA_PRODUCT_ASSOCIATION_REQUIRED') );
			return false;
		}
        if (empty($this->productattribute_name))
        {
            $this->setError( JText::_('COM_TIENDA_ATTRIBUTE_NAME_REQUIRED') );
            return false;
        }
		return true;
	}
	
	/**
	 * Adds context to the default reorder method
	 * @return unknown_type
	 */
    function reorder($where = '')
    {
        parent::reorder('product_id = '.$this->_db->Quote($this->product_id) );
    }
    
    /**
     * Run function after saving 
     */
    function save($src='', $orderingFilter = '', $ignore = '')
    {
        if ($return = parent::save($src, $orderingFilter, $ignore))
        {
            Tienda::load( "TiendaHelperProduct", 'helpers.product' );
            $helper = TiendaHelperBase::getInstance( 'product' );
            $helper->doProductQuantitiesReconciliation( $this->product_id, '0' );
        }
        
        return $return;
    }
    
    /**
     * Run function after deleteing
     */
    function delete( $oid=null, $doReconciliation=true )
    {
        $k = $this->_tbl_key;
        if ($oid) {
            $this->$k = intval( $oid );
        }

        if ($doReconciliation) 
        {
            if ($oid)
            {
                $row = DSCTable::getInstance('ProductAttributes', 'TiendaTable');
                $row->load( $oid );
                $product_id = $row->product_id;
            }
            else
            {
                $product_id = $this->product_id;
            }            
        }
        
        if ($return = parent::delete( $oid ))
        {
            DSCModel::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_tienda/models' );
            $model = DSCModel::getInstance( 'ProductAttributeOptions', 'TiendaModel' );
            $model->setState('filter_attribute', $this->$k );
            if ($items = $model->getList()) 
            {
                $table = $model->getTable();
                foreach ($items as $item) 
                {
                    if (!$table->delete( $item->productattributeoption_id ))
                    {
                        $this->setError( $table->getError() );
                    }                    
                }
            }
            
            if ($doReconciliation) 
            {
                Tienda::load( "TiendaHelperProduct", 'helpers.product' );
                TiendaHelperProduct::doProductQuantitiesReconciliation( $product_id );
            }
        }
        
        return parent::check();
    }
	
}
