<?php
/**
 * @version    1.5
 * @package    Tienda
 * @author     Dioscouri Design
 * @link     http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.application.component.model' );

class modTiendaCartHelper
{
    function getCart()
    {
        Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        JModel::addIncludePath( JPATH_SITE.DS.'components'.DS.'com_tienda'.DS.'models' );

        // determine whether we're working with a session or db cart
        $suffix = TiendaHelperCarts::getSuffix();
    	$model = JModel::getInstance( 'Carts', 'TiendaModel' );
    	
        $session = JFactory::getSession();
        $user = JFactory::getUser();
        
        $model->setState('filter_user', $user->id );
        if (empty($user->id))
        {
            $model->setState('filter_session', $session->getId() );
        }
    	
    	$list = $model->getList( false, false );
    	
    	Tienda::load( 'Tienda', 'defines' );
        $config = Tienda::getInstance();
        $show_tax = $config->get('display_prices_with_tax');
        $this->using_default_geozone = false;
        
        if ($show_tax)
        {
            Tienda::load('TiendaHelperUser', 'helpers.user');
            $geozones = TiendaHelperUser::getGeoZones( JFactory::getUser()->id );
            if (empty($geozones))
            {
                // use the default
                $this->using_default_geozone = true;
                $table = JTable::getInstance('Geozones', 'TiendaTable');
                $table->load(array('geozone_id'=>Tienda::getInstance()->get('default_tax_geozone')));
                $geozones = array( $table );
            }
            
            Tienda::load( "TiendaHelperProduct", 'helpers.product' );
            foreach ($list as &$item)
            {
                $taxtotal = TiendaHelperProduct::getTaxTotal($item->product_id, $geozones);
                $item->product_price = $item->product_price + $taxtotal->tax_total;
                $item->taxtotal = $taxtotal;
            }
        }
    	
    	return $list;
    }
}
?>
