<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda_product_comments.js', 'media/com_tienda/js/'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row;
JFilterOutput::objectHTMLSafe( $row );
Tienda::load( 'TiendaHelperManufacturer', 'helpers.manufacturer' );
?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >

	<fieldset>
		<legend><?php echo JText::_('Form'); ?></legend>
			<table class="admintable" style="width: 100%">
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<?php echo JText::_( 'Product Name' ); ?>:
					</td>
					<td>
						 <?php echo $this->elementArticle_product; ?>
                         <?php echo $this->resetArticle_product; ?> 
					</td>
				</tr>
				
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<?php echo JText::_( 'User Name' ); ?>:
					</td>
					<td>
						 <?php echo $this->elementUser_product; ?>
                         <?php echo $this->resetUser_product; ?> 
					</td>
				</tr>
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<?php echo JText::_( 'Comment' ); ?>:
					</td>
					<td>
						<textarea name="productcomment_text" id="productcomment_text" style="width: 100%;" rows="10"><?php echo @$row->	productcomment_text; ?></textarea>
					</td>
				</tr>
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<?php echo JText::_( 'User Rating' ); ?>:
					</td>
					<td>
					<?php
					$rate=@$row->productcomment_rating; 
					
					for($count=1;$count<=5;$count++){
					if($count<=$rate){?>
                            <span id="rating_<?php echo $count; ?>">
                            <img id="rate_<?php echo $count; ?>" src="../media/com_tienda/images/star_10.png">
                            </span>
                          <?php    }
                      else{?>
                     	 <span id="rating_<?php echo $count; ?>">
                            <img id="rate_<?php echo $count; ?>" src="../media/com_tienda/images/star_00.png">
                         </span>
                             
                    <?php } 
                      }?>
						
						<input type="hidden" id="productcomment_rating" name="productcomment_rating" value="<?php echo @$row->productcomment_rating; ?>" size="10" />
					</td>
				</tr>
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<?php echo JText::_( 'Published' ); ?>:
					</td>
					<td>
							<?php echo JHTML::_('select.booleanlist', 'productcomment_enabled', '', @$row->productcomment_enabled ); ?>
					</td>
				</tr>
			</table>
			<input type="hidden" name="id" value="<?php echo @$row->manufacturer_id; ?>" />
			<input type="hidden" name="task" value="" />
	</fieldset>
</form>
