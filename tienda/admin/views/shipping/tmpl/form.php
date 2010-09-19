<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; 
jimport('joomla.html.pane'); 
$tabs = &JPane::getInstance( 'tabs' ); 
?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" >
	<?php 
    // start tab pane
    echo $tabs->startPane( "Pane_Payment" );
    // Tab
    echo $tabs->startPanel( JText::_( 'Plugin Details' ), "plugin_properties");
    ?>
    <fieldset>
    <legend><?php echo JText::_( "Basic Information" ); ?></legend>
			<table class="admintable">
				<tr>
					<td width="100" align="right" class="key">
						<?php echo JText::_( 'Name' ); ?>:
					</td>
					<td>
						<input name="name" id="name" value="<?php echo @$row->name; ?>" size="48" maxlength="250" type="text" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<?php echo JText::_( 'Ordering' ); ?>:
					</td>
					<td>
						<input name="ordering" id="ordering" value="<?php echo @$row->ordering; ?>" size="10" maxlength="250" type="text" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="currency_enabled">
						<?php echo JText::_( 'Enabled' ); ?>:
						</label>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', 'published', '', @$row->published ) ?>
					</td>
				</tr>
			</table>
			</fieldset>
			<fieldset>
    		<legend><?php echo JText::_( "Parameters" ); ?></legend>
			<?php 
			if ($output = $this->params->render('params')) :
				echo $output;
				
			else :
				echo "<div style=\"text-align: center; padding: 5px; \">".JText::_('There are no parameters for this item')."</div>";
			endif;
			?>
			</fieldset>
			<?php 
	   	 	echo $tabs->endPanel();
			echo $tabs->endPane();
	
			?>
			<input type="hidden" name="id" value="<?php echo @$row->id; ?>" />
			<input type="hidden" name="task" value="" />
	
</form>