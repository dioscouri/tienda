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
		$this->registerTask( 'change_subnum', 'change_subnum' );
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
		if( TiendaConfig::getInstance()->get( 'display_subnum', 0 ) )
		$state['filter_subnum']       = $app->getUserStateFromRequest($ns.'filter_subnum', 'filter_subnum', '', '');

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
		$orderstates_csv = TiendaConfig::getInstance()->get('orderstates_csv', '2, 3, 5, 17');
		$orderstates_array=explode(',', $orderstates_csv);

		//Get Data From OrdersItems Model
		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$modelOrders= JModel::getInstance( 'Orders', 'TiendaModel');
		$modelOrders->setState( 'filter_userid',  $row->id );
		$modelOrders->setState( 'order', 'tbl.created_date' );
		$modelOrders->setState( 'direction', 'DESC' );
		$modelOrders->setState( 'filter_orderstates',  $orderstates_array);
		$allorders = $modelOrders->getList();
		$modelOrders->setState( 'limit', '5');
		$lastfiveorders = $modelOrders->getList( true );
		$view->assign( 'orders', $lastfiveorders );

		$spent = 0;
		foreach ($allorders as $orderitem)
		{
			$spent += $orderitem->order_total;
		}
		$view->assign( 'spent', $spent );

		

		//Get Data From Carts Model
		$modelCarts = JModel::getInstance( 'Carts', 'TiendaModel' );
		$modelCarts->setState( 'filter_user', $row->id );
		$carts = $modelCarts->getList();
		$view->assign( 'carts', $carts );
		foreach (@$carts as $cart)
		{
			$cart->total_price=$cart->product_price *$cart->product_qty;
		}
		
	    



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

	function change_subnum()
	{
		$sub_num  = JRequest::getInt( 'sub_number', 0 );
		$model = JModel::getInstance( 'Users', 'TiendaModel' );
		$id = $model->getId();
		$url = JRoute::_( 'index.php?option=com_tienda&controller=users&view=users&task=view&id='.$id, false );
		
		$db = JFactory::getDbo();
		$q = 'SELECT `user_info_id` FROM `#__tienda_userinfo` WHERE `user_id` <> '.$id.' AND `sub_number` = '.$sub_num;
		$db->setQuery( $q );
		$res = $db->loadResult();
		if( $res !== null )
		{
			$this->setRedirect( $url, JText::_( 'Couldnt change sub number' ), 'error' );
			return;
		}
		$q = 'UPDATE `#__tienda_userinfo` SET `sub_number` = '.$sub_num.' WHERE `user_id` = '.$id;
		$db->setQuery( $q );
		$db->query( $q );
		if( $db->getAffectedRows() == 1 )
			$this->setRedirect( $url, JText::_( 'Sub number changed' ) );
		else
			$this->setRedirect( $url, JText::_( 'No Sub number changed' ), 'notice' );
		return;
	}
}

?>