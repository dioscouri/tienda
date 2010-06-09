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

Tienda::load( 'TiendaPluginBase', 'library.plugins._base' );

class plgTiendaRelatedProducts extends TiendaPluginBase
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'relatedproducts';
    
	function plgTiendaRelatedProducts(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}
	
    /**
     * Override parent::_getData() to insert groupBy and orderBy clauses into query
     *  
     * @return unknown_type
     */
    function onAfterDisplayProductFormRightColumn( $product )
    {
        echo $this->_renderForm();
        return null;
    }
    
    function onAfterSaveProducts( $product )
    {
        echo JRequest::getVar( 'related_products' );
        echo "<br/>We're in the save method";
        exit;
    }
}
