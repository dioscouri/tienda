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

if ($num > 0 && $products)
{
    
    foreach (@$products as $product)
    {
        ?>
        <p>
        <b><?php echo $product->product_name; ?></b><br />
        <?php echo $product->product_description ?><br />
        <?php echo TiendaHelperBase::currency($product->product_price) ?>
        </p>
        <?php
    } 

    
}  
    elseif ($display_null == '1') 
{
    $text = JText::_( $null_text );
    echo $text;
}