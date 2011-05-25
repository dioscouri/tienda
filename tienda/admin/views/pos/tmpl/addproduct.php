<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('stylesheet', 'pos.css', 'media/com_tienda/css/'); ?>
<?php JHTML::_('stylesheet', 'component.css', 'media/com_tienda/css/'); ?>
<?php $state = @$this->state; ?>
<?php $row = @$this->row; ?>

<form action="index.php?option=com_tienda&view=pos&tmpl=component" method="post" name="adminForm" enctype="multipart/form-data">
    <div class="pos">
        <?php echo $this->loadTemplate( 'search' ); ?>
        <br/>
        <?php echo $this->loadTemplate( 'results' ); ?>
    
        <input type="hidden" name="task" id="task" value="addproducts" />
    </div>
</form>
<?php $added=JRequest::getInt('added', '0')?>
<?php if($added):?>
<script type="text/javascript">
	window.onload = window.top.document.location.reload(true);
</script>
<?php endif;?>