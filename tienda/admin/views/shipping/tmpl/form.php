<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; 
jimport('joomla.html.pane'); 
$tabs = JPane::getInstance( 'tabs' ); 
?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" >
<ul class="nav nav-tabs">
	<li class="active"><a href="#panel_details" data-toggle="tab"><?php echo JText::_('COM_TIENDA_PLUGIN_DETAILS'); ?></a></li>
	<li><a href="#panel_parameters" data-toggle="tab"><?php echo JText::_('COM_TIENDA_PARAMETERS'); ?></a></li>
</ul>	
<div class="tab-content">
  <div class="tab-pane active" id="panel_details" data-target="panel_details">
	<fieldset>
    <legend><?php echo JText::_('COM_TIENDA_BASIC_INFORMATION'); ?></legend>
			<table class="admintable">
				<tr>
					<td width="100" align="right" class="key">
						<?php echo JText::_('COM_TIENDA_NAME'); ?>:
					</td>
					<td>
						<input name="name" id="name" value="<?php echo @$row->name; ?>" size="48" maxlength="250" type="text" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<?php echo JText::_('COM_TIENDA_ORDERING'); ?>:
					</td>
					<td>
						<input name="ordering" id="ordering" value="<?php echo @$row->ordering; ?>" size="10" maxlength="250" type="text" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="currency_enabled">
						<?php echo JText::_('COM_TIENDA_ENABLED'); ?>:
						</label>
					</td>
					<td>
						<?php echo TiendaSelect::btbooleanlist( 'published', '', @$row->published ); ?>
					</td>
				</tr>
			</table>
			</fieldset>
	</div>
  	<div class="tab-pane" id="panel_parameters" data-target="panel_parameters">
			<fieldset>
    		<legend><?php echo JText::_('COM_TIENDA_PARAMETERS'); ?></legend>
			<?php 
			if ($output = $this->params->render('params')) :
				echo $output;
			else :
				echo "<div style=\"text-align: center; padding: 5px; \">".JText::_('COM_TIENDA_THERE_ARE_NO_PARAMETERS_FOR_THIS_ITEM')."</div>";
			endif;
			?>
			</fieldset>
  	</div>
</div>
			<input type="hidden" name="id" value="<?php echo @$row->id; ?>" />
			<input type="hidden" name="task" value="" />
	
</form>