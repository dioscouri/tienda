<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this -> form; ?>
<?php $row = @$this -> row;
	jimport('joomla.html.pane');
	$tabs = JPane::getInstance('tabs');
?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" >
	<?php
	// start tab pane
	echo $tabs -> startPane("Pane_Payment");
	// Tab
	echo $tabs -> startPanel(JText::_('COM_TIENDA_PLUGIN_DETAILS'), "plugin_properties");
    ?>
    <fieldset>
    <legend><?php echo JText::_('COM_TIENDA_BASIC_INFORMATION'); ?></legend>
			<table class="admintable">
				<tr>
					<td width="100" align="right" class="key">
						<?php echo JText::_('COM_TIENDA_NAME'); ?>:
					</td>
					<td>
						<input name="name" id="name" value="<?php echo @$row -> name; ?>" size="48" maxlength="250" type="text" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<?php echo JText::_('COM_TIENDA_ORDERING'); ?>:
					</td>
					<td>
						<input name="ordering" id="ordering" value="<?php echo @$row -> ordering; ?>" size="10" maxlength="250" type="text" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="currency_enabled">
						<?php echo JText::_('COM_TIENDA_ENABLED'); ?>:
						</label>
					</td>
					<td>
					<?php
						if (version_compare(JVERSION, '1.6.0', 'ge')) {
							// Joomla! 1.6+ code here
							echo JHTML::_('select.booleanlist', 'enabled', '', @$row -> enabled);
						} else {
							// Joomla! 1.5 code here
							echo JHTML::_('select.booleanlist', 'published', '', @$row -> published);
						}
					 ?>
					</td>
				</tr>
			</table>
			</fieldset>
			<fieldset>
    		<legend><?php echo JText::_('COM_TIENDA_PARAMETERS'); ?></legend>
			<?php
			if(version_compare(JVERSION,'1.6.0','ge')) {
			//I had to add a new XML file because the plugin XML for some reason fails to load.  try to fix this later to only core plugin. 	
 			$path = JPATH_SITE.'/plugins/'.$row->folder.'/'.$row->element.'/jform/'.$row->element.'.xml';
			//ok we get a jForm Object from the new file
			$form = JForm::getInstance("paymentplugin", $path);
			//we now need the data, so  lets use the rows params and put them in an object. 
			$registry = new JRegistry;
			//$registry->loadString(stripslashes($row->params));
			//for some reason the above method for me is returning NULL, which is just stupid. if you echo $row->params and copy it and put it below it loads
			$registry->loadString('{"merchant_email":"chris@chris.com","currency":"USD2","secure_post":"0","img_url_std":"https:\/\/www.paypal.com\/en_US\/i\/btn\/x-click-but20.gif","img_url_mixed":"https:\/\/www.paypal.com\/en_US\/i\/btn\/x-click-but02.gif","sandbox":"0","sandbox_merchant_email":"asdfasdfas@sdfasdfas.com","sandbox_customer_email":"asdfasdfasdfasdf@asdfadf.com","articleid":"12","display_article_title":"1","failed_order_state":"17","payment_received_order_state":"1"}');
			$data = $registry->toArray();
			var_dump($data);
			//this is supposed to bind the data, but it isn't working for me, for some reason bind is failing so We add the  values manually
			//$bind = $form->bind($data);
			foreach($data as $k => $v) {
				$form->setValue($k, 'params',$v);
			}
			//we are actually getting fields
			$fieldSets = $form->getFieldsets();
			foreach ($fieldSets as $name => $fieldSet) :
			if (isset($fieldSet->description) && trim($fieldSet->description)) :
			echo '<p class="tip">'.$this->escape(JText::_($fieldSet->description)).'</p>';
			endif;	
			?>
		<?php $hidden_fields = ''; ?>
		<ul class="adminformlist">
			<?php foreach ($form->getFieldset($name) as $field) : ?>
			<?php if (!$field->hidden) : ?>
			<li>
				<?php echo $field -> label; ?>
				<?php echo $field -> input; ?>
			</li>
			<?php else : $hidden_fields.= $field->input; ?>
			<?php endif; ?>
			<?php endforeach; ?>
		</ul>
		<?php echo $hidden_fields; ?>
	
		<?php endforeach; ?>
		<?php 
		
		
} else { ?>
	<?php
	// Joomla! 1.5 code here
	if ($output = $this -> params -> render('params')) :
		echo $output;
	else :
		echo "<div style=\"text-align: center; padding: 5px; \">" . JText::_('COM_TIENDA_THERE_ARE_NO_PARAMETERS_FOR_THIS_ITEM') . "</div>";
	endif;
			
}
			?>
			
			
			
			
			
			
			</fieldset>
			<?php
				echo $tabs -> endPanel();
				echo $tabs -> endPane();
			?>
			
			<?php 	if(version_compare(JVERSION,'1.6.0','ge')) {// Joomla! 1.6+ code here?>
			<input type="hidden" name="extension_id" value="<?php echo @$row -> extension_id; ?>" />
	        <input type="hidden" name="type" value="plugin" /> <?php
			} else {// Joomla! 1.5 code here
 ?>
	  	<input type="hidden" name="id" value="<?php echo @$row -> id; ?>" />
	  	<?php } ?>
			<input type="hidden" name="task" value="" />
			
	
</form>