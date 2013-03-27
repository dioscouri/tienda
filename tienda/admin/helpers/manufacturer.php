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

Tienda::load( 'TiendaHelperBase', 'helpers._base' );
jimport('joomla.filesystem.file');

class TiendaHelperManufacturer extends TiendaHelperBase
{
	public static function getImage( $id, $by='id', $alt='', $type='thumb', $url=false )
	{
		switch($type)
		{
			case "full":
				$path = 'manufacturers_images';
			  break;
			case "thumb":
			default:
				$path = 'manufacturers_thumbs';
			  break;
		}
		
		$tmpl = "";
		if (strpos($id, '.'))
		{
			// then this is a filename, return the full img tag if file exists, otherwise use a default image
			$src = (JFile::exists( Tienda::getPath( $path ).DS.$id))
				? Tienda::getUrl( $path ).$id : 'media/com_tienda/images/noimage.png';
			
			// if url is true, just return the url of the file and not the whole img tag
			$tmpl = ($url)
				? $src : "<img src='".$src."' alt='".JText::_( $alt )."' title='".JText::_( $alt )."' name='".JText::_( $alt )."' align='center' border='0' >";

		}
			else
		{
			if (!empty($id))
			{
				// load the item, get the filename, create tmpl
				DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
				$row = DSCTable::getInstance('Manufacturers', 'TiendaTable');
				$row->load( (int) $id );
				$id = $row->manufacturer_image;

				$src = (JFile::exists( Tienda::getPath( $path ).DS.$row->manufacturer_image))
					? Tienda::getUrl( $path ).$id : 'media/com_tienda/images/noimage.png';

				// if url is true, just return the url of the file and not the whole img tag
				$tmpl = ($url)
					? $src : "<img src='".$src."' alt='".JText::_( $alt )."' title='".JText::_( $alt )."' name='".JText::_( $alt )."' align='center' border='0' >";
			}			
		}
		return $tmpl;
	}

	/**
	 * Method to calculate statistics about manufacturers in an order
	 * 
	 * @param $items Array of order items
	 * 
	 * @return	Array with list of manufacturers and their stats
	 */
	function calculateStatsOrder( $items )
	{
		$db = JFactory::getDbo();
		DSCModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/models' );
		Tienda::load( 'TiendaQuery' ,'library.query' );
		$q = new TiendaQuery();
		$q->select( 'manufacturer_id' );
		$q->from( '`#__tienda_products`' );
		
		$result = array();
		foreach( $items as $item )
		{
			$q->where( 'product_id = '.(int)$item->product_id );
			$db->setQuery( $q );
			$res = $db->loadObject();
			if( $res == null )
				$man_id = 0;
			else
				$man_id = $res->manufacturer_id;
			if( !isset( $result[ $man_id ] ) )
			{
				$model = DSCModel::getInstance( 'Manufacturers', 'TiendaModel' );
				$model->setId( $man_id );
				if (!$man_item = $model->getItem()) {
				    $man_item = new stdClass();
				}
				$result[ $man_id ] = $man_item;
				$result[ $man_id ]->subtotal = 0;
				$result[ $man_id ]->total_tax = 0;
			}
			$result[ $man_id ]->subtotal += $item->orderitem_final_price;
			$result[ $man_id ]->total_tax += $item->orderitem_tax;
		}
		return $result;
	}
}