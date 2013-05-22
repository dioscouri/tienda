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

Tienda::load( 'TiendaViewBase', 'views._base', array( 'site'=>'site', 'type'=>'components', 'ext'=>'com_tienda' ) );
Tienda::load( "TiendaHelperProduct", 'helpers.product' );
Tienda::load( 'TiendaHelperCategory', 'helpers.category' );
Tienda::load( 'TiendaUrl', 'library.url' );

class TiendaViewProducts extends TiendaViewBase
{
    function __construct( $config=array() )
    {
        parent::__construct( $config );
    
        if (empty($this->helpers)) {
            $this->helpers = array();
        }
    
        Tienda::load( "TiendaHelperProduct", 'helpers.product' );
        $this->helpers['product'] = new TiendaHelperProduct();
    }
    
	/**
	 *
	 * @param $tpl
	 * @return unknown_type
	 */
	public function getLayoutVars($tpl=null)
	{
		$layout = $this->getLayout();
		switch(strtolower($layout))
		{
			case "view":
				$this->_form( $tpl );
				break;
			case "product_comments":
				$this->_default( $tpl, true );
				break;
			default:
			    $this->_default( $tpl );
				break;
		}
	}
}