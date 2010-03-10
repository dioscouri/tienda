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

/** Import library dependencies */
jimport('joomla.event.plugin');

class plgSearchTienda extends JPlugin 
{   
    function plgSearchTienda(& $subject, $config)
    {
        parent::__construct($subject, $config);
    }
    
    /**
     * Checks the extension is installed
     * 
     * @return boolean
     */
    function _isInstalled()
    {
        $success = false;
        
        jimport('joomla.filesystem.file');
        if (JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'defines.php')) 
        {
            require_once JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'defines.php';
            $success = true;
        }
        return $success;
    }
    
    /**
     * Tells the seach component what extentions are being searched
     * 
     * @return unknown_type
     */
    function onSearchAreas()
    {
        if (!$this->_isInstalled())
        {
            // TODO Find out if this should return null or array
            return array();
        }
        
        $areas = 
            array(
                'tienda' => 'Tienda'
            );
        return $areas;
    }
    
    /**
     * Performs the search
     * 
     * @param string $keyword
     * @param string $match
     * @param unknown_type $ordering
     * @param unknown_type $areas
     * @return unknown_type
     */    
    function onSearch( $keyword, $match='', $ordering='', $areas=null )
    {
        if (!$this->_isInstalled())
        {
            return array();
        }
        
        if ( is_array( $areas ) ) 
        {
            if ( !array_intersect( $areas, array_keys( $this->onSearch() ) ) ) 
            {
                return array();
            }
        }
        
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'Products', 'TiendaModel' );
        $match = strtolower($match);
        switch ($match)
        {
            case 'exact':
                $model->setState('filter', $match);
            case 'all':
            case 'any':
            default:
                $words = explode( ' ', $keyword );
                $wheres = array();
                foreach ($words as $word)
                {
                    $model->setState('filter', $word);
                }
                break;
        }
        
        // order the items according to the ordering selected in com_search
        switch ( $ordering ) 
        {
            case 'newest':
                $model->setState('order', 'tbl.created_date');
                $model->setState('direction', 'DESC');
                break;
            case 'oldest':
                $model->setState('order', 'tbl.created_date');
                $model->setState('direction', 'ASC');
                break;
            case 'alpha':
            case 'popular':
            default:
                $model->setState('order', 'tbl.product_name');
                break;
        }

        $items = $model->getList();
        if (empty($items)) { return array(); }
 
        // format the items array according to what com_search expects
        foreach ($items as $key => $item)
        {
            $item->href         = "index.php?option=com_tienda&controller=products&view=products&task=view&id=".$item->product_id;
            $item->title        = JText::_( $item->product_name );
            $item->created      = $item->created_date;
            $item->section      = JText::_( "Tienda" );
            $item->text         = substr( $item->product_description, 0, 250);
            $item->browsernav   = "1";                
        }

        return $items;
    }
}
?>