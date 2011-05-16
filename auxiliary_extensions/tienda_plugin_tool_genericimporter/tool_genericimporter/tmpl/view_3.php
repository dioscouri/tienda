<?php	defined('_JEXEC') or die('Restricted access');?>
<p>
	<?php	echo JText::_($this->_importer->get('tool_description'));?>
</p>
<div class="note">
	<span style="float: right; font-size: large; font-weight: bold;">
		<?php	echo JText::_("FINAL");?>
	</span>
	<p>
		<?php	echo JText::_("MIGRATION RESULTS");?>
	</p>
</div>
<?phpecho $this->getHtmlStep(3, 2);?>
<?phpecho $this->vars->additional_html;?>
