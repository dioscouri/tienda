<?php
	defined('_JEXEC') or die('Restricted access');
	JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
	JHTML::_('stylesheet', 'pos.css', 'media/com_tienda/css/');
	$state = @$this->state;
	$row = @$this->product;
	Tienda::load('TiendaHelperProduct', 'helpers.product');
?>
<form action="index.php?option=com_tienda&view=pos&tmpl=component" method="post" name="adminForm" enctype="multipart/form-data">
    
    <table class="table table-striped table-bordered">
    	<tr>
    		<td>
    			<?php echo TiendaHelperProduct::getImage($row -> product_id, 'id', $row -> product_name, 'full', false, false, array('width' => 150));?>
    		</td>
    		<td>
    			<h3><?php echo $row->product_name; ?></h3>                    
                <p><?php echo $row->product_description_short; ?></p>
            </td>
            <td>
	           	<div id="product_buy">
          			<?php 
          			$values = array('user_id' => JFactory::getSession()->get('user_id', '', 'tienda_pos') );
          			echo TiendaHelperProduct::getCartButton( $row->product_id, 'viewproduct_addtocart', $values );
          			?>
               	</div>
            </td>
        </tr>
    	<tr >
    		<td colspan="3">
    			<div style="text-align: left;">
    				<a href="index.php?option=com_tienda&view=pos&task=addproducts&tmpl=component">
        				<?php echo JText::_('COM_TIENDA_RETURN_TO_SEARCH_RESULTS'); ?>
        			</a>
    			</div>
    		</td>
    	</tr>
    </table>
</form>