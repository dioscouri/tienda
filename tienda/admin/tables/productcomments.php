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

class TiendaTableProductComments extends TiendaTable 
{
	function TiendaTableProductComments( &$db ) 
	{
		$tbl_key 	= 'productcomment_id';
		$tbl_suffix = 'productcomments';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= "tienda";
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );	
	}

	function check()
	{
		$nullDate	= $this->_db->getNullDate();

		if (empty($this->created_date) || $this->created_date == $nullDate)
		{
			$date = JFactory::getDate();
			$this->created_date = $date->toMysql();
		}		
		return true;
	}
	
	/**
	 * 
	 * @param $oid
	 * @return unknown_type
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
	            $row = JTable::getInstance('ProductComments', 'TiendaTable');
	            $row->load( $oid );
	            $product_id = $row->product_id;
	        }
	        else
	        {
	            $product_id = $this->product_id;
	        }
	    }
		
		if ( parent::delete( $oid ) )
		{
		    DSCModel::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_tienda/models' );
		    $model = DSCModel::getInstance( 'ProductCommentsHelpfulness', 'TiendaModel' );
		    $model->setState('filter_comment', $this->$k );
		    if ($items = $model->getList())
		    {
		        $table = $model->getTable();
		        foreach ($items as $item)
		        {
		            if (!$table->delete( $item->productcommentshelpfulness_id ))
		            {
		                $this->setError( $table->getError() );
		            }
		        }
		    }
		    
		    if ($doReconciliation) 
		    {
		        $product = JTable::getInstance('Products', 'TiendaTable');
		        $product->load( $product_id );
		        $product->updateOverallRating();
		        if ( !$product->save() )
		        {
		            $this->setError( $product->getError() );
		        }
		    }
		}
		
		return parent::check();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DSCTable::save()
	 */
	function save($src='', $orderingFilter = '', $ignore = '')
	{
	    $isNew = false;
        if (empty($this->productcomment_id))
        {
            $isNew = true;
        }
        
        if ($save = parent::save($src, $orderingFilter, $ignore))
        {
            if ($this->productcomment_enabled && empty($this->rating_updated))
            {
                // get the product row
                $product = JTable::getInstance('Products', 'TiendaTable');
                $product->load( $this->product_id );
                
                $product->updateOverallRating();
                
                if (!$product->save())
                {
                    $this->setError( $product->getError() );
                }
                    else
                {
                    $this->rating_updated = '1';
                    parent::store();
                }
            }
                elseif (!$this->productcomment_enabled && !empty($this->rating_updated) )
            {
                // comment has been disabled after it already updated the overall rating
                // so remove it from the overall rating
                
                // get the product row
                $product = JTable::getInstance('Products', 'TiendaTable');
                $product->load( $this->product_id );
                
                $product->updateOverallRating();
                                                
                if (!$product->save())
                {
                    $this->setError( $product->getError() );
                }
                    else
                {
                    $this->rating_updated = '0';
                    parent::store();
                }
            }
        }
        
        return $save;
    }
}