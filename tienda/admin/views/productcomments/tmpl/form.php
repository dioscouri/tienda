<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
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
						<?php echo $row->product_name; ?>
						 <?php //echo $this->elementArticle_product; ?>
                         <?php //echo $this->resetArticle_product; ?> 
					</td>
				</tr>
				
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<?php echo JText::_( 'User Name' ); ?>:
					</td>
					<td>
					<?php echo $row->user_name; ?>
						 <?php //echo $this->elementUser_product; ?>
                         <?php //echo $this->resetUser_product; ?> 
					</td>
				</tr>
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<?php echo JText::_( 'Comment' ); ?>:
					</td>
					<td>
					<?php echo @$row->productcomment_text; ?>
						<!--
							<textarea name="productcomment_text" id="productcomment_text" style="width: 100%;" rows="10"><?php echo @$row->productcomment_text; ?></textarea>
						 -->
					</td>
				</tr>
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<?php echo JText::_( 'User Rating' ); ?>:
					</td>
					<td>
                        <?php Tienda::load( 'TiendaHelperProduct', 'helpers.product' ); ?>
                        <?php echo TiendaHelperProduct::getRatingImage( $this, @$row->productcomment_rating ); ?>
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
