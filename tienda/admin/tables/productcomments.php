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
	function delete( $oid=null  )
	{
		$this->load( $oid );
		$product_id = $this->product_id;
		if( parent::delete( $oid ) )
		{
			$product = JTable::getInstance('Products', 'TiendaTable');
			$product->load( $product_id );
			$product->updateOverallRating();
			if ( !$product->save() )
			{
				$this->setError( $product->getError().'what?!' );
				return false;
			}
			return true;
		}
		$this->setError( $product->getError().'beh?' );
		
		return false;
	}
	
	function save()
	{
	    $isNew = false;
        if (empty($this->productcomment_id))
        {
            $isNew = true;
        }
        
        if ($save = parent::save())
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