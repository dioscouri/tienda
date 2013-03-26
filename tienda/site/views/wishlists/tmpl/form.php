<?php
	defined('_JEXEC') or die('Restricted access');

	$item = @$this->wishlist;
	$state = @$this->state;

?>

<div class='componentheading'>
    <span><?php echo JText::_('COM_TIENDA_MY_WISHLIST'); ?></span>
</div>

<form action="<?php echo $this->form['action']; ?>" method="post" class="wishlistform" name="wishlistform" id="wishlistform" enctype="multipart/form-data" >
	<input class="text" type="text" name="name">
	<?php //TODO  make a TIENDASELECT ?>
	<input type="radio" name="privacy" value="1">Public<br>
	<input type="radio" name="privacy" value="2" >Link Only<br>
	<input type="radio" name="privacy" value="3">Private
	<?php echo $this->form['validate']; ?>
	<button type="submit" class="btn btn-primary">Submit</button>
</form>
    