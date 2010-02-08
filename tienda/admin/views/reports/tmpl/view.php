<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; ?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" >

	<h3>
	    <?php echo @$row->name ?>
	</h3>
	
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