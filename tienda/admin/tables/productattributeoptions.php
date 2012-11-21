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

class TiendaTableProductAttributeOptions extends TiendaTable 
{
	function TiendaTableProductAttributeOptions ( &$db ) 
	{
		
		$tbl_key 	= 'productattributeoption_id';
		$tbl_suffix = 'productattributeoptions';
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
		if (empty($this->productattribute_id))
		{
			$this->setError( JText::_('COM_TIENDA_PRODUCT_ATTRIBUTE_ASSOCIATION_REQUIRED') );
			return false;
		}
        if (empty($this->productattributeoption_name))
        {
            $this->setError( JText::_('COM_TIENDA_ATTRIBUTE_OPTION_NAME_REQUIRED') );
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
        parent::reorder('productattribute_id = '.$this->_db->Quote($this->productattribute_id) );
    }

    /**
     * Run function when saving
     * @see tienda/admin/tables/TiendaTable#save()
     */
    function save($src='', $orderingFilter = '', $ignore = '')
    {
    	if ($return = parent::save( $src, $orderingFilter, $ignore))
    	{
            $pa = JTable::getInstance('ProductAttributes', 'TiendaTable');
            $pa->load( $this->productattribute_id );
            
            Tienda::load( "TiendaHelperProduct", 'helpers.product' );
            $helper = TiendaHelperBase::getInstance( 'product' );
            $helper->doProductQuantitiesReconciliation( $pa->product_id );
    	}
        
    	return $return;
    }
    
    /**
     * Run function when deleting
     * @see tienda/admin/tables/TiendaTable#save()
     */
    function delete( $oid=null, $doReconciliation=true )
    {
        $k = $this->_tbl_key;
        if ($oid) {
            $this->$k = intval( $oid );
        }
        
        if ($doReconciliation) 
        {
            $pa = JTable::getInstance('ProductAttributes', 'TiendaTable');
            if ($oid)
            {
                $row = JTable::getInstance('ProductAttributeOptions', 'TiendaTable');
                $row->load( $oid );

                $pa->load( $row->productattribute_id );                
            }
            else
            {
                $pa->load( $this->productattribute_id );
            }
            $product_id = $pa->product_id;
        }

        
        if ($return = parent::delete( $oid ))
        {
            DSCModel::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_tienda/models' );
            $model = DSCModel::getInstance( 'ProductAttributeOptionValues', 'TiendaModel' );
            $model->setState('filter_option', $this->$k );
            if ($items = $model->getList())
            {
                $table = $model->getTable();
                foreach ($items as $item)
                {
                    if (!$table->delete( $item->productattributeoptionvalue_id ))
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
