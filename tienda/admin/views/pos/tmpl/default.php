<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('stylesheet', 'pos.css', 'media/com_tienda/css/'); ?>
<?php $state = @$this->state; ?>
<?php $row = @$this->row; ?>
<!-------- MOVE $this->loadTemplate outside the form the avoid having a form within a form since we already called the payment plugin form-------->
<?php if($this->step != 'step4'):?>
<form action="<?php echo JRoute::_( "index.php?option=com_tienda&view=pos" )?>" method="post" name="adminForm" enctype="multipart/form-data">
    <div class="pos">
        <?php echo $this->loadTemplate( $this->step ); ?>
    
        <input type="hidden" name="task" id="task" value="" />
        <input type="hidden" name="step" id="step" value="<?php echo $this->step; ?>" />
    </div>
</form>
<?php else:?>
	 <div class="pos">
        <?php echo $this->loadTemplate( $this->step ); ?>    
    </div>
<?php endif;?>
