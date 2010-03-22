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
defined('_JEXEC') or die('Restricted access');

JLoader::import( 'com_tienda.library.file', JPATH_ADMINISTRATOR .DS. 'components' );

class TiendaImage extends TiendaFile 
{
	var $image;
	var $type;
	
	function TiendaImage($filename = "") 
	{
		parent::__construct();
		
		if(!empty($filename))
		{
			if ( !JFile::exists($filename))
			{
				$this->setError("Image does not exist");
				return;	
			}
			
			$this->full_path = $filename;
			$this->setDirectory( substr( $this->full_path, 0, strrpos($this->full_path, DS) ) );
			$this->proper_name = JFile::getName($filename);
		}
	}

    /**
     * Prepares the storage directory
     * We override the parent::setDirectory()
     * because images dont need htaccess
     * 
     * @param mixed Boolean
     * @param mixed Boolean
     * @return array
     */
    function setDirectory( $dir=null ) 
    {
        $success = false;

        // checks to confirm existence of directory
        // then confirms directory is writeable     
        if ($dir === null)
        {
            $dir = $this->getDirectory();   
        }       
        
        $helper = TiendaHelperBase::getInstance();
        $helper->checkDirectory($dir);
        $this->_directory = $dir;
        return $this->_directory;
    }
	
	/**
	 * Load the image!
	 */
	function load()
	{
		$filename = $this->full_path;
		$image_info = getimagesize($filename);
      	$this->type = $image_info[2];
		
		if( $this->type == IMAGETYPE_JPEG ) {
			$this->image = imagecreatefromjpeg($filename);
		} elseif( $this->type == IMAGETYPE_GIF ) {
			$this->image = imagecreatefromgif($filename);
		} elseif( $this->type == IMAGETYPE_PNG ) {
			$this->image = imagecreatefrompng($filename);
		}
	}
	
	/**
	 * Save the image and chmods
	 * @param $filename
	 * @param $image_type image type: png, gif, jpeg
	 * @param $compression
	 * @param $permissions
	 */
	function save($filename, $image_type = 'jpg', $compression=75, $permissions=null) 
	{		
		if( $image_type == 'jpg' ) {
			imagejpeg($this->image,$filename,$compression);
		} elseif( $image_type == 'gif' ) {
			imagegif($this->image,$filename);
		} elseif( $image_type == 'png' ) {
			imagepng($this->image,$filename);
		}
		
		if( $permissions != null) {
			chmod($filename,$permissions);
		}
		unset($this->image);
	}
	
	/**
	 * Get the image width
	 */
	function getWidth() {
		return imagesx($this->image);
	}
	
	/**
	 * Get the image height
	 */
	function getHeight() {
		return imagesy($this->image);
	}
	
	/**
	 * Resize the image to a defined height
	 * @param $height
	 */
	function resizeToHeight($height) {
		$ratio = $height / $this->getHeight();
		$width = $this->getWidth() * $ratio;
		$this->resize($width,$height);
	}
	
	/**
	 * Resize the image to a defined width
	 * @param $width
	 */
	function resizeToWidth($width) {
		$ratio = $width / $this->getWidth();
		$height = $this->getheight() * $ratio;
		$this->resize($width,$height);
	}
	
	/**
	 * Scale the image to the defined proportion in %
	 * @param unknown_type $scale
	 */
	function scale($scale) {
		$width = $this->getWidth() * $scale/100;
		$height = $this->getheight() * $scale/100;
		$this->resize($width,$height);
	}
	
	/**
	 * Resize the image
	 * @param $width
	 * @param $height
	 */
	function resize($width,$height) {
		$new_image = imagecreatetruecolor($width, $height);
		imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
		$this->image = $new_image;
	}
}
?>