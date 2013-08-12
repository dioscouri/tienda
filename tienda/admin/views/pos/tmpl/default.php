<?php
	defined('_JEXEC') or die('Restricted access');
	JHTML::_('script', 'class.js', 'media/com_tienda/js/');
	JHTML::_('script', 'validation.js', 'media/com_tienda/js/');
	JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
	JHTML::_('script', 'pos.js', 'media/com_tienda/js/');
	JHTML::_('stylesheet', 'pos.css', 'media/com_tienda/css/');
	$state = @$this->state;
	$row = @$this->row;	
?>
<!-------- MOVE $this->loadTemplate outside the form the avoid having a form within a form since we already called the payment plugin form-------->
<form action="<?php echo JRoute::_( "index.php?option=com_tienda&view=pos" )?>" method="post" id="pos-form-<?php echo $this->step; echo isset($this->subtask) ? '-'.$this->subtask :''; ?>" <?php echo isset($this->subtask) ? 'data-subtask="'.$this->subtask.'"' :''; ?>  name="adminForm" enctype="multipart/form-data">
    <div class="pos">
        <?php echo $this->loadTemplate( $this->step ); ?>
    
        <input type="hidden" name="task" id="task" value="" />
        <input type="hidden" name="step" id="step" value="<?php echo $this->step; ?>" />
    </div>
</form>
