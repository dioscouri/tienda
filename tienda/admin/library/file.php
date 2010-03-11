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

jimport('joomla.filesystem.file');
JLoader::import( 'com_tienda.helpers._base', JPATH_ADMINISTRATOR.DS.'components' );

class TiendaFile extends JObject 
{
	
	
	/**
	 * Returns a list of types
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

		// then confirms existence of htaccess file
		//		$htaccess = $dir.DS.'.htaccess';
		//		if (!$fileexists = &JFile::exists( $htaccess ) ) {
		//
		//			$destination = $htaccess;
		//			$text = "deny from all";
		//			
		//			if ($f = @fopen($destination,'wb')) {
		//				fwrite ( $f, $text );
		//				fclose($f);
		//			}
		//
		//		}

		$this->_directory = $dir;
		return $this->_directory;
	}

	/**
	 * 
	 * @return unknown_type
	 */
	function getDirectory( $media='images' )
	{
		if (!isset($this->_directory)) 
		{
			$this->_directory = Tienda::getPath( $media );
		}
		return $this->_directory;
	}
		
	/**
	 * Returns 
	 * @param mixed Boolean
	 * @param mixed Boolean
	 * @return object
	 */
	function handleUpload ($fieldname='userfile') 
	{
		$success = false;
		$config = &TiendaConfig::getInstance();
		
		// Check if file uploads are enabled
		if (!(bool)ini_get('file_uploads')) {
			$this->setError( JText::_( 'Uploads Disabled' ) );
			return $success;
		}
	
		// Check that the zlib is available
		if(!extension_loaded('zlib')) {
			$this->setError( JText::_( 'ZLib Unavailable' ) );
			return $success;
		}

		// check that upload exists
		$userfile = JRequest::getVar( $fieldname, '', 'files', 'array' );
		
		if (!$userfile) 
		{
			$this->setError( JText::_( 'No File' ) );
			return $success;
		}
		
		if($num == -1)
			$this->proper_name = basename($userfile['name']);
		else
			$this->proper_name = basename($userfile['name'][$num]);
		
		if ($userfile['size'] == 0) {
			$this->setError( JText::_( 'Invalid File' ) );
			return $success;
		}
		
		$this->size = $userfile['size']/1024;		
		// check size of upload against max set in config
		if($this->size > $config->get( 'files_maxsize', '3000' ) ) 
		{
			$this->setError( JText::_( 'Invalid File Size' ) );
			return $success;
	    }
	    $this->size = number_format( $this->size, 2 ).' Kb';
		
		if (!is_uploaded_file($userfile['tmp_name'])) 
		{
			$this->setError( JText::_( 'Invalid File' ) );
			return $success;
	    } 
	    	else 
	    {
	    	$this->file_path = $userfile['tmp_name'];
		}
		
		$this->getExtension();
		$this->uploaded = true;
		$success = true;		
		return $success;
	}
	
/**
	 * Returns 
	 * @param mixed Boolean
	 * @param mixed Boolean
	 * @return object
	 */
	function handleMultipleUpload ($fieldname='userfile', $num = 0) 
	{
		$success = false;
		$config = &TiendaConfig::getInstance();
		
		// Check if file uploads are enabled
		if (!(bool)ini_get('file_uploads')) {
			$this->setError( JText::_( 'Uploads Disabled' ) );
			return $success;
		}
	
		// Check that the zlib is available
		if(!extension_loaded('zlib')) {
			$this->setError( JText::_( 'ZLib Unavailable' ) );
			return $success;
		}

		// check that upload exists
		$userfile = JRequest::getVar( $fieldname, '', 'files', 'array' );
		
		if (!$userfile) 
		{
			$this->setError( JText::_( 'No File' ) );
			return $success;
		}
		
		$this->proper_name = basename($userfile['name'][$num]);
		
		if ($userfile['size'][$num] == 0) {
			$this->setError( JText::_( 'Invalid File' ) );
			return $success;
		}
		
		$this->size = $userfile['size'][$num]/1024;		
		// check size of upload against max set in config
		if($this->size > $config->get( 'files_maxsize', '3000' ) ) 
		{
			$this->setError( JText::_( 'Invalid File Size' ) );
			return $success;
	    }
	    $this->size = number_format( $this->size, 2 ).' Kb';
		
		if (!is_uploaded_file($userfile['tmp_name'][$num])) 
		{
			$this->setError( JText::_( 'Invalid File' ) );
			return $success;
	    } 
	    	else 
	    {
	    	$this->file_path = $userfile['tmp_name'][$num];
		}
		
		$this->getExtension();
		$this->uploaded = true;
		$success = true;		
		
		return $success;
	}
	
	/**
	 * Do the real upload
	 */
	function upload(){
		// path
		$dest = $this->getDirectory().DS.$this->getPhysicalName();
		// delete the file if dest exists
		if ($fileexists = JFile::exists( $dest ))
		{
			JFile::delete($dest);
		}
		// save path and filename or just filename
		if (!JFile::upload($this->file_path, $dest))
		{
        	$this->setError( sprintf( JText::_("Move failed from"), $this->file_path, $dest) );
        	return false;			
		}
		
		$this->full_path = $dest;
		return true;
	}

	/**
	 * Returns a list of types
	 * @param mixed Boolean
	 * @param mixed Boolean
	 * @return array
	 */
	function getExtension() 
	{
		if (!isset($this->extension)) 
		{
			$namebits = explode('.', $this->proper_name);
			$this->extension = $namebits[count($namebits)-1];
		}
				
		return $this->extension;
	}

