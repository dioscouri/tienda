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

class TiendaControllerProductFiles extends TiendaController 
{
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
		
		$this->set('suffix', 'productfiles');
	}

	/**
	 * downloads a file
	 *
	 * @return void
	 */
	function downloadFile( )
	{
		$user = JFactory::getUser( );
		$productfile_id = intval( JRequest::getvar( 'id', '', 'request', 'int' ) );
		$product_id = intval( JRequest::getvar( 'product_id', '', 'request', 'int' ) );
		$link = 'index.php?option=com_tienda&view=products&task=edit&id=' . $product_id;
		
		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance( 'ProductDownload', 'TiendaHelper' );
		
		JTable::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables' );
		$productfile = JTable::getInstance( 'ProductFiles', 'TiendaTable' );
		$productfile->load( $productfile_id );
		if ( empty( $productfile->productfile_id ) )
		{
			$this->messagetype = 'notice';
			$this->message = JText::_('COM_TIENDA_INVALID FILE');
			$this->setRedirect( $link, $this->message, $this->messagetype );
			return false;
		}
		
		// log and download
		Tienda::load( 'TiendaFile', 'library.file' );
		
		// geting the ProductDownloadId to updated for which productdownload_max  is greater then 0
		$productToDownload = $helper->getProductDownloadInfo( $productfile->productfile_id, $user->id );
		
		if ( $downloadFile = TiendaFile::download( $productfile ) )
		{
			$link = JRoute::_( $link, false );
			$this->setRedirect( $link );
		}
	}
}

?>