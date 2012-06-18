<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2010 Dioscouri. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );


class TiendaImage extends DSCImage
{
	var $image;
	var $type;
	var $is_archive = false;
	var $archive_files = array( );
	
	public $thumb_width = '160';
	public $thumb_height = '90';
	
	
	
	/**
	 * Support Zip files for image galleries 
	 * @see TiendaFile::upload()
	 */
	function upload( )
	{
		if ( $result = parent::upload( ) )
		{
			// Check if it's a supported archive
			$allowed_archives = array(
				'zip', 'tar', 'tgz', 'gz', 'gzip', 'tbz2', 'bz2', 'bzip2'
			);
			
			if ( in_array( strtolower( $this->getExtension( ) ), $allowed_archives ) )
			{
				$dir = $this->getDirectory( );
				jimport( 'joomla.filesystem.archive' );
				JArchive::extract( $this->full_path, $dir );
				JFile::delete($this->full_path);
				
				$this->is_archive = true;
				
				$files = JFolder::files( $dir );
				
				// Thumbnails support
				if ( count( $files ) )
				{
					// Name correction
					foreach ( $files as &$file )
					{
						$file = new TiendaImage( $dir . DS . $file);
					}
					
					$this->archive_files = $files;
					$this->physicalname = $files[0]->getPhysicalname( );
				}
			}
			
		}
		
		return $result;
	}
}
?>