<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; ?>

<form action="<?php echo JRoute::_( 'index.php?option=com_tienda&view=reports&layout=view' ) ?>" method="post" class="adminform" id="adminform" name="adminForm" >

	<?php
		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger( 'onGetReportView', array( $row ) );

        for ($i=0; $i<count($results); $i++) 
        {
            $result = $results[$i];
            echo $result;
        }
	?>
	
	<?php
	    echo $form['validate'];
	?>   
	<input type="hidden" name="id" value="<?php echo @$row->id; ?>" />
	<input type="hidden" name="task" id="task" value="" />
	
</form>