	/**
	 * Returns a unique physical name
	 * @param mixed Boolean
	 * @param mixed Boolean
	 * @return array
	 */
	function getPhysicalName( $obfuscate='' )
	{
		if (!empty($this->physicalname))
		{
			return $this->physicalname;
		}
		
		if ($obfuscate)
		{
			$dir = $this->getDirectory();
			$extension = $this->getExtension();
			$name = JUtility::getHash( time() );
			$physicalname = $name.".".$extension;
			
			while ($fileexists = &JFile::exists( $dir.DS.$physicalname ) ) 
			{
				$name = JUtility::getHash( time() );
				$physicalname = $name.".".$extension;
			}
			$this->physicalname = $physicalname;
		}
			else
		{
			$name = explode('.', $this->proper_name);
			$this->physicalname = $this->cleanTitle( $name[0] ).'.'.$this->getExtension();
		}
				
		return $this->physicalname;
	}
	
	/**
	 * Returns a cleaned title
	 * @param mixed Boolean
	 * @param mixed Boolean
	 * @return array
	 */
	function cleanTitle( $title, $length='64' ) 
	{
		// trim whitespace
		$trim_title = strtolower( trim( str_replace( " ", "", $title ) ) );
		
		// strip all html tags
		$wc = strip_tags($trim_title);
		
		// remove 'words' that don't consist of alphanumerical characters or punctuation
		$pattern = "#[^(\w|\d|\'|\"|\.|\!|\?|;|,|\\|\/|\-|:|\&|@)]+#";
		$wc = trim(preg_replace($pattern, "", $wc));
		
		// remove one-letter 'words' that consist only of punctuation
		$wc = trim(preg_replace("#\s*[(\'|\"|\.|\!|\?|;|,|\\|\/|\-|:|\&|@)]\s*#", "", $wc));
		
		// remove superfluous whitespace
		$wc = preg_replace("/\s\s+/", "", $wc);		
		
		// cut title to length
		$cut_title = substr($wc, 0, $length);
		
		$data = $cut_title;
		
		return $data;
	}

	/**
	 * Returns 
	 * @param mixed Boolean
	 * @param mixed Boolean
	 * @return array
	 */
	function fileToText () 
	{
		$database = &JFactory::getDBO();
		$source = $this->file_path;
		$success = false;
		
		if ($f = @fopen($source,'rb')) 
		{
			$sql = "";
			while($f && !feof($f)) 
			{
				$chunk = fread($f, 65535);
				$sql .= $chunk;
			}
			fclose($f);
			$this->fileastext = $sql;
			$success = true;
			return $success;
		} 
			else 
		{
			return $success;
		}
	}

	/**
	 * Returns 
	 * @param mixed Boolean
	 * @param mixed Boolean
	 * @return array
	 */
	function fileToBlob () 
	{
		$database = &JFactory::getDBO();
		$source = $this->file_path;
		$success = false;
		
		if ($f = @fopen($source,'rb')) 
		{
			$sql = "";
			while($f && !feof($f)) 
			{
				$chunk = fread($f, 65535);
				$sql .= $chunk;
			}
			fclose($f);
			$this->fileasblob = $sql;
			$this->fileisblob = '1';
			$success = true;
			return $success;
		} else {
			return $success;
		}
	}
		
	/**
	 * Returns
	 * @param mixed Boolean
	 * @param mixed Boolean
	 * @return array
	 */
	function textToFile ( $file, $temppath='' ) 
	{
		global $mainframe;
		$result = false;
		if (!$temppath || !is_writable($temppath)) 
		{
			$temppath = $mainframe->getCfg( 'config.tmp_path' );
		}
		
		$destination = $temppath.DS.$file->filename;
		
		if ($f = @fopen($destination,'wb')) {
			if ($file->filetext AND fwrite ( $f, $file->filetext )) { $result = $destination; }
			fclose($f);
		}
		
		return $result;
	}
	
	/**
	 * Returns
	 * @param mixed Boolean
	 * @param mixed Boolean
	 * @return array
	 */
	function blobToFile ( $file, $temppath='' ) 
	{
		global $mainframe;
		$result = false;
		if (!$temppath || !is_writable($temppath)) 
		{
			$temppath = $mainframe->getCfg( 'config.tmp_path' );
		}
		
		$destination = $temppath.DS.$file->filename;
		
		if ($f = @fopen($destination,'wb')) 
		{
			if ($file->fileblob AND fwrite ( $f, $file->fileblob )) { $result = $destination; }
			fclose($f);
		}
		
		return $result;
	}	

	/**
	 * Retrieves the values
	 * @return array Array of objects containing the data from the database
	 */
	function getStorageMethods() 
	{
		static $instance;
		
		if (!is_array($instance)) {
			$instance = array();
				$instance_file = new stdClass();
				$instance_file->id = 1;
				$instance_file->title = 'File';
			$instance[0] = $instance_file;
				$instance_blob = new stdClass();
				$instance_blob->id = 2;
				$instance_blob->title = 'Blob';
			$instance[1] = $instance_blob;

		}

		return $instance;
	}
	
	/**
	 * Creates a List
	 * @return array Array of objects containing the data from the database
	 */
	function getArrayListStorageMethods() 
	{
		static $instance;
		
		if (!is_array($instance)) {
			$instance = array();
			$data = &TiendaFile::getStorageMethods();
			for ($i=0; $i<count($data); $i++) {
				$d = $data[$i];
		  		$instance[] = JHTML::_('select.option', $d->id, JText::_( $d->title ) );
			}
		}

		return $instance;

	}
	
	/**
	 * Get Full file Path
	 */
	function getFullPath(){
		return $this->full_path;
	}
	
}

?>
