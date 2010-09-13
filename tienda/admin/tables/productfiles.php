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

class TiendaTableProductFiles extends TiendaTable 
{
	function TiendaTableProductFiles ( &$db ) 
	{
		
		$tbl_key 	= 'productfile_id';
		$tbl_suffix = 'productfiles';
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
			$this->setError( JText::_( "Product Association Required" ) );
			return false;
		}
		
        if (empty($this->productfile_name))
        {
            $this->setError( JText::_( "File Name Required" ) );
            return false;
        }

	    $nullDate   = $this->_db->getNullDate();
        if (empty($this->created_date) || $this->created_date == $nullDate)
        {
            $date = JFactory::getDate();
            $this->created_date = $date->toMysql();
        }
        $date = JFactory::getDate();
        $this->modified_date = $date->toMysql();
        
		return true;
	}
	
	/**
	 * Adds context to the default reorder method
	 * @return unknown_type
	 */
    function reorder()
    {
        parent::reorder('product_id = '.$this->_db->Quote($this->product_id) );
    }
    
    /**
     * Determines if a user can download the file
     * only using datetime if it is present
     *  
     * @param unknown_type $user_id
     * @param unknown_type $datetime
     * @return unknown_type
     */
    function canDownload( $user_id, $datetime=null )
    {
        // if the user is super admin, yes
        $user = JFactory::getUser( $user_id );
        if ($user->gid == '25') { return true; }
            
        // if the product file doesn't require purchase
        if (empty($this->purchase_required))
        {
            return true;
        }
 // or because they have purchased it and the num_downloads is < max (or max == -1)
//        $productdownloads = JTable::getInstance( 'ProductDownloads', 'TiendaTable' );
//        $productdownloads->load( array( 'productfile_id'=>$this->productfile_id, 'user_id'=>$user_id) );
        
//        no need of logs 
//        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
//        $model = JModel::getInstance( 'ProductDownloadLogs', 'TiendaModel' );
//        $model->setState('filter_productfile', $this->productfile_id);
//        $model->setState('filter_user', $user_id);
//        $items = $model->getList();
//        $num_downloads = count( $items );
        
//        if (!empty($productdownloads->productdownload_id) && ( $productdownloads->productdownload_max == '-1' || $num_downloads < $productdownloads->productdownload_max) )
//        {
//            return true;
//        }

        // Check the is it unlimited numbers of downloads 
         $productdownloads = JTable::getInstance( 'ProductDownloads', 'TiendaTable' );
	     $productdownloads->load( array( 'productfile_id'=>$this->productfile_id, 'user_id'=>$user_id) );
         
         if ( $productdownloads->productdownload_max == '-1')
        {
            return true;
        }
        
        // otherwise no
        return false;
    }
    
    /**
     * Logs a download
     * 
     * @param $user_id
     * @return unknown_type
     */
    function logDownload( $user_id )
    {
        $downloadlog = JTable::getInstance( 'ProductDownloadLogs', 'TiendaTable' );
        $downloadlog->user_id = $user_id;
        $downloadlog->productfile_id = $this->productfile_id;
        $downloadlog->save();    
    }
	
}
