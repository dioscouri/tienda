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

class TiendaControllerUsers extends TiendaController 
{
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
		
		$this->set('suffix', 'users');
	}
	
    /**
     * Sets the model's state
     * 
     * @return array()
     */
    function _setModelState()
    {
        $state = parent::_setModelState();      
        $app = JFactory::getApplication();
        $model = $this->getModel( $this->get('suffix') );
        $ns = $this->getNamespace();

        $state['filter_id_from']    = $app->getUserStateFromRequest($ns.'id_from', 'filter_id_from', '', '');
        $state['filter_id_to']      = $app->getUserStateFromRequest($ns.'id_to', 'filter_id_to', '', '');
        $state['filter_name']         = $app->getUserStateFromRequest($ns.'name', 'filter_name', '', '');
        $state['filter_username']         = $app->getUserStateFromRequest($ns.'username', 'filter_username', '', '');
        $state['filter_email']         = $app->getUserStateFromRequest($ns.'email', 'filter_email', '', '');
        $state['filter_group']         = $app->getUserStateFromRequest($ns.'filter_group', 'filter_group', '', '');
        
        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );   
        }
        return $state;
    }
    
	function view()
	{
		$model = $this->getModel( $this->get('suffix') );
		$model->getId();
		$row = $model->getItem();
		$view   = $this->getView( $this->get('suffix'), 'html' );
		$view->setModel( $model, true );
		$view->assign( 'row', $row );
		$view->setLayout( 'view' );
		
		
		//Get Data From OrdersItems Model
		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$modelOrders= JModel::getInstance( 'Orders', 'TiendaModel');
		$modelOrders->setState( 'filter_userid',  $row->id );		
		$modelOrders->setState( 'order', 'tbl.created_date' );
		$modelOrders->setState( 'direction', 'DESC' );
		//$modelOrders->setState( 'filter_orderstate',  array('2','3','5','17') );
		//$allorders = $modelOrders->getList();
		$modelOrders->setState( 'limit', '5');
		$lastfiveorders = $modelOrders->getList( true );
		$view->assign( 'orders', $lastfiveorders );
		
		//Get Data From Carts Model
		$modelCarts = JModel::getInstance( 'Carts', 'TiendaModel' );
		$modelCarts->setState( 'filter_user', $row->id );	
		$carts = $modelCarts->getList();
		$view->assign( 'carts', $carts );	
		 foreach (@$carts as $cart)
		 {
				$cart->total_price=$cart->product_price *$cart->product_qty;
		 }
		 
		 //Summary Data	
		$modelSum = JModel::getInstance( 'Orderitems', 'TiendaModel');
		$modelSum->setState( 'filter_userid',  $row->id );
		$modelSum->setState( 'filter_orderstate',  array('2','3','5','17') );	
		$orderitems = $modelSum->getList();		
		$spent = 0;
		$total_qty = 0;
		foreach ($orderitems as $orderitem)
		{
			$spent += $orderitem->orderitem_price;
			$total_qty +=$orderitem->orderitem_quantity;
		}
		$view->assign( 'spent', $spent );
		$view->assign('total_qty',$total_qty );
		
		//Subcription Data
		$modelSubs= JModel::getInstance( 'subscriptions', 'TiendaModel');
		$modelSubs->setState( 'filter_userid',  $row->id );	
		$modelSubs->setState( 'filter_enabled', 1 );
		$modelOrders->setState( 'limit', '5' );
		$subs= $modelSubs->getList();
		$view->assign( 'subs',$subs );		
		
		
		 //Get Data from Productcomments Model and left join to products
		$database = $model->getDbo();
		Tienda::load( 'TiendaQuery', 'library.query' );
        $query = new TiendaQuery();
        $query->select( 'tbl.*');
        $query->select( 'substring(tbl.productcomment_text, 1, 250) AS trimcom' );
        $query->from( '#__tienda_productcomments AS tbl' );
        $query->select('p.product_name AS p_name');
        $query->join('LEFT', '#__tienda_products AS p ON p.product_id = tbl.product_id');           
        $query->where("tbl.user_id='$row->id'");        
        $database->setQuery( (string) $query );
        $procoms = $database->loadObjectList();
		$view->assign( 'procoms', $procoms);
				
		$model->emptyState();
		$this->_setModelState();
		$surrounding = $model->getSurrounding( $model->getId() );
		$view->assign( 'surrounding', $surrounding );

		$view->display();
		$this->footer();
		return;
	}
}

?>