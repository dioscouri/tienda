<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('stylesheet', 'pos.css', 'media/com_tienda/css/'); ?>
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