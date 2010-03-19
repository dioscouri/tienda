<?php
/**
 * @version 1.5
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

JLoader::import( 'com_tienda.helpers._base', JPATH_ADMINISTRATOR.DS.'components' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class TiendaHelperProductDownload extends TiendaHelperBase
{
    /**
     * Given a productfile or an array of productfiles
     * will filter productfiles if the user cannot download them 
     *  
     * @param mixed $items
     * @param int $user_id
     * @return array
     */
    function filterRestricted( $productfiles, $user_id )
    {
        (array) $productfiles;
        $filtered = array();
        
        foreach ($productfiles as $productfile)
        {
            if (TiendaHelperProductDownload::canDownload( $productfile->productfile_id, $user_id))
            {
                $filtered[] = $productfile;
            }
        }
        return $filtered;
    }
    
    /**
     * Given a productfile_id and user_id
     * determines if user can download file
     *  
     * @param $productfile_id
     * @param $user_id
     * @return boolean
     */
    function canDownload( $productfile_id, $user_id, $datetime=null )
    {
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $productfile = JTable::getInstance( 'ProductFiles', 'TiendaTable' );
        $productfile->load( $productfile_id );
        if ($productfile->canDownload( $user_id, $datetime ))
        {
            return true;
        }
        return false;
    }
}